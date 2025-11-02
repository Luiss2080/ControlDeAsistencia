# Instalación Rápida - Sistema de Control de Asistencia

## Pasos de Instalación

### 1. Requisitos Previos

```bash
# Instalar XAMPP 8.1+
# Descargar desde: https://www.apachefriends.org/
# Instalar en C:\xampp\
```

### 2. Configurar Base de Datos

```sql
-- En phpMyAdmin (http://localhost/phpmyadmin)
CREATE DATABASE control_asistencia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Importar archivo: database/schema.sql
-- O ejecutar desde terminal:
mysql -u root control_asistencia < database/schema.sql
```

### 3. Configurar Proyecto

```bash
# Extraer proyecto en C:\xampp\htdocs\ControlDeAsistencia\
# Copiar archivo de configuración
copy config\.env.example config\.env

# Editar config\.env con sus configuraciones
```

### 4. Instalar Dependencias (Opcional)

```bash
# Si tiene Composer instalado
composer install

# Si no tiene Composer, el sistema funciona sin él
```

### 5. Iniciar Servicios

```bash
# Desde Panel de Control XAMPP:
# - Iniciar Apache
# - Iniciar MySQL

# O desde terminal para desarrollo:
cd C:\xampp\htdocs\ControlDeAsistencia
c:\xampp\php\php.exe -S localhost:8000 -t public
```

### 6. Verificar Instalación

- Navegar a: http://localhost:8000
- Login con: admin@empresa.com / password
- Verificar que el dashboard carga correctamente

## Credenciales por Defecto

### Administrador

- **Email:** admin@empresa.com
- **Contraseña:** password

### RRHH

- **Email:** rrhh@empresa.com
- **Contraseña:** password

### Empleado

- **Email:** juan@empresa.com
- **Contraseña:** password

## Configuración ESP32

### Hardware Requerido

- ESP32 DevKit V1
- Lector RFID RC522
- LEDs (Verde/Rojo)
- Resistencias 220Ω
- Cables Dupont

### Código ESP32

```cpp
// Configuración WiFi
const char* ssid = "TU_WIFI";
const char* password = "TU_PASSWORD";

// Configuración servidor
const char* serverURL = "http://IP_SERVIDOR:8000/api/asistencia";
const char* deviceToken = "ESP32_TEST_001"; // Del panel admin
```

### API Endpoint

```
POST http://localhost:8000/api/asistencia
Content-Type: application/json

{
    "uid_tarjeta": "A1B2C3D4",
    "token_dispositivo": "ESP32_TEST_001"
}
```

## Estructura del Proyecto

```
ControlDeAsistencia/
├── api/                    # API REST para ESP32
├── app/
│   ├── Controllers/        # Controladores MVC
│   ├── Models/            # Modelos de datos
│   ├── Utils/             # Utilidades
│   └── Views/             # Vistas HTML
├── config/                # Configuraciones
├── database/              # Scripts SQL
├── esp32/                 # Código Arduino
├── public/                # Punto de entrada web
├── composer.json          # Dependencias PHP
├── REQUIREMENTS.md        # Requisitos detallados
└── README.md             # Documentación
```

## Solución de Problemas

### Error de Conexión

```bash
# Verificar que Apache y MySQL estén corriendo
netstat -an | findstr :80
netstat -an | findstr :3306
```

### Error 500

```bash
# Revisar logs de Apache
type C:\xampp\apache\logs\error.log
```

### Base de Datos no Conecta

```bash
# Verificar configuración en config/database.php
# Verificar que la BD existe:
mysql -u root -e "SHOW DATABASES;"
```

## URLs Importantes

- **Sistema:** http://localhost:8000
- **phpMyAdmin:** http://localhost/phpmyadmin
- **Panel XAMPP:** http://localhost/dashboard
- **API Docs:** http://localhost:8000/api
- **Panel Admin:** http://localhost:8000/admin

## Contacto y Soporte

Para soporte técnico o preguntas:

- Revisar REQUIREMENTS.md
- Consultar logs del sistema
- Verificar configuración de red para ESP32
