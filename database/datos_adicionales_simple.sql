-- Script Simple de Datos de Prueba Adicionales
-- Usando IDs existentes para evitar problemas de foreign key

-- Agregar más dispositivos
INSERT IGNORE INTO dispositivos (nombre, ubicacion, token_dispositivo, ip_address, ultimo_ping, estado) VALUES
('Lector Cafetería', 'Área de Descanso - Piso 2', 'cf47d8e9a1b2c3d4e5f6789012345678901234567890abcdef123456789abcdef', '192.168.1.105', NOW() - INTERVAL 2 MINUTE, 'activo'),
('Lector Oficinas', 'Pasillo Principal - Piso 3', 'ab12cd34ef56789012345678901234567890abcdef123456789012345678901234', '192.168.1.106', NOW() - INTERVAL 1 HOUR, 'activo'),
('Lector Parqueadero', 'Entrada Parqueadero', '9876543210abcdef123456789012345678901234567890abcdef1234567890abcd', NULL, NULL, 'inactivo'),
('Lector Sala Juntas', 'Sala de Reuniones A', 'fed123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef', '192.168.1.107', NOW() - INTERVAL 20 MINUTE, 'activo'),
('Lector Desarrollo', 'Área de Desarrollo - Piso 4', '147258369abcdef0123456789abcdef0123456789abcdef0123456789abcdef012', '192.168.1.108', NOW() - INTERVAL 30 MINUTE, 'activo');

-- Agregar más tarjetas RFID
INSERT IGNORE INTO tarjetas_rfid (uid_tarjeta, usuario_id, estado, fecha_asignacion) VALUES
-- Tarjetas para empleados existentes
('Q7R8S9T0', 5, 'activa', '2025-01-15 10:00:00'),  -- Ana Martínez
('U1V2W3X4', 6, 'activa', '2025-02-01 09:30:00'),  -- Carlos López
('Y5Z6A7B8', 7, 'activa', '2025-02-15 11:00:00'),  -- Laura Rodríguez
('C9D0E1F2', 8, 'activa', '2025-03-01 08:45:00'),  -- Miguel Hernández
('G3H4I5J6', 9, 'activa', '2025-03-15 14:30:00'),  -- Sofia González
('K7L8M9N0', 10, 'activa', '2025-04-01 16:00:00'), -- Roberto Jiménez
('O1P2Q3R4', 11, 'activa', '2025-04-15 13:15:00'), -- Patricia Morales
('S5T6U7V8', 12, 'activa', '2024-12-01 12:00:00'), -- Diego Vargas
('W9X0Y1Z2', 13, 'activa', '2024-11-15 15:45:00'), -- Carmen Torres
('A3B4C5D6', 14, 'activa', '2024-10-01 17:20:00'), -- Elena Castillo
-- Tarjetas sin asignar para testing
('E7F8G9H0', NULL, 'activa', '2025-11-01 00:00:00'),
('I1J2K3L4', NULL, 'activa', '2025-11-02 00:00:00'),
('M5N6O7P8', NULL, 'activa', '2025-11-03 00:00:00'),
-- Tarjeta bloqueada
('Q9R0S1T2', NULL, 'inactiva', '2025-11-04 00:00:00'),
-- Tarjeta del usuario inactivo
('INACTIVE1', 15, 'inactiva', '2024-01-01 00:00:00');

-- Asistencias variadas para testing
-- Semana actual con tardanzas para detectar patrones
INSERT IGNORE INTO asistencias (usuario_id, dispositivo_id, fecha_hora, tipo, es_tardanza) VALUES
-- Lunes (hace 6 días)
(5, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 8 HOUR + INTERVAL 20 MINUTE, 'entrada', 1),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 17 HOUR + INTERVAL 15 MINUTE, 'salida', 0),
(7, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 8 HOUR + INTERVAL 30 MINUTE, 'entrada', 1),
(7, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY) + INTERVAL 17 HOUR + INTERVAL 30 MINUTE, 'salida', 0),

-- Martes (hace 5 días)
(5, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 7 HOUR + INTERVAL 55 MINUTE, 'entrada', 0),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 9 HOUR, 'entrada', 1), -- Tardanza fuerte
(6, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 18 HOUR, 'salida', 0),
(7, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 8 HOUR + INTERVAL 25 MINUTE, 'entrada', 1),
(7, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 17 HOUR + INTERVAL 35 MINUTE, 'salida', 0),

