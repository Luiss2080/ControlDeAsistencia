-- =====================================================================
-- SCRIPT DE DATOS DE PRUEBA COMPLETOS PARA CONTROL DE ASISTENCIA
-- =====================================================================
-- Este script agrega datos suficientes para probar todas las funcionalidades

-- Agregar más usuarios para testing completo
INSERT IGNORE INTO usuarios (numero_empleado, nombres, apellidos, email, telefono, puesto, fecha_ingreso, horario_entrada, horario_salida, password_hash, rol, activo) VALUES
-- Más empleados regulares (usando IGNORE para evitar duplicados)
('EMP002', 'Ana', 'Martínez', 'ana.martinez@empresa.com', '555-0102', 'Analista de Sistemas', '2025-01-15', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
('EMP003', 'Carlos', 'López', 'carlos.lopez@empresa.com', '555-0103', 'Desarrollador Senior', '2025-02-01', '09:00:00', '18:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
('EMP004', 'Laura', 'Rodríguez', 'laura.rodriguez@empresa.com', '555-0104', 'Diseñadora UX', '2025-02-15', '08:30:00', '17:30:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
('EMP005', 'Miguel', 'Hernández', 'miguel.hernandez@empresa.com', '555-0105', 'QA Tester', '2025-03-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
('EMP006', 'Sofia', 'González', 'sofia.gonzalez@empresa.com', '555-0106', 'Project Manager', '2025-03-15', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
('EMP007', 'Roberto', 'Jiménez', 'roberto.jimenez@empresa.com', '555-0107', 'DevOps Engineer', '2025-04-01', '07:30:00', '16:30:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
('EMP008', 'Patricia', 'Morales', 'patricia.morales@empresa.com', '555-0108', 'Business Analyst', '2025-04-15', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
-- Supervisores y coordinadores
('SUP001', 'Diego', 'Vargas', 'diego.vargas@empresa.com', '555-0201', 'Supervisor de Desarrollo', '2024-12-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
('SUP002', 'Carmen', 'Torres', 'carmen.torres@empresa.com', '555-0202', 'Coordinadora de QA', '2024-11-15', '08:30:00', '17:30:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1),
-- Personal de RRHH adicional
('RH002', 'Elena', 'Castillo', 'elena.castillo@empresa.com', '555-0301', 'Asistente de RRHH', '2024-10-01', '08:00:00', '17:00:00', '$2y$10$IVzEhZp9Pu4pQceTU9ujkOl0lcIgKiWVkK3QZB02c2ruiEVg/aIpK', 'rrhh', 1),
-- Usuario inactivo para testing
('EMP999', 'Usuario', 'Inactivo', 'inactivo@empresa.com', '555-9999', 'Empleado Inactivo', '2024-01-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 0);

-- Obtener los IDs reales de los usuarios para las foreign keys
SET @ana_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP002');
SET @carlos_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP003');
SET @laura_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP004');
SET @miguel_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP005');
SET @sofia_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP006');
SET @roberto_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP007');
SET @patricia_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP008');
SET @diego_id = (SELECT id FROM usuarios WHERE numero_empleado = 'SUP001');
SET @carmen_id = (SELECT id FROM usuarios WHERE numero_empleado = 'SUP002');
SET @elena_id = (SELECT id FROM usuarios WHERE numero_empleado = 'RH002');
SET @inactivo_id = (SELECT id FROM usuarios WHERE numero_empleado = 'EMP999');

-- Agregar más dispositivos para testing
INSERT IGNORE INTO dispositivos (nombre, ubicacion, token_dispositivo, ip_address, ultimo_ping, estado) VALUES
('Lector Cafetería', 'Área de Descanso - Piso 2', 'cf47d8e9a1b2c3d4e5f6789012345678901234567890abcdef123456789abcdef', '192.168.1.105', NOW() - INTERVAL 2 MINUTE, 'activo'),
('Lector Oficinas', 'Pasillo Principal - Piso 3', 'ab12cd34ef56789012345678901234567890abcdef123456789012345678901234', '192.168.1.106', NOW() - INTERVAL 1 HOUR, 'activo'),
('Lector Parqueadero', 'Entrada Parqueadero', '9876543210abcdef123456789012345678901234567890abcdef1234567890abcd', NULL, NULL, 'inactivo'),
('Lector Sala Juntas', 'Sala de Reuniones A', 'fed123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef', '192.168.1.107', NOW() - INTERVAL 20 MINUTE, 'activo'),
('Lector Desarrollo', 'Área de Desarrollo - Piso 4', '147258369abcdef0123456789abcdef0123456789abcdef0123456789abcdef012', '192.168.1.108', NOW() - INTERVAL 30 MINUTE, 'activo');

-- Agregar más tarjetas RFID usando las variables
INSERT IGNORE INTO tarjetas_rfid (uid_tarjeta, usuario_id, estado, fecha_asignacion) VALUES
-- Tarjetas para nuevos empleados (usar los IDs reales)
('Q7R8S9T0', @ana_id, 'activa', '2025-01-15 10:00:00'),
('U1V2W3X4', @carlos_id, 'activa', '2025-02-01 09:30:00'),
('Y5Z6A7B8', @laura_id, 'activa', '2025-02-15 11:00:00'),
('C9D0E1F2', @miguel_id, 'activa', '2025-03-01 08:45:00'),
('G3H4I5J6', @sofia_id, 'activa', '2025-03-15 14:30:00'),
('K7L8M9N0', @roberto_id, 'activa', '2025-04-01 16:00:00'),
('O1P2Q3R4', @patricia_id, 'activa', '2025-04-15 13:15:00'),
('S5T6U7V8', @diego_id, 'activa', '2024-12-01 12:00:00'),
('W9X0Y1Z2', @carmen_id, 'activa', '2024-11-15 15:45:00'),
('A3B4C5D6', @elena_id, 'activa', '2024-10-01 17:20:00'),
-- Tarjetas sin asignar para testing
('E7F8G9H0', NULL, 'activa', '2025-11-01 00:00:00'),
('I1J2K3L4', NULL, 'activa', '2025-11-02 00:00:00'),
('M5N6O7P8', NULL, 'activa', '2025-11-03 00:00:00'),
-- Tarjeta bloqueada para testing
('Q9R0S1T2', NULL, 'inactiva', '2025-11-04 00:00:00'),
-- Tarjeta del usuario inactivo
('INACTIVE1', @inactivo_id, 'inactiva', '2024-01-01 00:00:00');

-- Generar asistencias variadas para los últimos 30 días para testing completo
-- Esto incluye patrones realistas de asistencia, tardanzas, ausencias, etc.

-- Asistencias de la semana actual (últimos 7 días)
-- Lunes (hace 6 días) - Día normal con algunas tardanzas
INSERT IGNORE INTO asistencias (usuario_id, dispositivo_id, fecha_hora, tipo, es_tardanza) VALUES
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 8 HOUR + INTERVAL 20 MINUTE, 'entrada', 1),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 17 HOUR + INTERVAL 15 MINUTE, 'salida', 0),
(@laura_id, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 8 HOUR + INTERVAL 30 MINUTE, 'entrada', 1),
(@laura_id, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 17 HOUR + INTERVAL 30 MINUTE, 'salida', 0),
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 17 HOUR, 'salida', 0),

-- Martes (hace 5 días)
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 7 HOUR + INTERVAL 55 MINUTE, 'entrada', 0),
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR + INTERVAL 5 MINUTE, 'salida', 0),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 9 HOUR + INTERVAL 10 MINUTE, 'entrada', 1),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 18 HOUR, 'salida', 0),
(@laura_id, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 8 HOUR + INTERVAL 25 MINUTE, 'entrada', 1),
(@laura_id, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR + INTERVAL 35 MINUTE, 'salida', 0),
(@patricia_id, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@patricia_id, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR, 'salida', 0),

-- Miércoles (hace 4 días) 
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 8 HOUR + INTERVAL 5 MINUTE, 'entrada', 0),
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 8 HOUR + INTERVAL 30 MINUTE, 'entrada', 1),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 17 HOUR + INTERVAL 20 MINUTE, 'salida', 0),
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(@roberto_id, 6, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 7 HOUR + INTERVAL 30 MINUTE, 'entrada', 0),
(@roberto_id, 6, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 16 HOUR + INTERVAL 30 MINUTE, 'salida', 0),

