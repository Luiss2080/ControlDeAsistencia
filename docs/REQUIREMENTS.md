# Sistema de Control de Asistencia - Requisitos y Dependencias

## Requisitos del Sistema

### Software Base Requerido

- **PHP 8.0+** (Recomendado: PHP 8.1 o superior)
- **MySQL 5.7+** o **MariaDB 10.3+**
- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **Composer** (Gestor de dependencias PHP)

### Stack XAMPP (Recomendado para desarrollo)

- **XAMPP 8.1.25** o superior
  - PHP 8.1.25
  - Apache 2.4.54
  - MySQL 8.0.31
  - phpMyAdmin 5.2.0

## Dependencias PHP

### Extensiones PHP Requeridas

```
php-pdo
php-pdo_mysql
php-json
php-curl
php-mbstring
php-openssl
php-session
php-filter
php-hash
```

### Bibliotecas PHP (Composer)

```json
{
  "require": {
    "php": ">=8.0",
    "ext-pdo": "*",
    "ext-pdo_mysql": "*",
    "ext-json": "*",
    "ext-curl": "*",
    "ext-mbstring": "*",
    "ext-openssl": "*"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.5",
    "squizlabs/php_codesniffer": "^3.6"
  }
}
```

## Frontend Dependencies

### CSS Frameworks

- **Bootstrap 5.3.0** (CDN)
- **Font Awesome 6.0.0** (CDN) - Para iconos

### JavaScript Libraries

- **Bootstrap JS 5.3.0** (CDN)
- **Chart.js** (Opcional - para gráficos)

## Hardware para ESP32

### Componentes Electrónicos

- **ESP32 DevKit V1** o compatible
- **Lector RFID RC522**
- **Tarjetas RFID** (ISO14443A)
- **LED indicadores** (Verde/Rojo)
- **Buzzer** (Opcional - para feedback sonoro)
- **Resistencias** 220Ω para LEDs
- **Cables Dupont** macho-hembra
- **Protoboard** o PCB

### Librerías Arduino para ESP32

```cpp
// En Arduino IDE - Gestor de Librerías
#include <WiFi.h>          // ESP32 WiFi
#include <HTTPClient.h>    // Cliente HTTP
#include <ArduinoJson.h>   // Manejo JSON - v6.19.4+
#include <SPI.h>           // Comunicación SPI
#include <MFRC522.h>       // Librería RFID RC522 - v1.4.10+
```

## Configuración del Servidor

### Configuración PHP (php.ini)

```ini
; Configuraciones recomendadas
memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 300
max_input_vars = 3000
session.gc_maxlifetime = 3600

; Extensiones requeridas
extension=pdo_mysql
extension=curl
extension=openssl
extension=mbstring
extension=json
```

### Configuración MySQL

```sql
-- Configuraciones recomendadas en my.ini o my.cnf
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 128M
sql_mode = STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO
default_authentication_plugin = mysql_native_password
```

### Configuración Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# PHP settings
php_value upload_max_filesize 32M
php_value post_max_size 32M
php_value memory_limit 256M
```

## Configuración de Red

### Puertos Requeridos

- **Puerto 80** - HTTP (Apache/Nginx)
- **Puerto 443** - HTTPS (Opcional pero recomendado)
- **Puerto 3306** - MySQL
- **Puerto 8000** - Servidor de desarrollo PHP

### Configuración WiFi para ESP32

```cpp
// En el código del ESP32
const char* ssid = "TU_WIFI_SSID";
const char* password = "TU_WIFI_PASSWORD";
const char* serverURL = "http://IP_SERVIDOR:8000/api/asistencia";
```

## Variables de Entorno

### Archivo .env (crear en /config/)

```env
# Base de datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=control_asistencia
DB_USER=root
DB_PASS=

# Aplicación
APP_DEBUG=true
APP_TIMEZONE=America/Mexico_City
APP_URL=http://localhost:8000

# Seguridad
SESSION_LIFETIME=3600
CSRF_TOKEN_LIFETIME=7200
```

## Instalación Paso a Paso

### 1. Instalación XAMPP

1. Descargar XAMPP desde https://www.apachefriends.org/
2. Instalar en C:\xampp\
3. Iniciar Apache y MySQL desde el panel de control

### 2. Configuración Base de Datos

```sql
-- Ejecutar en phpMyAdmin o consola MySQL
CREATE DATABASE control_asistencia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE control_asistencia;
-- Importar database/schema.sql
```

### 3. Configuración del Proyecto

```bash
# Clonar/extraer proyecto en C:\xampp\htdocs\
cd C:\xampp\htdocs\ControlDeAsistencia\

# Instalar dependencias (si usa Composer)
composer install

# Configurar permisos (en Linux/Mac)
chmod 755 storage/
chmod 755 logs/
```

### 4. Verificación de la Instalación

- Navegar a http://localhost/ControlDeAsistencia/
- Login con admin@empresa.com / password
- Verificar dashboard y funcionalidades

## Solución de Problemas Comunes

### Error de Conexión a Base de Datos

- Verificar que MySQL esté ejecutándose
- Verificar credenciales en config/database.php
- Verificar que la base de datos existe

### Error 500 - Internal Server Error

- Revisar logs de Apache en C:\xampp\apache\logs\error.log
- Verificar extensiones PHP habilitadas
- Verificar permisos de archivos

### ESP32 no conecta

- Verificar credenciales WiFi
- Verificar IP del servidor en código ESP32
- Verificar que el servidor web esté accesible

## Consideraciones de Seguridad

### Producción

- Cambiar contraseñas por defecto
- Habilitar HTTPS
- Configurar firewall
- Actualizar regularmente dependencias
- Configurar backups automáticos

### Desarrollo

- Usar entorno aislado
- No exponer base de datos a internet
- Usar tokens seguros para ESP32
- Implementar rate limiting para API

## Versiones Testadas

- **PHP**: 8.1.25
- **MySQL**: 8.0.31
- **Apache**: 2.4.54
- **Bootstrap**: 5.3.0
- **ESP32 Core**: 2.0.11
- **Arduino IDE**: 2.2.1
