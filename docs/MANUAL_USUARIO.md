# 📋 Sistema de Control de Asistencia - Guía Completa

## 🔐 **CREDENCIALES DE ACCESO**

### 👨‍💼 **Administrador** (Acceso total)
- **Email:** `admin@empresa.com`
- **Contraseña:** `admin123`
- **Permisos:** Administrar usuarios, dispositivos, reportes, configuración

### 👩‍💼 **Recursos Humanos (RRHH)**
- **Email:** `rrhh@empresa.com`
- **Contraseña:** `rrhh123`
- **Permisos:** Ver reportes de asistencia, gestionar empleados

### 👨‍💻 **Empleado**
- **Email:** `juan@empresa.com`
- **Contraseña:** `emp123`
- **Permisos:** Ver su propia asistencia e historial

---

## 🌐 **ACCESO AL SISTEMA**

### URL Principal:
```
http://localhost/ControlDeAsistencia/public/
```

### URLs de Prueba:
```
http://localhost/ControlDeAsistencia/public/test.php          (Diagnóstico)
http://localhost/ControlDeAsistencia/public/login_directo.php (Login directo)
```

---

## 🎯 **FUNCIONALIDADES POR ROL**

### 🔧 **PANEL ADMINISTRADOR** (`/admin/dashboard`)
- **Gestión de Usuarios:** Crear, editar, eliminar empleados
- **Gestión de Dispositivos:** Registrar lectores RFID ESP32
- **Gestión de Tarjetas:** Asignar tarjetas RFID a empleados
- **Reportes Completos:** Ver asistencia de todos los empleados
- **Configuración:** Horarios, parámetros del sistema

### 📊 **PANEL RRHH** (`/rrhh/dashboard`)
- **Reportes de Asistencia:** Por empleado, fecha, departamento
- **Estadísticas:** Puntualidad, ausencias, horas trabajadas
- **Exportar Datos:** PDF, Excel

### 👤 **PANEL EMPLEADO** (`/empleado/dashboard`)
- **Mi Asistencia:** Ver registros propios
- **Historial:** Últimos registros de entrada/salida
- **Estadísticas:** Horas trabajadas, días presentes

---

## 🔄 **FLUJO DEL SISTEMA**

### 1. **Registro de Asistencia (ESP32 + RFID)**
```
Empleado acerca tarjeta → ESP32 lee UID → Envía a API → Registra entrada/salida
```

### 2. **API Endpoints** (`/api/`)
- `POST /api/asistencia` - Registrar entrada/salida
- `GET /api/ping` - Verificar conexión

### 3. **Estructura de Base de Datos**
- `usuarios` - Empleados y sus datos
- `dispositivos` - Lectores RFID registrados
- `tarjetas_rfid` - Tarjetas asignadas
- `registros_asistencia` - Entradas y salidas
- `logs_sistema` - Auditoría

---

## 🛠 **CONFIGURACIÓN ESP32**

### Hardware Necesario:
- ESP32 DevKit
- Lector RFID RC522
- Tarjetas/Tags RFID
- Buzzer (opcional)
- LED indicadores (opcional)

### Archivo de configuración: `esp32/lector_asistencia.ino`

---

## 📱 **CÓMO USAR EL SISTEMA**

### **Para Empleados:**
1. Acercarse al lector RFID con su tarjeta
2. Escuchar el beep de confirmación
3. Ver el registro en su panel web

### **Para RRHH:**
1. Login con credenciales RRHH
2. Ir a `/rrhh/dashboard`
3. Generar reportes por fechas/empleados
4. Exportar datos si es necesario

### **Para Administradores:**
1. Login con credenciales Admin
2. Ir a `/admin/dashboard`
3. Gestionar usuarios, dispositivos y configuración
4. Monitorear el sistema completo

---

## 🔧 **MANTENIMIENTO**

### **Archivos Importantes:**
- `config/database.php` - Configuración de BD
- `config/bootstrap.php` - Configuración general
- `routes.php` - Definición de rutas
- `api/index.php` - API para ESP32

### **Logs del Sistema:**
- Tabla `logs_sistema` en la base de datos
- Registro de logins, cambios, errores

### **Backup de Base de Datos:**
```sql
mysqldump -u root control_asistencia > backup_asistencia.sql
```

---

## 🚨 **SOLUCIÓN DE PROBLEMAS**

### **Sistema no carga:**
1. Verificar Apache y MySQL en XAMPP
2. Comprobar permisos de archivos
3. Revisar logs de PHP

### **ESP32 no conecta:**
1. Verificar configuración WiFi
2. Comprobar URL de la API
3. Verificar token del dispositivo

### **Tarjeta RFID no funciona:**
1. Verificar que esté registrada
2. Comprobar UID en la base de datos
3. Verificar asignación al empleado

---

## 📞 **SOPORTE TÉCNICO**

Para problemas técnicos:
- Revisar logs en `logs_sistema`
- Verificar configuración en `config/`
- Comprobar conexión de red ESP32

---

**🎉 ¡Sistema listo para usar!**

Comienza con las credenciales de administrador para configurar usuarios y dispositivos.