-- Jueves (hace 3 días) - Ausencia de algunos empleados
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 8 HOUR + INTERVAL 2 MINUTE, 'entrada', 0),
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 17 HOUR, 'salida', 0),
-- Carlos López ausente (sin registros)
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(@patricia_id, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@patricia_id, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 17 HOUR, 'salida', 0),

-- Viernes (hace 2 días)
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@ana_id, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR + INTERVAL 5 MINUTE, 'entrada', 0),
(@carlos_id, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(@laura_id, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR + INTERVAL 35 MINUTE, 'entrada', 1),
(@laura_id, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR + INTERVAL 30 MINUTE, 'salida', 0),
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(@miguel_id, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR, 'salida', 0),

-- Hoy - Algunas entradas pero aún no salidas (para testing tiempo real)
(@ana_id, 1, CURDATE() + INTERVAL 8 HOUR, 'entrada', 0),
(@miguel_id, 4, CURDATE() + INTERVAL 8 HOUR + INTERVAL 5 MINUTE, 'entrada', 0),
(@patricia_id, 5, CURDATE() + INTERVAL 8 HOUR, 'entrada', 0),
(@roberto_id, 6, CURDATE() + INTERVAL 7 HOUR + INTERVAL 30 MINUTE, 'entrada', 0),
(@diego_id, 1, CURDATE() + INTERVAL 8 HOUR + INTERVAL 45 MINUTE, 'entrada', 1),
-- Algunas marcaciones sospechosas (misma persona en diferentes lugares muy rápido)
(@sofia_id, 1, CURDATE() + INTERVAL 9 HOUR, 'entrada', 0),
(@sofia_id, 5, CURDATE() + INTERVAL 9 HOUR + INTERVAL 2 MINUTE, 'entrada', 0);

-- Asistencias del mes anterior para reportes históricos
INSERT INTO asistencias (usuario_id, dispositivo_id, fecha_hora, tipo, es_tardanza) VALUES
-- Datos del mes pasado (octubre) para reportes
(1, 1, '2025-10-15 08:00:00', 'entrada', 0),
(1, 1, '2025-10-15 17:00:00', 'salida', 0),
(2, 1, '2025-10-15 08:15:00', 'entrada', 0),
(2, 1, '2025-10-15 17:15:00', 'salida', 0),
(3, 2, '2025-10-15 08:30:00', 'entrada', 1), -- Tardanza histórica
(3, 2, '2025-10-15 17:30:00', 'salida', 0),
(4, 1, '2025-10-16 08:00:00', 'entrada', 0),
(4, 1, '2025-10-16 17:00:00', 'salida', 0),
(5, 2, '2025-10-16 09:00:00', 'entrada', 1),
(5, 2, '2025-10-16 18:00:00', 'salida', 0),
-- Más datos históricos para estadísticas robustas
(1, 1, '2025-10-17 08:00:00', 'entrada', 0),
(1, 1, '2025-10-17 17:00:00', 'salida', 0),
(2, 1, '2025-10-17 08:00:00', 'entrada', 0),
(2, 1, '2025-10-17 17:00:00', 'salida', 0),
(3, 2, '2025-10-17 08:00:00', 'entrada', 0),
(3, 2, '2025-10-17 17:00:00', 'salida', 0),
(4, 1, '2025-10-18 08:00:00', 'entrada', 0),
(4, 1, '2025-10-18 17:00:00', 'salida', 0);

-- Datos de auditoría API para testing
INSERT INTO auditoria_api (dispositivo_id, uid_tarjeta, ip_origen, timestamp_request, respuesta, mensaje) VALUES
(1, 'A1B2C3D4', '192.168.1.105', NOW() - INTERVAL 1 HOUR, 'exito', 'Marcación registrada correctamente'),
(2, 'E5F6G7H8', '192.168.1.106', NOW() - INTERVAL 2 HOUR, 'exito', 'Marcación registrada correctamente'),
(1, 'INVALID123', '192.168.1.105', NOW() - INTERVAL 3 HOUR, 'tarjeta_no_encontrada', 'UID de tarjeta no registrado'),
(3, 'A1B2C3D4', '192.168.1.107', NOW() - INTERVAL 4 HOUR, 'dispositivo_invalido', 'Token de dispositivo inválido'),
(1, 'Q9R0S1T2', '192.168.1.105', NOW() - INTERVAL 5 HOUR, 'error', 'Tarjeta bloqueada'),
(2, 'U1V2W3X4', '192.168.1.106', NOW() - INTERVAL 6 HOUR, 'exito', 'Marcación registrada correctamente');

-- Logs del sistema para auditoría
INSERT INTO logs_sistema (usuario_id, accion, tabla_afectada, id_registro, descripcion, ip_address, user_agent) VALUES
(1, 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(1, 'CREATE', 'dispositivos', 4, 'Creación de nuevo dispositivo: Lector Cafetería', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(1, 'CREATE', 'tarjetas_rfid', 5, 'Creación de nueva tarjeta RFID: Q7R8S9T0', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(1, 'UPDATE', 'tarjetas_rfid', 5, 'Asignación de tarjeta Q7R8S9T0 a usuario Ana Martínez', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'LOGIN', 'usuarios', 2, 'Inicio de sesión exitoso', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'EXPORT', 'asistencias', NULL, 'Exportación de reporte de asistencias a Excel', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- Configuraciones adicionales del sistema
INSERT IGNORE INTO configuracion_sistema (clave, valor, descripcion) VALUES
('hora_inicio_jornada', '08:00:00', 'Hora estándar de inicio de jornada laboral'),
('hora_fin_jornada', '17:00:00', 'Hora estándar de fin de jornada laboral'),
('dias_laborales', 'L,M,Mi,J,V', 'Días laborales de la semana'),
('timeout_ping_dispositivo', '15', 'Minutos de timeout para considerar dispositivo offline'),
('max_marcaciones_dia', '10', 'Máximo número de marcaciones permitidas por día por empleado'),
('notificaciones_email', '1', 'Habilitar notificaciones por email'),
('notificaciones_browser', '1', 'Habilitar notificaciones del navegador'),
('intervalo_actualizacion_dashboard', '30', 'Segundos entre actualizaciones automáticas del dashboard'),
('max_intentos_login', '5', 'Máximo número de intentos de login antes de bloqueo');

-- =====================================================================
-- VERIFICACIONES FINALES
-- =====================================================================

-- Verificar integridad de datos
SELECT 'VERIFICACIÓN DE DATOS COMPLETADA' as mensaje;
SELECT 
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM dispositivos) as total_dispositivos, 
    (SELECT COUNT(*) FROM tarjetas_rfid) as total_tarjetas,
    (SELECT COUNT(*) FROM asistencias) as total_asistencias,
    (SELECT COUNT(*) FROM configuracion_sistema) as total_configuraciones;

-- Mostrar usuarios con tardanzas para testing de alertas
SELECT 
    u.nombres, u.apellidos, COUNT(*) as tardanzas_esta_semana
FROM asistencias a 
JOIN usuarios u ON a.usuario_id = u.id 
WHERE a.es_tardanza = 1 
AND a.fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY u.id 
HAVING tardanzas_esta_semana >= 2
ORDER BY tardanzas_esta_semana DESC;

-- Mostrar empleados ausentes hoy (para testing de alertas)
SELECT u.nombres, u.apellidos, u.numero_empleado
FROM usuarios u
LEFT JOIN asistencias a ON u.id = a.usuario_id AND DATE(a.fecha_hora) = CURDATE()
WHERE u.activo = 1 AND u.rol = 'empleado' AND a.id IS NULL
ORDER BY u.apellidos, u.nombres;