-- Miércoles (hace 4 días)
(5, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 8 HOUR + INTERVAL 5 MINUTE, 'entrada', 0),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 8 HOUR + INTERVAL 30 MINUTE, 'entrada', 1),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 17 HOUR + INTERVAL 20 MINUTE, 'salida', 0),
(8, 4, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(8, 4, DATE_SUB(CURDATE(), INTERVAL 4 DAY) + INTERVAL 17 HOUR, 'salida', 0),

-- Jueves (hace 3 días) - Ausencias para testing
(5, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 17 HOUR, 'salida', 0),
-- Carlos ausente (ID 6)
(8, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(8, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY) + INTERVAL 17 HOUR, 'salida', 0),

-- Viernes (hace 2 días)
(5, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR, 'entrada', 0),
(5, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR + INTERVAL 5 MINUTE, 'entrada', 0),
(6, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR, 'salida', 0),
(7, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 8 HOUR + INTERVAL 35 MINUTE, 'entrada', 1),
(7, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 17 HOUR + INTERVAL 30 MINUTE, 'salida', 0),

-- Hoy - Para testing de tiempo real
(5, 1, CURDATE() + INTERVAL 8 HOUR, 'entrada', 0),
(8, 4, CURDATE() + INTERVAL 8 HOUR + INTERVAL 5 MINUTE, 'entrada', 0),
(9, 5, CURDATE() + INTERVAL 8 HOUR, 'entrada', 0),
(10, 6, CURDATE() + INTERVAL 7 HOUR + INTERVAL 30 MINUTE, 'entrada', 0),
(12, 1, CURDATE() + INTERVAL 8 HOUR + INTERVAL 45 MINUTE, 'entrada', 1), -- Tardanza para alerta
-- Marcación sospechosa (misma persona en dos lugares muy rápido)
(11, 1, CURDATE() + INTERVAL 9 HOUR, 'entrada', 0),
(11, 5, CURDATE() + INTERVAL 9 HOUR + INTERVAL 2 MINUTE, 'entrada', 0);

-- Datos históricos del mes pasado
INSERT IGNORE INTO asistencias (usuario_id, dispositivo_id, fecha_hora, tipo, es_tardanza) VALUES
(1, 1, '2025-10-15 08:00:00', 'entrada', 0),
(1, 1, '2025-10-15 17:00:00', 'salida', 0),
(2, 1, '2025-10-15 08:15:00', 'entrada', 0),
(2, 1, '2025-10-15 17:15:00', 'salida', 0),
(3, 2, '2025-10-15 08:30:00', 'entrada', 1),
(3, 2, '2025-10-15 17:30:00', 'salida', 0),
(5, 1, '2025-10-16 08:00:00', 'entrada', 0),
(5, 1, '2025-10-16 17:00:00', 'salida', 0),
(6, 2, '2025-10-16 09:00:00', 'entrada', 1),
(6, 2, '2025-10-16 18:00:00', 'salida', 0);

-- Auditoría API
INSERT IGNORE INTO auditoria_api (dispositivo_id, uid_tarjeta, ip_origen, timestamp_request, respuesta, mensaje) VALUES
(1, 'A1B2C3D4', '192.168.1.105', NOW() - INTERVAL 1 HOUR, 'exito', 'Marcación registrada correctamente'),
(2, 'E5F6G7H8', '192.168.1.106', NOW() - INTERVAL 2 HOUR, 'exito', 'Marcación registrada correctamente'),
(1, 'INVALID123', '192.168.1.105', NOW() - INTERVAL 3 HOUR, 'tarjeta_no_encontrada', 'UID de tarjeta no registrado'),
(1, 'Q9R0S1T2', '192.168.1.105', NOW() - INTERVAL 5 HOUR, 'error', 'Tarjeta bloqueada');

-- Logs del sistema
INSERT IGNORE INTO logs_sistema (usuario_id, accion, tabla_afectada, id_registro, descripcion, ip_address, user_agent) VALUES
(1, 'LOGIN', 'usuarios', 1, 'Inicio de sesión exitoso', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(1, 'CREATE', 'dispositivos', 4, 'Creación de nuevo dispositivo', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, 'EXPORT', 'asistencias', NULL, 'Exportación de reporte de asistencias', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- Configuraciones adicionales
INSERT IGNORE INTO configuracion_sistema (clave, valor, descripcion) VALUES
('hora_inicio_jornada', '08:00:00', 'Hora estándar de inicio de jornada laboral'),
('hora_fin_jornada', '17:00:00', 'Hora estándar de fin de jornada laboral'),
('dias_laborales', 'L,M,Mi,J,V', 'Días laborales de la semana'),
('timeout_ping_dispositivo', '15', 'Minutos de timeout para considerar dispositivo offline'),
('notificaciones_browser', '1', 'Habilitar notificaciones del navegador'),
('intervalo_actualizacion_dashboard', '30', 'Segundos entre actualizaciones automáticas del dashboard');

-- Verificación final
SELECT 'DATOS DE PRUEBA AGREGADOS CORRECTAMENTE' as mensaje;
SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE activo = 1) as usuarios_activos,
    (SELECT COUNT(*) FROM dispositivos) as total_dispositivos,
    (SELECT COUNT(*) FROM tarjetas_rfid) as total_tarjetas,
    (SELECT COUNT(*) FROM asistencias) as total_asistencias;

-- Empleados con tardanzas esta semana (para testing alertas)
SELECT 
    u.nombres, u.apellidos, COUNT(*) as tardanzas_semana
FROM asistencias a 
JOIN usuarios u ON a.usuario_id = u.id 
WHERE a.es_tardanza = 1 
AND a.fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY u.id 
HAVING tardanzas_semana >= 2;

-- Empleados presentes hoy
SELECT u.nombres, u.apellidos, 'Presente' as estado
FROM usuarios u
JOIN asistencias a ON u.id = a.usuario_id 
WHERE DATE(a.fecha_hora) = CURDATE() AND u.activo = 1
GROUP BY u.id
UNION
SELECT u.nombres, u.apellidos, 'Ausente' as estado
FROM usuarios u
LEFT JOIN asistencias a ON u.id = a.usuario_id AND DATE(a.fecha_hora) = CURDATE()
WHERE u.activo = 1 AND u.rol = 'empleado' AND a.id IS NULL;