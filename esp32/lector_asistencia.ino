/*
 * Sistema de Control de Asistencia con ESP32 y RFID RC522
 * Versión: 2.0 - Completa y optimizada
 * 
 * Funcionalidades:
 * - Lee tarjetas RFID y envía datos al servidor
 * - Indicadores LED y buzzer para feedback
 * - Conexión WiFi con reconexión automática
 * - Almacenamiento offline para casos sin internet
 * - Sincronización de tiempo con NTP
 * - Ping periódico al servidor
 * 
 * Conexiones ESP32 - RC522:
 * - SDA/SS: GPIO 5
 * - SCK: GPIO 18
 * - MOSI: GPIO 23
 * - MISO: GPIO 19
 * - IRQ: No conectado
 * - GND: GND
 * - RST: GPIO 22
 * - 3.3V: 3.3V
 * 
 * Conexiones adicionales:
 * - LED Verde: GPIO 2 (220Ω a GND)
 * - LED Rojo: GPIO 4 (220Ω a GND)
 * - LED Azul: GPIO 16 (220Ω a GND)
 * - Buzzer: GPIO 17 (Activo directo)
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <Preferences.h>

// ===== CONFIGURACIÓN DE HARDWARE =====
#define SS_PIN 5
#define RST_PIN 22
#define LED_VERDE 2
#define LED_ROJO 4
#define LED_AZUL 16
#define BUZZER 17

// ===== CONFIGURACIÓN DE RED =====
// CAMBIAR POR TUS DATOS DE WIFI
const char* ssid = "TU_RED_WIFI";
const char* password = "TU_PASSWORD_WIFI";

// ===== CONFIGURACIÓN DEL SERVIDOR =====
// CAMBIAR POR LA IP DE TU SERVIDOR
const char* serverURL = "http://192.168.1.100/ControlDeAsistencia/api";
const char* apiKey = "ESP32_TOKEN_PRINCIPAL_2024";

// ===== CONFIGURACIÓN DE TIEMPO =====
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", -21600, 60000); // GMT-6 México

// ===== OBJETOS GLOBALES =====
MFRC522 mfrc522(SS_PIN, RST_PIN);
HTTPClient http;
Preferences preferences;

// ===== VARIABLES GLOBALES =====
bool wifiConnected = false;
unsigned long lastCardRead = 0;
unsigned long lastPing = 0;
String lastUID = "";

// ===== CONSTANTES =====
const unsigned long CARD_DEBOUNCE = 3000;  // 3 segundos entre lecturas de la misma tarjeta
const unsigned long WIFI_TIMEOUT = 20000;  // 20 segundos timeout para WiFi
const unsigned long PING_INTERVAL = 60000; // 1 minuto entre pings

void setup() {
  Serial.begin(115200);
  Serial.println("\n=== Sistema de Control de Asistencia ===");
  Serial.println("Versión: 2.0");
  Serial.println("Iniciando ESP32...");
  
  // Inicializar preferencias (EEPROM)
  preferences.begin("asistencia", false);
  
  // Configurar pines
  pinMode(LED_VERDE, OUTPUT);
  pinMode(LED_ROJO, OUTPUT);
  pinMode(LED_AZUL, OUTPUT);
  pinMode(BUZZER, OUTPUT);
  
  // LEDs de inicio
  indicarInicio();
  
  // Inicializar SPI y MFRC522
  SPI.begin();
  mfrc522.PCD_Init();
  
  // Verificar lector RFID
  if (!verificarLectorRFID()) {
    Serial.println("ERROR: No se pudo inicializar el lector RFID");
    indicarError();
    while(1) delay(1000);
  }
  
  Serial.println("✓ Lector RFID inicializado correctamente");
  
  // Conectar a WiFi
  conectarWiFi();
  
  // Inicializar cliente NTP
  timeClient.begin();
  sincronizarTiempo();
  
  // Obtener configuración del servidor
  obtenerConfiguracion();
  
  // Ping inicial
  enviarPing();
  
  Serial.println("✓ Sistema listo para leer tarjetas");
  indicarListo();
}

void loop() {
  // Verificar conexión WiFi
  if (WiFi.status() != WL_CONNECTED) {
    wifiConnected = false;
    Serial.println("WiFi desconectado. Reintentando...");
    conectarWiFi();
  } else {
    wifiConnected = true;
  }
  
  // Actualizar tiempo
  timeClient.update();
  
  // Enviar ping periódico
  if (millis() - lastPing > PING_INTERVAL) {
    enviarPing();
    lastPing = millis();
  }
  
  // Leer tarjetas RFID
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    procesarTarjeta();
  }
  
  // Indicar estado con LED azul
  if (wifiConnected) {
    digitalWrite(LED_AZUL, (millis() / 1000) % 2); // Parpadeo lento
  } else {
    digitalWrite(LED_AZUL, (millis() / 200) % 2);  // Parpadeo rápido
  }
  
  delay(100);
}

void conectarWiFi() {
  Serial.println("Conectando a WiFi...");
  Serial.println("Red: " + String(ssid));
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  
  unsigned long startTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startTime < WIFI_TIMEOUT) {
    delay(500);
    Serial.print(".");
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    wifiConnected = true;
    Serial.println();
    Serial.println("✓ WiFi conectado");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    Serial.print("MAC: ");
    Serial.println(WiFi.macAddress());
    Serial.print("Señal: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
    
    sonidoExito();
  } else {
    wifiConnected = false;
    Serial.println("\n✗ Error al conectar WiFi");
    sonidoError();
  }
}

void sincronizarTiempo() {
  Serial.println("Sincronizando tiempo...");
  
  int retries = 0;
  while (!timeClient.update() && retries < 5) {
    timeClient.forceUpdate();
    retries++;
    delay(1000);
  }
  
  if (retries < 5) {
    Serial.println("✓ Tiempo sincronizado");
    Serial.println("Hora actual: " + timeClient.getFormattedTime());
  } else {
    Serial.println("✗ No se pudo sincronizar el tiempo");
  }
}

bool verificarLectorRFID() {
  byte version = mfrc522.PCD_ReadRegister(MFRC522::VersionReg);
  return (version == 0x91 || version == 0x92);
}

void procesarTarjeta() {
  // Evitar lecturas duplicadas muy rápidas
  if (millis() - lastCardRead < CARD_DEBOUNCE) {
    return;
  }
  
  // Obtener UID de la tarjeta
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  
  // Evitar procesar la misma tarjeta muy seguido
  if (uid == lastUID && millis() - lastCardRead < CARD_DEBOUNCE * 2) {
    return;
  }
  
  lastUID = uid;
  lastCardRead = millis();
  
  Serial.println("\n=== TARJETA DETECTADA ===");
  Serial.println("UID: " + uid);
  Serial.println("Timestamp: " + obtenerTimestamp());
  
  // Indicar lectura
  digitalWrite(LED_AZUL, HIGH);
  sonidoLectura();
  
  // Validar tarjeta primero
  if (validarTarjeta(uid)) {
    // Registrar asistencia
    if (registrarAsistencia(uid)) {
      indicarExito();
      Serial.println("✓ Asistencia registrada");
    } else {
      indicarError();
      Serial.println("✗ Error al registrar asistencia");
    }
  } else {
    indicarError();
    Serial.println("✗ Tarjeta no válida");
  }
  
  digitalWrite(LED_AZUL, LOW);
  
  // Detener la tarjeta
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
  
  Serial.println("========================\n");
}

bool validarTarjeta(String uid) {
  if (!wifiConnected) {
    Serial.println("Sin conexión WiFi - no se puede validar");
    return false;
  }
  
  http.begin(String(serverURL) + "/validar-tarjeta");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Authorization", "Bearer " + String(apiKey));
  
  // Crear JSON
  DynamicJsonDocument doc(1024);
  doc["uid_tarjeta"] = uid;
  doc["dispositivo_token"] = apiKey;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpCode = http.POST(jsonString);
  
  if (httpCode == 200) {
    String response = http.getString();
    
    DynamicJsonDocument responseDoc(1024);
    deserializeJson(responseDoc, response);
    
    bool valida = responseDoc["success"];
    
    if (valida) {
      String usuario = responseDoc["usuario_nombre"];
      Serial.println("👤 Usuario: " + usuario);
    } else {
      String error = responseDoc["error"];
      Serial.println("❌ Error: " + error);
    }
    
    http.end();
    return valida;
  }
  
  Serial.println("Error en validación HTTP: " + String(httpCode));
  http.end();
  return false;
}

bool registrarAsistencia(String uid) {
  if (!wifiConnected) {
    Serial.println("Sin conexión WiFi - guardando offline");
    return guardarAsistenciaOffline(uid);
  }
  
  http.begin(String(serverURL) + "/registrar-asistencia");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Authorization", "Bearer " + String(apiKey));
  
  // Crear JSON con todos los datos
  DynamicJsonDocument doc(1024);
  doc["uid_tarjeta"] = uid;
  doc["dispositivo_token"] = apiKey;
  doc["timestamp"] = obtenerTimestamp();
  doc["ip_address"] = WiFi.localIP().toString();
  doc["signal_strength"] = WiFi.RSSI();
  doc["firmware_version"] = "2.0";
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("📤 Enviando: " + jsonString);
  
  int httpCode = http.POST(jsonString);
  
  if (httpCode == 200) {
    String response = http.getString();
    Serial.println("📥 Respuesta: " + response);
    
    DynamicJsonDocument responseDoc(2048);
    deserializeJson(responseDoc, response);
    
    if (responseDoc["success"]) {
      String usuario = responseDoc["usuario_nombre"];
      String tipo = responseDoc["tipo_marcacion"];
      bool fuera_horario = responseDoc["fuera_horario"];
      
      Serial.println("👤 Usuario: " + usuario);
      Serial.println("📝 Tipo: " + tipo);
      
      if (fuera_horario) {
        Serial.println("⚠️ FUERA DE HORARIO");
        sonidoTardanza();
      }
      
      http.end();
      return true;
    } else {
      String error = responseDoc["error"];
      Serial.println("❌ Error del servidor: " + error);
    }
  }
  
  Serial.println("❌ Error HTTP: " + String(httpCode));
  if (httpCode > 0) {
    Serial.println("Respuesta: " + http.getString());
  }
  
  http.end();
  
  // Si falla, guardar offline
  return guardarAsistenciaOffline(uid);
}

bool guardarAsistenciaOffline(String uid) {
  Serial.println("💾 Guardando asistencia offline...");
  
  // Usar preferences para guardar datos offline
  int contador = preferences.getInt("offline_count", 0);
  String key = "offline_" + String(contador);
  
  DynamicJsonDocument doc(512);
  doc["uid"] = uid;
  doc["timestamp"] = obtenerTimestamp();
  doc["mac"] = WiFi.macAddress();
  doc["dispositivo_token"] = apiKey;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  preferences.putString(key.c_str(), jsonString);
  preferences.putInt("offline_count", contador + 1);
  
  Serial.println("✓ Guardado offline (#" + String(contador + 1) + ")");
  return true;
}

void enviarAsistenciasOffline() {
  int contador = preferences.getInt("offline_count", 0);
  
  if (contador == 0) return;
  
  Serial.println("📮 Enviando " + String(contador) + " asistencias offline...");
  
  int enviadas = 0;
  for (int i = 0; i < contador; i++) {
    String key = "offline_" + String(i);
    String data = preferences.getString(key.c_str(), "");
    
    if (data.length() > 0) {
      // Intentar enviar
      http.begin(String(serverURL) + "/registrar-asistencia");
      http.addHeader("Content-Type", "application/json");
      http.addHeader("Authorization", "Bearer " + String(apiKey));
      
      int httpCode = http.POST(data);
      
      if (httpCode == 200) {
        preferences.remove(key.c_str());
        enviadas++;
        Serial.println("✓ Asistencia offline " + String(i + 1) + " enviada");
      } else {
        Serial.println("✗ Error enviando asistencia " + String(i + 1));
        break; // Parar si hay error
      }
      
      http.end();
      delay(500); // Esperar entre envíos
    }
  }
  
  if (enviadas > 0) {
    // Reorganizar índices si quedan pendientes
    int restantes = contador - enviadas;
    for (int i = 0; i < restantes; i++) {
      String keyOld = "offline_" + String(i + enviadas);
      String keyNew = "offline_" + String(i);
      String data = preferences.getString(keyOld.c_str(), "");
      preferences.putString(keyNew.c_str(), data);
      preferences.remove(keyOld.c_str());
    }
    
    preferences.putInt("offline_count", restantes);
    Serial.println("📮 Enviadas " + String(enviadas) + " asistencias offline");
  }
}

void enviarPing() {
  if (!wifiConnected) return;
  
  http.begin(String(serverURL) + "/ping-dispositivo");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Authorization", "Bearer " + String(apiKey));
  
  DynamicJsonDocument doc(1024);
  doc["dispositivo_token"] = apiKey;
  doc["timestamp"] = obtenerTimestamp();
  doc["ip_address"] = WiFi.localIP().toString();
  doc["firmware_version"] = "2.0";
  doc["free_heap"] = ESP.getFreeHeap();
  doc["uptime"] = millis();
  doc["signal_strength"] = WiFi.RSSI();
  doc["estado"] = "online";
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpCode = http.POST(jsonString);
  
  if (httpCode == 200) {
    Serial.println("💓 Ping enviado");
    // Enviar asistencias offline pendientes
    enviarAsistenciasOffline();
  } else {
    Serial.println("❌ Error en ping: " + String(httpCode));
  }
  
  http.end();
}

void obtenerConfiguracion() {
  if (!wifiConnected) return;
  
  Serial.println("⚙️ Obteniendo configuración del servidor...");
  
  http.begin(String(serverURL) + "/configuracion-dispositivo");
  http.addHeader("Authorization", "Bearer " + String(apiKey));
  
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String response = http.getString();
    
    DynamicJsonDocument doc(2048);
    deserializeJson(doc, response);
    
    if (doc["success"]) {
      JsonObject config = doc["configuracion"];
      
      Serial.println("✓ Configuración obtenida");
      
      // Mostrar configuraciones importantes
      if (config.containsKey("minutos_entre_marcaciones")) {
        Serial.println("Minutos entre marcaciones: " + String((int)config["minutos_entre_marcaciones"]));
      }
      
      if (config.containsKey("tolerancia_entrada")) {
        Serial.println("Tolerancia entrada: " + String((int)config["tolerancia_entrada"]) + " min");
      }
    }
  } else {
    Serial.println("⚠️ No se pudo obtener configuración");
  }
  
  http.end();
}

String obtenerTimestamp() {
  // Formato: YYYY-MM-DD HH:MM:SS
  time_t epochTime = timeClient.getEpochTime();
  struct tm *ptm = gmtime(&epochTime);
  
  char timestamp[20];
  sprintf(timestamp, "%04d-%02d-%02d %02d:%02d:%02d",
          ptm->tm_year + 1900,
          ptm->tm_mon + 1,
          ptm->tm_mday,
          ptm->tm_hour,
          ptm->tm_min,
          ptm->tm_sec);
  
  return String(timestamp);
}

// ===== FUNCIONES DE INDICACIÓN =====

void indicarInicio() {
  Serial.println("🔄 Inicializando hardware...");
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_VERDE, HIGH);
    digitalWrite(LED_ROJO, HIGH);
    digitalWrite(LED_AZUL, HIGH);
    delay(200);
    digitalWrite(LED_VERDE, LOW);
    digitalWrite(LED_ROJO, LOW);
    digitalWrite(LED_AZUL, LOW);
    delay(200);
  }
}

void indicarListo() {
  digitalWrite(LED_VERDE, HIGH);
  delay(1000);
  digitalWrite(LED_VERDE, LOW);
  sonidoListo();
}

void indicarExito() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(LED_VERDE, HIGH);
    delay(300);
    digitalWrite(LED_VERDE, LOW);
    delay(200);
  }
}

void indicarError() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_ROJO, HIGH);
    delay(200);
    digitalWrite(LED_ROJO, LOW);
    delay(200);
  }
}

void sonidoExito() {
  tone(BUZZER, 1000, 200);
  delay(250);
  tone(BUZZER, 1500, 200);
}

void sonidoError() {
  for (int i = 0; i < 3; i++) {
    tone(BUZZER, 300, 200);
    delay(300);
  }
}

void sonidoLectura() {
  tone(BUZZER, 800, 100);
}

void sonidoListo() {
  tone(BUZZER, 600, 200);
  delay(250);
  tone(BUZZER, 800, 200);
  delay(250);
  tone(BUZZER, 1000, 200);
}

void sonidoTardanza() {
  for (int i = 0; i < 5; i++) {
    tone(BUZZER, 400, 100);
    delay(150);
  }
}

void setup() {
  Serial.begin(115200);
  Serial.println("\n=== Sistema de Control de Asistencia ===");
  Serial.println("Iniciando ESP32...");
  
  // Inicializar preferencias (EEPROM)
  preferences.begin("asistencia", false);
  
  // Configurar pines
  pinMode(LED_VERDE, OUTPUT);
  pinMode(LED_ROJO, OUTPUT);
  pinMode(LED_AZUL, OUTPUT);
  pinMode(BUZZER, OUTPUT);
  
  // LEDs de inicio
  indicarInicio();
  
  // Inicializar SPI y MFRC522
  SPI.begin();
  mfrc522.PCD_Init();
  
  // Verificar lector RFID
  if (!verificarLectorRFID()) {
    Serial.println("ERROR: No se pudo inicializar el lector RFID");
    indicarError();
    while(1) delay(1000);
  }
  
  Serial.println("✓ Lector RFID inicializado correctamente");
  
  // Conectar a WiFi
  conectarWiFi();
  
  // Inicializar cliente NTP
  timeClient.begin();
  sincronizarTiempo();
  
  // Obtener configuración del servidor
  obtenerConfiguracion();
  
  // Ping inicial
  enviarPing();
  
  Serial.println("✓ Sistema listo para leer tarjetas");
  indicarListo();
}

void loop() {
  // Verificar conexión WiFi
  if (WiFi.status() != WL_CONNECTED) {
    wifiConnected = false;
    Serial.println("WiFi desconectado. Reintentando...");
    conectarWiFi();
  } else {
    wifiConnected = true;
  }
  
  // Actualizar tiempo
  timeClient.update();
  
  // Enviar ping periódico
  if (millis() - lastPing > PING_INTERVAL) {
    enviarPing();
    lastPing = millis();
  }
  
  // Leer tarjetas RFID
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    procesarTarjeta();
  }
  
  // Indicar estado con LED azul
  if (wifiConnected) {
    digitalWrite(LED_AZUL, (millis() / 1000) % 2); // Parpadeo lento
  } else {
    digitalWrite(LED_AZUL, (millis() / 200) % 2);  // Parpadeo rápido
  }
  
  delay(100);
}

void conectarWiFi() {
  Serial.println("Conectando a WiFi...");
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  
  unsigned long startTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startTime < WIFI_TIMEOUT) {
    delay(500);
    Serial.print(".");
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    wifiConnected = true;
    Serial.println();
    Serial.println("✓ WiFi conectado");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    Serial.print("MAC: ");
    Serial.println(WiFi.macAddress());
    
    sonidoExito();
  } else {
    wifiConnected = false;
    Serial.println("\n✗ Error al conectar WiFi");
    sonidoError();
  }
}

void sincronizarTiempo() {
  Serial.println("Sincronizando tiempo...");
  
  int retries = 0;
  while (!timeClient.update() && retries < 5) {
    timeClient.forceUpdate();
    retries++;
    delay(1000);
  }
  
  if (retries < 5) {
    Serial.println("✓ Tiempo sincronizado");
    Serial.println("Hora actual: " + timeClient.getFormattedTime());
  } else {
    Serial.println("✗ No se pudo sincronizar el tiempo");
  }
}

bool verificarLectorRFID() {
  byte version = mfrc522.PCD_ReadRegister(MFRC522::VersionReg);
  return (version == 0x91 || version == 0x92);
}

void procesarTarjeta() {
  // Evitar lecturas duplicadas muy rápidas
  if (millis() - lastCardRead < CARD_DEBOUNCE) {
    return;
  }
  
  // Obtener UID de la tarjeta
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  
  // Evitar procesar la misma tarjeta muy seguido
  if (uid == lastUID && millis() - lastCardRead < CARD_DEBOUNCE * 2) {
    return;
  }
  
  lastUID = uid;
  lastCardRead = millis();
  
  Serial.println("\n--- Tarjeta detectada ---");
  Serial.println("UID: " + uid);
  
  // Indicar lectura
  digitalWrite(LED_AZUL, HIGH);
  sonidoLectura();
  
  // Validar tarjeta primero
  if (validarTarjeta(uid)) {
    // Registrar asistencia
    if (registrarAsistencia(uid)) {
      indicarExito();
      Serial.println("✓ Asistencia registrada");
    } else {
      indicarError();
      Serial.println("✗ Error al registrar asistencia");
    }
  } else {
    indicarError();
    Serial.println("✗ Tarjeta no válida");
  }
  
  digitalWrite(LED_AZUL, LOW);
  
  // Detener la tarjeta
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}

bool validarTarjeta(String uid) {
  if (!wifiConnected) {
    Serial.println("Sin conexión WiFi - no se puede validar");
    return false;
  }
  
  http.begin(String(serverURL) + "/tarjeta/validar");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);
  
  // Crear JSON
  DynamicJsonDocument doc(1024);
  doc["uid_tarjeta"] = uid;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpCode = http.POST(jsonString);
  
  if (httpCode == 200) {
    String response = http.getString();
    
    DynamicJsonDocument responseDoc(1024);
    deserializeJson(responseDoc, response);
    
    bool valida = responseDoc["data"]["valida"];
    
    if (valida) {
      String usuario = responseDoc["data"]["usuario"];
      Serial.println("Usuario: " + usuario);
    } else {
      String motivo = responseDoc["data"]["motivo"];
      Serial.println("Motivo: " + motivo);
    }
    
    http.end();
    return valida;
  }
  
  Serial.println("Error en validación HTTP: " + String(httpCode));
  http.end();
  return false;
}

bool registrarAsistencia(String uid) {
  if (!wifiConnected) {
    Serial.println("Sin conexión WiFi - guardando offline");
    return guardarAsistenciaOffline(uid);
  }
  
  http.begin(String(serverURL) + "/asistencia");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);
  
  // Crear JSON con timestamp
  DynamicJsonDocument doc(1024);
  doc["uid_tarjeta"] = uid;
  doc["timestamp"] = obtenerTimestamp();
  doc["dispositivo_info"] = WiFi.macAddress();
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("Enviando: " + jsonString);
  
  int httpCode = http.POST(jsonString);
  
  if (httpCode == 200) {
    String response = http.getString();
    Serial.println("Respuesta: " + response);
    
    DynamicJsonDocument responseDoc(2048);
    deserializeJson(responseDoc, response);
    
    if (responseDoc["success"]) {
      String usuario = responseDoc["data"]["usuario"];
      String tipo = responseDoc["data"]["tipo"];
      bool tardanza = responseDoc["data"]["tardanza"];
      
      Serial.println("Usuario: " + usuario);
      Serial.println("Tipo: " + tipo);
      
      if (tardanza) {
        Serial.println("⚠ TARDANZA detectada");
        sonidoTardanza();
      }
      
      http.end();
      return true;
    }
  }
  
  Serial.println("Error HTTP: " + String(httpCode));
  if (httpCode > 0) {
    Serial.println("Respuesta: " + http.getString());
  }
  
  http.end();
  
  // Si falla, guardar offline
  return guardarAsistenciaOffline(uid);
}

bool guardarAsistenciaOffline(String uid) {
  // Implementar almacenamiento local para casos sin internet
  Serial.println("Guardando asistencia offline...");
  
  // Usar preferences para guardar datos offline
  int contador = preferences.getInt("offline_count", 0);
  String key = "offline_" + String(contador);
  
  DynamicJsonDocument doc(512);
  doc["uid"] = uid;
  doc["timestamp"] = obtenerTimestamp();
  doc["mac"] = WiFi.macAddress();
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  preferences.putString(key.c_str(), jsonString);
  preferences.putInt("offline_count", contador + 1);
  
  Serial.println("✓ Guardado offline");
  return true;
}

void enviarAsistenciasOffline() {
  int contador = preferences.getInt("offline_count", 0);
  
  if (contador == 0) return;
  
  Serial.println("Enviando " + String(contador) + " asistencias offline...");
  
  for (int i = 0; i < contador; i++) {
    String key = "offline_" + String(i);
    String data = preferences.getString(key.c_str(), "");
    
    if (data.length() > 0) {
      // Intentar enviar
      http.begin(String(serverURL) + "/asistencia");
      http.addHeader("Content-Type", "application/json");
      http.addHeader("X-API-Key", apiKey);
      
      int httpCode = http.POST(data);
      
      if (httpCode == 200) {
        preferences.remove(key.c_str());
        Serial.println("✓ Asistencia offline " + String(i) + " enviada");
      } else {
        Serial.println("✗ Error enviando asistencia " + String(i));
        break; // Parar si hay error
      }
      
      http.end();
      delay(500); // Esperar entre envíos
    }
  }
  
  // Limpiar contador si se enviaron todas
  preferences.putInt("offline_count", 0);
}

void enviarPing() {
  if (!wifiConnected) return;
  
  http.begin(String(serverURL) + "/dispositivo/status");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);
  
  DynamicJsonDocument doc(512);
  doc["timestamp"] = obtenerTimestamp();
  doc["ip_address"] = WiFi.localIP().toString();
  doc["firmware_version"] = "1.0.0";
  doc["free_heap"] = ESP.getFreeHeap();
  doc["uptime"] = millis();
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpCode = http.POST(jsonString);
  
  if (httpCode == 200) {
    Serial.println("✓ Ping enviado");
    // Enviar asistencias offline pendientes
    enviarAsistenciasOffline();
  } else {
    Serial.println("✗ Error en ping: " + String(httpCode));
  }
  
  http.end();
}

void obtenerConfiguracion() {
  if (!wifiConnected) return;
  
  Serial.println("Obteniendo configuración del servidor...");
  
  http.begin(String(serverURL) + "/dispositivo/config");
  http.addHeader("X-API-Key", apiKey);
  
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String response = http.getString();
    
    DynamicJsonDocument doc(2048);
    deserializeJson(doc, response);
    
    if (doc["success"]) {
      JsonObject config = doc["data"]["configuracion"];
      
      // Actualizar configuraciones locales si es necesario
      Serial.println("✓ Configuración obtenida");
      
      // Mostrar algunas configuraciones importantes
      if (config.containsKey("tolerancia_entrada_minutos")) {
        Serial.println("Tolerancia entrada: " + String((int)config["tolerancia_entrada_minutos"]) + " min");
      }
    }
  }
  
  http.end();
}

String obtenerTimestamp() {
  // Formato: YYYY-MM-DD HH:MM:SS
  time_t epochTime = timeClient.getEpochTime();
  struct tm *ptm = gmtime(&epochTime);
  
  char timestamp[20];
  sprintf(timestamp, "%04d-%02d-%02d %02d:%02d:%02d",
          ptm->tm_year + 1900,
          ptm->tm_mon + 1,
          ptm->tm_mday,
          (ptm->tm_hour - 6 + 24) % 24, // Ajuste GMT-6
          ptm->tm_min,
          ptm->tm_sec);
  
  return String(timestamp);
}

// Funciones de indicación visual y sonora

void indicarInicio() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_VERDE, HIGH);
    digitalWrite(LED_ROJO, HIGH);
    digitalWrite(LED_AZUL, HIGH);
    delay(200);
    digitalWrite(LED_VERDE, LOW);
    digitalWrite(LED_ROJO, LOW);
    digitalWrite(LED_AZUL, LOW);
    delay(200);
  }
}

void indicarListo() {
  digitalWrite(LED_VERDE, HIGH);
  delay(1000);
  digitalWrite(LED_VERDE, LOW);
  sonidoListo();
}

void indicarExito() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(LED_VERDE, HIGH);
    delay(300);
    digitalWrite(LED_VERDE, LOW);
    delay(200);
  }
}

void indicarError() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_ROJO, HIGH);
    delay(200);
    digitalWrite(LED_ROJO, LOW);
    delay(200);
  }
}

void sonidoExito() {
  tone(BUZZER, 1000, 200);
  delay(250);
  tone(BUZZER, 1500, 200);
}

void sonidoError() {
  for (int i = 0; i < 3; i++) {
    tone(BUZZER, 300, 200);
    delay(300);
  }
}

void sonidoLectura() {
  tone(BUZZER, 800, 100);
}

void sonidoListo() {
  tone(BUZZER, 600, 200);
  delay(250);
  tone(BUZZER, 800, 200);
  delay(250);
  tone(BUZZER, 1000, 200);
}

void sonidoTardanza() {
  for (int i = 0; i < 5; i++) {
    tone(BUZZER, 400, 100);
    delay(150);
  }
}