# 🏢 Sistema de Control de Asistencia

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![ESP32](https://img.shields.io/badge/ESP32-Compatible-green.svg)](https://espressif.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Sistema integral de control de asistencia utilizando tecnología RFID con ESP32, desarrollado en PHP con arquitectura MVC.

### 🌟 Características Principales

- 🏷️ **Control RFID**: Lectura automática con MFRC522
- 📡 **ESP32**: Comunicación Wi-Fi en tiempo real
- 🌐 **API REST**: Endpoints seguros y optimizados
- 👥 **Multi-roles**: Administrador, RRHH y Empleados
- 📊 **Reportes**: Dashboards interactivos y exportación PDF/Excel
- 🔒 **Seguridad**: Tokens JWT, sesiones cifradas y validaciones
- ⚡ **Tiempo Real**: Registro inmediato de asistencias
- 🎯 **Precisión**: Detección automática de tardanzas
- 📱 **Responsive**: Compatible con móviles y tablets

## Estructura del Proyecto

```
sistema/
├── app/
│   ├── Controllers/          # Controladores MVC
│   ├── Models/              # Modelos de datos
│   ├── Views/               # Vistas (templates)
│   ├── Middleware/          # Middleware de autenticación
│   └── Utils/               # Utilidades y helpers
├── config/                  # Configuraciones
├── public/                  # Archivos públicos
├── database/               # Scripts SQL y migraciones
├── esp32/                  # Código Arduino para ESP32
├── api/                    # Endpoints de la API
└── docs/                   # Documentación
```

## Requisitos

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- ESP32 + MFRC522
- Tarjetas RFID

## Instalación

1. Clonar el repositorio
2. Ejecutar `composer install`
3. Configurar base de datos en `config/database.php`
4. Importar esquema desde `database/schema.sql`
5. Configurar ESP32 con credenciales Wi-Fi

## ESP32 - Componentes

- ESP32 DevKit
- Módulo RFID MFRC522
- LEDs indicadores
- Resistencias 220Ω
- Buzzer (opcional)

## API Endpoints

- `POST /api/asistencia` - Registrar entrada/salida
- `GET /api/dispositivos` - Listar dispositivos autorizados
- `POST /api/validar-token` - Validar token de dispositivo

## Roles y Permisos

### Administrador

- Gestión completa de usuarios
- Configuración de dispositivos
- Asignación de tarjetas RFID
- Configuraciones del sistema

### Recursos Humanos

- Visualización de asistencias
- Generación de reportes
- Alertas de ausencias/retrasos
- Exportación de datos

### Empleado

- Consulta de historial personal
- Estadísticas de puntualidad
- Descarga de reportes propios

## Licencia

MIT License - Uso libre para proyectos personales y comerciales
