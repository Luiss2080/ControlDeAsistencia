-- Sistema de Control de Asistencia - Esquema Completo
-- Base de datos actualizada con todas las funcionalidades

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `control_asistencia` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `control_asistencia`;

-- =====================================================
-- TABLA: departamentos
-- =====================================================
CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `jefe_departamento_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_departamento_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: horarios_trabajo
-- =====================================================
CREATE TABLE `horarios_trabajo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `dias_laborales` json NOT NULL COMMENT 'Array de números: 1=Lunes, 7=Domingo',
  `tolerancia_minutos` int(11) DEFAULT 15,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_empleado` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `horario_id` int(11) DEFAULT NULL,
  `rol` enum('admin','rrhh','empleado') NOT NULL DEFAULT 'empleado',
  `fecha_ingreso` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_numero_empleado` (`numero_empleado`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `fk_usuario_departamento` (`departamento_id`),
  KEY `fk_usuario_horario` (`horario_id`),
  CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_usuario_horario` FOREIGN KEY (`horario_id`) REFERENCES `horarios_trabajo` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: dispositivos
-- =====================================================
CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `configuracion` json DEFAULT NULL,
  `estado` enum('online','offline','mantenimiento','error') DEFAULT 'offline',
  `version_firmware` varchar(50) DEFAULT NULL,
  `ultima_conexion` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_dispositivo_token` (`token`),
  KEY `idx_dispositivo_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: tarjetas_rfid
-- =====================================================
CREATE TABLE `tarjetas_rfid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid_tarjeta` varchar(50) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('activa','inactiva','perdida','dañada') DEFAULT 'activa',
  `fecha_asignacion` timestamp NULL DEFAULT NULL,
  `fecha_desasignacion` timestamp NULL DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_uid_tarjeta` (`uid_tarjeta`),
  KEY `fk_tarjeta_usuario` (`usuario_id`),
  KEY `idx_tarjeta_estado` (`estado`),
  CONSTRAINT `fk_tarjeta_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: registros_asistencia
-- =====================================================
CREATE TABLE `registros_asistencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tarjeta_id` int(11) DEFAULT NULL,
  `dispositivo_id` int(11) DEFAULT NULL,
  `uid_tarjeta` varchar(50) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_dispositivo` varchar(45) DEFAULT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `estado` enum('procesado','pendiente','error') DEFAULT 'procesado',
  `valido` tinyint(1) DEFAULT 1,
  `fuera_horario` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `datos_raw` json DEFAULT NULL,
  `datos_correccion` json DEFAULT NULL,
  `anulado_por` int(11) DEFAULT NULL,
  `fecha_anulacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_registro_usuario` (`usuario_id`),
  KEY `fk_registro_tarjeta` (`tarjeta_id`),
  KEY `fk_registro_dispositivo` (`dispositivo_id`),
  KEY `idx_registro_fecha` (`fecha_hora`),
  KEY `idx_registro_tipo` (`tipo`),
  KEY `idx_registro_valido` (`valido`),
  CONSTRAINT `fk_registro_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_registro_tarjeta` FOREIGN KEY (`tarjeta_id`) REFERENCES `tarjetas_rfid` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_registro_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: marcaciones_fallidas
-- =====================================================
CREATE TABLE `marcaciones_fallidas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid_tarjeta` varchar(50) NOT NULL,
  `dispositivo_id` int(11) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `error` text NOT NULL,
  `datos_raw` json DEFAULT NULL,
  `ip_dispositivo` varchar(45) DEFAULT NULL,
  `estado` enum('error','procesada') DEFAULT 'error',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_fallida_dispositivo` (`dispositivo_id`),
  KEY `idx_fallida_fecha` (`fecha_hora`),
  CONSTRAINT `fk_fallida_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: sesiones
-- =====================================================
CREATE TABLE `sesiones` (
  `id` varchar(128) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `datos_sesion` json DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_sesion_usuario` (`usuario_id`),
  KEY `idx_sesion_actividad` (`last_activity`),
  CONSTRAINT `fk_sesion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: logs_sistema
-- =====================================================
CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(50) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `datos_anteriores` json DEFAULT NULL,
  `datos_nuevos` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_log_usuario` (`usuario_id`),
  KEY `idx_log_accion` (`accion`),
  KEY `idx_log_tabla` (`tabla_afectada`),
  KEY `idx_log_fecha` (`created_at`),
  CONSTRAINT `fk_log_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: configuracion_sistema
-- =====================================================
CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('string','int','bool','json') DEFAULT 'string',
  `categoria` varchar(50) DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_config_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: reportes_generados
-- =====================================================
CREATE TABLE `reportes_generados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo_reporte` varchar(50) NOT NULL,
  `parametros` json DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `estado` enum('generando','completado','error') DEFAULT 'generando',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_reporte_usuario` (`usuario_id`),
  KEY `idx_reporte_tipo` (`tipo_reporte`),
  CONSTRAINT `fk_reporte_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTAR DATOS INICIALES
-- =====================================================

-- Departamentos iniciales
INSERT INTO `departamentos` (`nombre`, `descripcion`) VALUES
('Administración', 'Departamento administrativo'),
('Recursos Humanos', 'Gestión de personal'),
('Sistemas', 'Tecnologías de información'),
('Operaciones', 'Operaciones generales');

-- Horarios de trabajo
INSERT INTO `horarios_trabajo` (`nombre`, `hora_entrada`, `hora_salida`, `dias_laborales`, `tolerancia_minutos`) VALUES
('Horario Normal', '08:00:00', '17:00:00', '[1,2,3,4,5]', 15),
('Horario Matutino', '06:00:00', '14:00:00', '[1,2,3,4,5]', 10),
('Horario Vespertino', '14:00:00', '22:00:00', '[1,2,3,4,5]', 10);

-- Usuarios iniciales
INSERT INTO `usuarios` (`numero_empleado`, `nombres`, `apellidos`, `email`, `password_hash`, `departamento_id`, `horario_id`, `rol`, `fecha_ingreso`) VALUES
('ADM001', 'Administrador', 'del Sistema', 'admin@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 'admin', '2024-01-01'),
('RH001', 'María', 'García', 'rrhh@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, 'rrhh', '2024-01-01'),
('EMP001', 'Juan', 'Pérez', 'juan@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'empleado', '2024-01-01');

-- Dispositivos iniciales
INSERT INTO `dispositivos` (`nombre`, `token`, `ubicacion`, `configuracion`) VALUES
('Lector Principal', 'esp32_token_principal_2024', 'Entrada Principal', '{"led_enable": true, "buzzer_enable": true, "debug": false}'),
('Lector Secundario', 'esp32_token_secundario_2024', 'Entrada Trasera', '{"led_enable": true, "buzzer_enable": true, "debug": false}');

-- Tarjetas RFID iniciales
INSERT INTO `tarjetas_rfid` (`uid_tarjeta`, `usuario_id`, `estado`, `fecha_asignacion`) VALUES
('A1B2C3D4', 1, 'activa', NOW()),
('E5F6G7H8', 2, 'activa', NOW()),
('I9J0K1L2', 3, 'activa', NOW());

-- Configuración del sistema
INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`, `tipo`, `categoria`) VALUES
('minutos_entre_marcaciones', '5', 'Minutos mínimos entre marcaciones del mismo usuario', 'int', 'asistencia'),
('tolerancia_entrada', '15', 'Minutos de tolerancia para entrada', 'int', 'horarios'),
('tolerancia_salida', '30', 'Minutos de tolerancia para salida', 'int', 'horarios'),
('backup_automatico', '1', 'Activar backup automático diario', 'bool', 'sistema'),
('zona_horaria', 'America/Mexico_City', 'Zona horaria del sistema', 'string', 'sistema'),
('empresa_nombre', 'Mi Empresa', 'Nombre de la empresa', 'string', 'general'),
('empresa_logo', '', 'URL del logo de la empresa', 'string', 'general');

-- Crear índices adicionales para optimización
CREATE INDEX `idx_registro_usuario_fecha` ON `registros_asistencia` (`usuario_id`, `fecha_hora`);
CREATE INDEX `idx_registro_fecha_tipo` ON `registros_asistencia` (`fecha_hora`, `tipo`);
CREATE INDEX `idx_usuario_activo` ON `usuarios` (`activo`);
CREATE INDEX `idx_tarjeta_usuario_estado` ON `tarjetas_rfid` (`usuario_id`, `estado`);

-- Crear triggers para auditoría
DELIMITER $$

CREATE TRIGGER `tr_usuarios_log` AFTER UPDATE ON `usuarios`
FOR EACH ROW BEGIN
    INSERT INTO logs_sistema (usuario_id, accion, tabla_afectada, id_registro, descripcion, datos_anteriores, datos_nuevos)
    VALUES (NEW.id, 'UPDATE', 'usuarios', NEW.id, 'Usuario actualizado',
            JSON_OBJECT('nombres', OLD.nombres, 'apellidos', OLD.apellidos, 'email', OLD.email, 'activo', OLD.activo),
            JSON_OBJECT('nombres', NEW.nombres, 'apellidos', NEW.apellidos, 'email', NEW.email, 'activo', NEW.activo));
END$$

CREATE TRIGGER `tr_tarjetas_log` AFTER UPDATE ON `tarjetas_rfid`
FOR EACH ROW BEGIN
    INSERT INTO logs_sistema (usuario_id, accion, tabla_afectada, id_registro, descripcion, datos_anteriores, datos_nuevos)
    VALUES (NEW.usuario_id, 'UPDATE', 'tarjetas_rfid', NEW.id, 'Tarjeta RFID actualizada',
            JSON_OBJECT('usuario_id', OLD.usuario_id, 'estado', OLD.estado),
            JSON_OBJECT('usuario_id', NEW.usuario_id, 'estado', NEW.estado));
END$$

DELIMITER ;

-- =====================================================
-- VISTAS PARA REPORTES
-- =====================================================

-- Vista para reporte diario de asistencia
CREATE VIEW `v_asistencia_diaria` AS
SELECT 
    u.id as usuario_id,
    u.numero_empleado,
    CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
    d.nombre as departamento,
    DATE(ra.fecha_hora) as fecha,
    MIN(CASE WHEN ra.tipo = 'entrada' THEN ra.fecha_hora END) as primera_entrada,
    MAX(CASE WHEN ra.tipo = 'salida' THEN ra.fecha_hora END) as ultima_salida,
    COUNT(CASE WHEN ra.tipo = 'entrada' THEN 1 END) as total_entradas,
    COUNT(CASE WHEN ra.tipo = 'salida' THEN 1 END) as total_salidas,
    COUNT(CASE WHEN ra.fuera_horario = 1 THEN 1 END) as marcaciones_fuera_horario
FROM usuarios u
LEFT JOIN registros_asistencia ra ON u.id = ra.usuario_id AND ra.valido = 1
LEFT JOIN departamentos d ON u.departamento_id = d.id
WHERE u.activo = 1
GROUP BY u.id, DATE(ra.fecha_hora);

-- Vista para estadísticas de dispositivos
CREATE VIEW `v_estadisticas_dispositivos` AS
SELECT 
    d.id,
    d.nombre,
    d.ubicacion,
    d.estado,
    d.ultima_conexion,
    COUNT(ra.id) as total_marcaciones,
    COUNT(CASE WHEN DATE(ra.fecha_hora) = CURDATE() THEN 1 END) as marcaciones_hoy,
    MAX(ra.fecha_hora) as ultima_marcacion
FROM dispositivos d
LEFT JOIN registros_asistencia ra ON d.id = ra.dispositivo_id AND ra.valido = 1
WHERE d.activo = 1
GROUP BY d.id;

COMMIT;