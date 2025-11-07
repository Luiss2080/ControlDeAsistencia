-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-11-2025 a las 01:14:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `control_asistencia`
--

DELIMITER $$
--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `determinar_tipo_marcacion` (`p_usuario_id` INT, `p_fecha_hora` TIMESTAMP) RETURNS VARCHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC READS SQL DATA BEGIN
    DECLARE ultima_marcacion VARCHAR(10) DEFAULT NULL;
    DECLARE tiempo_desde_ultima INT DEFAULT 0;
    DECLARE minutos_minimos INT DEFAULT 5;
    
    
    SELECT CAST(valor AS UNSIGNED) INTO minutos_minimos 
    FROM configuracion_sistema 
    WHERE clave = 'minutos_entre_marcaciones';
    
    
    SELECT tipo INTO ultima_marcacion
    FROM asistencias 
    WHERE usuario_id = p_usuario_id 
      AND fecha_hora >= DATE_SUB(p_fecha_hora, INTERVAL 24 HOUR)
      AND fecha_hora < p_fecha_hora
    ORDER BY fecha_hora DESC 
    LIMIT 1;
    
    
    IF ultima_marcacion IS NOT NULL THEN
        SELECT TIMESTAMPDIFF(MINUTE, 
            (SELECT fecha_hora FROM asistencias 
             WHERE usuario_id = p_usuario_id 
               AND fecha_hora >= DATE_SUB(p_fecha_hora, INTERVAL 24 HOUR)
               AND fecha_hora < p_fecha_hora
             ORDER BY fecha_hora DESC LIMIT 1),
            p_fecha_hora
        ) INTO tiempo_desde_ultima;
        
        
        IF tiempo_desde_ultima < minutos_minimos THEN
            RETURN 'duplicada';
        END IF;
        
        
        IF ultima_marcacion = 'entrada' THEN
            RETURN 'salida';
        ELSE
            RETURN 'entrada';
        END IF;
    ELSE
        
        RETURN 'entrada';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipo` enum('entrada','salida') NOT NULL,
  `es_tardanza` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `usuario_id`, `dispositivo_id`, `fecha_hora`, `tipo`, `es_tardanza`, `observaciones`, `created_at`) VALUES
(1, 1, 1, '2025-11-02 12:05:00', 'entrada', 0, NULL, '2025-11-02 16:10:19'),
(2, 2, 1, '2025-11-02 12:25:00', 'entrada', 0, NULL, '2025-11-02 16:10:19'),
(3, 3, 1, '2025-11-02 11:55:00', 'entrada', 0, NULL, '2025-11-02 16:10:19'),
(4, 1, 1, '2025-11-02 12:15:00', 'entrada', 1, NULL, '2025-11-02 17:12:30'),
(5, 3, 1, '2025-11-02 11:55:00', 'entrada', 0, NULL, '2025-11-02 17:12:30'),
(6, 1, 1, '2025-11-02 21:05:00', 'salida', 0, NULL, '2025-11-02 17:12:30'),
(7, 3, 2, '2025-11-01 12:30:00', 'entrada', 1, NULL, '2025-11-02 17:12:30'),
(8, 5, 1, '2025-10-31 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(9, 5, 1, '2025-10-31 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(10, 6, 2, '2025-10-31 12:20:00', 'entrada', 1, NULL, '2025-11-06 20:48:49'),
(11, 6, 2, '2025-10-31 21:15:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(12, 7, 1, '2025-10-31 12:30:00', 'entrada', 1, NULL, '2025-11-06 20:48:49'),
(13, 7, 1, '2025-10-31 21:30:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(14, 8, 4, '2025-10-31 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(15, 8, 4, '2025-10-31 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(16, 5, 1, '2025-11-01 11:55:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(17, 5, 1, '2025-11-01 21:05:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(18, 6, 2, '2025-11-01 13:10:00', 'entrada', 1, NULL, '2025-11-06 20:48:49'),
(19, 6, 2, '2025-11-01 22:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(20, 7, 1, '2025-11-01 12:25:00', 'entrada', 1, NULL, '2025-11-06 20:48:49'),
(21, 7, 1, '2025-11-01 21:35:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(22, 11, 5, '2025-11-01 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(23, 11, 5, '2025-11-01 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(24, 5, 1, '2025-11-02 12:05:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(25, 5, 1, '2025-11-02 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(26, 6, 2, '2025-11-02 12:30:00', 'entrada', 1, NULL, '2025-11-06 20:48:49'),
(27, 6, 2, '2025-11-02 21:20:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(28, 8, 4, '2025-11-02 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(29, 8, 4, '2025-11-02 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(30, 10, 6, '2025-11-02 11:30:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(31, 10, 6, '2025-11-02 20:30:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(32, 5, 1, '2025-11-03 12:02:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(33, 5, 1, '2025-11-03 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(34, 8, 4, '2025-11-03 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(35, 8, 4, '2025-11-03 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(36, 11, 5, '2025-11-03 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(37, 11, 5, '2025-11-03 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(38, 5, 1, '2025-11-04 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(39, 5, 1, '2025-11-04 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(40, 6, 2, '2025-11-04 12:05:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(41, 6, 2, '2025-11-04 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(42, 7, 1, '2025-11-04 12:35:00', 'entrada', 1, NULL, '2025-11-06 20:48:49'),
(43, 7, 1, '2025-11-04 21:30:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(44, 8, 4, '2025-11-04 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(45, 8, 4, '2025-11-04 21:00:00', 'salida', 0, NULL, '2025-11-06 20:48:49'),
(46, 5, 1, '2025-11-06 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(47, 8, 4, '2025-11-06 12:05:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(48, 11, 5, '2025-11-06 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(49, 10, 6, '2025-11-06 11:30:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(50, 12, 1, '2025-11-06 12:45:00', 'entrada', 1, NULL, '2025-11-06 20:48:49'),
(51, 9, 1, '2025-11-06 13:00:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(52, 9, 5, '2025-11-06 13:02:00', 'entrada', 0, NULL, '2025-11-06 20:48:49'),
(71, 5, 1, '2025-10-31 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(72, 5, 1, '2025-10-31 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(73, 6, 2, '2025-10-31 12:20:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(74, 6, 2, '2025-10-31 21:15:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(75, 7, 1, '2025-10-31 12:30:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(76, 7, 1, '2025-10-31 21:30:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(77, 5, 1, '2025-11-01 11:55:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(78, 5, 1, '2025-11-01 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(79, 6, 2, '2025-11-01 13:00:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(80, 6, 2, '2025-11-01 22:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(81, 7, 1, '2025-11-01 12:25:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(82, 7, 1, '2025-11-01 21:35:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(83, 5, 1, '2025-11-02 12:05:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(84, 5, 1, '2025-11-02 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(85, 6, 2, '2025-11-02 12:30:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(86, 6, 2, '2025-11-02 21:20:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(87, 8, 4, '2025-11-02 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(88, 8, 4, '2025-11-02 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(89, 5, 1, '2025-11-03 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(90, 5, 1, '2025-11-03 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(91, 8, 4, '2025-11-03 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(92, 8, 4, '2025-11-03 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(93, 5, 1, '2025-11-04 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(94, 5, 1, '2025-11-04 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(95, 6, 2, '2025-11-04 12:05:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(96, 6, 2, '2025-11-04 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(97, 7, 1, '2025-11-04 12:35:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(98, 7, 1, '2025-11-04 21:30:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(99, 5, 1, '2025-11-06 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(100, 8, 4, '2025-11-06 12:05:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(101, 9, 5, '2025-11-06 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(102, 10, 6, '2025-11-06 11:30:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(103, 12, 1, '2025-11-06 12:45:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(104, 11, 1, '2025-11-06 13:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(105, 11, 5, '2025-11-06 13:02:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(106, 1, 1, '2025-10-15 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(107, 1, 1, '2025-10-15 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(108, 2, 1, '2025-10-15 12:15:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(109, 2, 1, '2025-10-15 21:15:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(110, 3, 2, '2025-10-15 12:30:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(111, 3, 2, '2025-10-15 21:30:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(112, 5, 1, '2025-10-16 12:00:00', 'entrada', 0, NULL, '2025-11-06 20:50:01'),
(113, 5, 1, '2025-10-16 21:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01'),
(114, 6, 2, '2025-10-16 13:00:00', 'entrada', 1, NULL, '2025-11-06 20:50:01'),
(115, 6, 2, '2025-10-16 22:00:00', 'salida', 0, NULL, '2025-11-06 20:50:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_api`
--

CREATE TABLE `auditoria_api` (
  `id` int(11) NOT NULL,
  `dispositivo_id` int(11) DEFAULT NULL,
  `uid_tarjeta` varchar(50) DEFAULT NULL,
  `ip_origen` varchar(15) DEFAULT NULL,
  `timestamp_request` timestamp NOT NULL DEFAULT current_timestamp(),
  `respuesta` enum('exito','error','tarjeta_no_encontrada','dispositivo_invalido') DEFAULT NULL,
  `mensaje` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_api`
--

INSERT INTO `auditoria_api` (`id`, `dispositivo_id`, `uid_tarjeta`, `ip_origen`, `timestamp_request`, `respuesta`, `mensaje`) VALUES
(1, 1, 'A1B2C3D4', '192.168.1.105', '2025-11-06 19:50:01', 'exito', 'Marcaci├│n registrada correctamente'),
(2, 2, 'E5F6G7H8', '192.168.1.106', '2025-11-06 18:50:01', 'exito', 'Marcaci├│n registrada correctamente'),
(3, 1, 'INVALID123', '192.168.1.105', '2025-11-06 17:50:01', 'tarjeta_no_encontrada', 'UID de tarjeta no registrado'),
(4, 1, 'Q9R0S1T2', '192.168.1.105', '2025-11-06 15:50:01', 'error', 'Tarjeta bloqueada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `clave`, `valor`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'minutos_entre_marcaciones', '5', 'Minutos minimos entre marcaciones para evitar duplicados', '2025-11-02 16:17:40', '2025-11-02 16:17:40'),
(2, 'tolerancia_tardanza', '15', 'Minutos de tolerancia antes de marcar tardanza', '2025-11-02 16:17:40', '2025-11-02 16:17:40'),
(3, 'horario_corte_dia', '04:00:00', 'Hora que marca el cambio de dia laboral', '2025-11-02 16:17:40', '2025-11-02 16:17:40'),
(4, 'hora_inicio_jornada', '08:00:00', 'Hora estandar de inicio de jornada laboral', '2025-11-06 20:50:01', '2025-11-06 20:50:01'),
(5, 'hora_fin_jornada', '17:00:00', 'Hora estandar de fin de jornada laboral', '2025-11-06 20:50:01', '2025-11-06 20:50:01'),
(6, 'dias_laborales', 'L,M,Mi,J,V', 'Dias laborales de la semana', '2025-11-06 20:50:01', '2025-11-06 20:50:01'),
(7, 'timeout_ping_dispositivo', '15', 'Minutos de timeout para considerar dispositivo offline', '2025-11-06 20:50:01', '2025-11-06 20:50:01'),
(8, 'notificaciones_browser', '1', 'Habilitar notificaciones del navegador', '2025-11-06 20:50:01', '2025-11-06 20:50:01'),
(9, 'intervalo_actualizacion_dashboard', '30', 'Segundos entre actualizaciones automaticas del dashboard', '2025-11-06 20:50:01', '2025-11-06 20:50:01'),
(10, 'backup_automatico', '1', 'Habilitar respaldo automatico de datos', '2025-11-06 22:17:14', '2025-11-06 22:17:14'),
(11, 'exportar_logs', '30', 'Dias de retencion de logs antes de archivar', '2025-11-06 22:17:14', '2025-11-06 22:17:14'),
(12, 'max_dispositivos_activos', '50', 'Numero maximo de dispositivos ESP32 activos', '2025-11-06 22:17:14', '2025-11-06 22:17:14'),
(13, 'alertas_tardanzas_consecutivas', '3', 'Numero de tardanzas consecutivas antes de alerta', '2025-11-06 22:17:14', '2025-11-06 22:17:14'),
(14, 'tiempo_sesion_minutos', '480', 'Tiempo maximo de sesion en minutos (8 horas)', '2025-11-06 22:17:14', '2025-11-06 22:17:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `token_dispositivo` varchar(255) NOT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  `ultimo_ping` timestamp NULL DEFAULT NULL,
  `estado` enum('activo','inactivo','mantenimiento') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id`, `nombre`, `ubicacion`, `token_dispositivo`, `ip_address`, `ultimo_ping`, `estado`, `created_at`) VALUES
(1, 'Entrada Principal', 'Recepcion - Piso 1', '38a3267b7436fa3f2c635799fa6e80e56a30be809636b74c80c122f0c5253950', NULL, NULL, 'activo', '2025-11-02 01:48:33'),
(2, 'Lector Principal', 'Entrada Principal', 'ESP32_TEST_001', NULL, NULL, 'activo', '2025-11-02 17:07:52'),
(3, 'Lector Secundario', 'Entrada Trasera', 'ESP32_TEST_002', NULL, NULL, 'activo', '2025-11-02 17:07:52'),
(4, 'Lector Cafeteria', 'Area de Descanso - Piso 2', 'cf47d8e9a1b2c3d4e5f6789012345678901234567890abcdef123456789abcdef', '192.168.1.105', '2025-11-06 20:44:24', 'activo', '2025-11-06 20:46:24'),
(5, 'Lector Oficinas', 'Pasillo Principal - Piso 3', 'ab12cd34ef56789012345678901234567890abcdef123456789012345678901234', '192.168.1.106', '2025-11-06 19:46:24', 'activo', '2025-11-06 20:46:24'),
(6, 'Lector Parqueadero', 'Entrada Parqueadero', '9876543210abcdef123456789012345678901234567890abcdef1234567890abcd', NULL, NULL, 'inactivo', '2025-11-06 20:46:24'),
(7, 'Lector Sala Juntas', 'Sala de Reuniones A', 'fed123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef', '192.168.1.107', '2025-11-06 20:26:24', 'activo', '2025-11-06 20:46:24'),
(8, 'Lector Desarrollo', 'Area de Desarrollo - Piso 4', '147258369abcdef0123456789abcdef0123456789abcdef0123456789abcdef012', '192.168.1.108', '2025-11-06 20:16:24', 'activo', '2025-11-06 20:46:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_especiales`
--

CREATE TABLE `horarios_especiales` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `horario_entrada` time NOT NULL,
  `horario_salida` time NOT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `horarios_especiales`
--

INSERT INTO `horarios_especiales` (`id`, `usuario_id`, `fecha_inicio`, `fecha_fin`, `horario_entrada`, `horario_salida`, `motivo`, `activo`, `created_at`) VALUES
(1, 10, '2025-11-06', '2025-12-06', '07:00:00', '16:00:00', 'Horario especial por proyecto', 1, '2025-11-06 21:44:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `justificaciones_ausencias`
--

CREATE TABLE `justificaciones_ausencias` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_ausencia` date NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `documento_adjunto` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `aprobada_por` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `justificaciones_ausencias`
--

INSERT INTO `justificaciones_ausencias` (`id`, `usuario_id`, `fecha_ausencia`, `motivo`, `descripcion`, `documento_adjunto`, `estado`, `aprobada_por`, `fecha_solicitud`, `fecha_respuesta`) VALUES
(1, 6, '2025-11-03', 'Cita médica', 'Consulta médica especializada', NULL, 'aprobada', NULL, '2025-11-06 21:08:34', NULL),
(2, 7, '2025-11-05', 'Asunto personal', 'Tramite urgente', NULL, 'pendiente', NULL, '2025-11-06 21:08:34', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(50) NOT NULL,
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `logs_sistema`
--

INSERT INTO `logs_sistema` (`id`, `usuario_id`, `accion`, `tabla_afectada`, `id_registro`, `descripcion`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', 'usuarios', 1, 'Inicio de session exitoso', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-11-06 20:50:01'),
(2, 1, 'CREATE', 'dispositivos', 4, 'creacion de nuevo dispositivo', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-11-06 20:50:01'),
(3, 2, 'EXPORT', 'asistencias', NULL, 'exploracion de reporte de asistencias', '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-11-06 20:50:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('info','warning','error','success') DEFAULT 'info',
  `leida` tinyint(1) DEFAULT 0,
  `url_accion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `titulo`, `mensaje`, `tipo`, `leida`, `url_accion`, `created_at`) VALUES
(1, NULL, 'Bienvenida al Sistema', 'Bienvenido al sistema de control de asistencia con RFID', 'info', 0, NULL, '2025-11-06 21:44:39'),
(2, 2, 'Reporte Mensual', 'El reporte mensual esta listo para revision', 'success', 0, NULL, '2025-11-06 21:44:39');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `reporte_asistencia_diaria`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `reporte_asistencia_diaria` (
`fecha` date
,`numero_empleado` varchar(20)
,`empleado` varchar(201)
,`puesto` varchar(100)
,`primera_entrada` time
,`ultima_salida` time
,`total_entradas` bigint(21)
,`total_salidas` bigint(21)
,`tardanzas` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuario`
--

CREATE TABLE `sesiones_usuario` (
  `id` varchar(128) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ultimo_acceso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjetas_rfid`
--

CREATE TABLE `tarjetas_rfid` (
  `id` int(11) NOT NULL,
  `uid_tarjeta` varchar(50) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('activa','inactiva') DEFAULT 'activa',
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarjetas_rfid`
--

INSERT INTO `tarjetas_rfid` (`id`, `uid_tarjeta`, `usuario_id`, `estado`, `fecha_asignacion`) VALUES
(1, 'A1B2C3D4', 1, 'activa', '2025-11-02 16:08:00'),
(2, 'E5F6G7H8', 2, 'activa', '2025-11-02 16:08:00'),
(3, 'I9J0K1L2', 3, 'activa', '2025-11-02 16:08:00'),
(4, 'M3N4O5P6', NULL, 'activa', '2025-11-02 16:08:00'),
(21, 'Q7R8S9T0', 5, 'activa', '2025-01-15 14:00:00'),
(22, 'U1V2W3X4', 6, 'activa', '2025-02-01 13:30:00'),
(23, 'Y5Z6A7B8', 7, 'activa', '2025-02-15 15:00:00'),
(24, 'C9D0E1F2', 8, 'activa', '2025-03-01 12:45:00'),
(25, 'G3H4I5J6', 9, 'activa', '2025-03-15 18:30:00'),
(26, 'K7L8M9N0', 10, 'activa', '2025-04-01 20:00:00'),
(27, 'O1P2Q3R4', 11, 'activa', '2025-04-15 17:15:00'),
(28, 'S5T6U7V8', 12, 'activa', '2024-12-01 16:00:00'),
(29, 'W9X0Y1Z2', 13, 'activa', '2024-11-15 19:45:00'),
(30, 'A3B4C5D6', 14, 'activa', '2024-10-01 21:20:00'),
(31, 'E7F8G9H0', NULL, 'activa', '2025-11-01 04:00:00'),
(32, 'I1J2K3L4', NULL, 'activa', '2025-11-02 04:00:00'),
(33, 'M5N6O7P8', NULL, 'activa', '2025-11-03 04:00:00'),
(34, 'Q9R0S1T2', NULL, 'inactiva', '2025-11-04 04:00:00'),
(35, 'INACTIVE1', 15, 'inactiva', '2024-01-01 04:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `numero_empleado` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `puesto` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `horario_entrada` time DEFAULT '08:00:00',
  `horario_salida` time DEFAULT '17:00:00',
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','rrhh','empleado') DEFAULT 'empleado',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `numero_empleado`, `nombres`, `apellidos`, `email`, `telefono`, `puesto`, `fecha_ingreso`, `horario_entrada`, `horario_salida`, `password_hash`, `rol`, `activo`, `created_at`, `ultimo_login`) VALUES
(1, 'ADM001', 'Administrador', 'del Sistema', 'admin@empresa.com', NULL, 'Administrador', '2025-11-01', '08:00:00', '17:00:00', '$2y$10$4tw1iNEByr/MJufYn40zxewaGojYqzSTi9ZaBP9PZod2vxuFQuoma', 'admin', 1, '2025-11-02 01:47:13', '2025-11-06 23:07:34'),
(2, 'RH001', 'Maria', 'Garcia', 'rrhh@empresa.com', NULL, 'Jefe de RRHH', '2025-11-01', '08:00:00', '17:00:00', '$2y$10$IVzEhZp9Pu4pQceTU9ujkOl0lcIgKiWVkK3QZB02c2ruiEVg/aIpK', 'rrhh', 1, '2025-11-02 01:47:43', NULL),
(3, 'EMP001', 'Juan', 'Perez', 'juan@empresa.com', NULL, 'Empleado', '2025-11-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-02 01:48:19', NULL),
(5, 'EMP002', 'Ana', 'Matinez', 'ana.martinez@empresa.com', '555-0102', 'Analista de Sistemas', '2025-01-15', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(6, 'EMP003', 'Carlos', 'Lopez', 'carlos.lopez@empresa.com', '555-0103', 'Desarrollador Senior', '2025-02-01', '09:00:00', '18:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(7, 'EMP004', 'Laura', 'Rodriguez', 'laura.rodriguez@empresa.com', '555-0104', 'Diseñadora UX', '2025-02-15', '08:30:00', '17:30:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(8, 'EMP005', 'Miguel', 'Hernandez', 'miguel.hernandez@empresa.com', '555-0105', 'QA Tester', '2025-03-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(9, 'EMP006', 'Sofia', 'Gonzalez', 'sofia.gonzalez@empresa.com', '555-0106', 'Project Manager', '2025-03-15', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(10, 'EMP007', 'Roberto', 'Jimenes', 'roberto.jimenez@empresa.com', '555-0107', 'DevOps Engineer', '2025-04-01', '07:30:00', '16:30:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(11, 'EMP008', 'Patricia', 'Morales', 'patricia.morales@empresa.com', '555-0108', 'Business Analyst', '2025-04-15', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(12, 'SUP001', 'Diego', 'Vargas', 'diego.vargas@empresa.com', '555-0201', 'Supervisor de Desarrollo', '2024-12-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(13, 'SUP002', 'Carmen', 'Torres', 'carmen.torres@empresa.com', '555-0202', 'Coordinadora de QA', '2024-11-15', '08:30:00', '17:30:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-06 20:46:24', NULL),
(14, 'RH002', 'Elena', 'Castillo', 'elena.castillo@empresa.com', '555-0301', 'Asistente de RRHH', '2024-10-01', '08:00:00', '17:00:00', '$2y$10$IVzEhZp9Pu4pQceTU9ujkOl0lcIgKiWVkK3QZB02c2ruiEVg/aIpK', 'rrhh', 1, '2025-11-06 20:46:24', NULL),
(15, 'EMP999', 'Usuario', 'Inactivo', 'inactivo@empresa.com', '555-9999', 'Empleado Inactivo', '2024-01-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 0, '2025-11-06 20:46:24', NULL);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_asistencias`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_asistencias` (
`id` int(11)
,`fecha_hora` timestamp
,`tipo` enum('entrada','salida')
,`es_tardanza` tinyint(1)
,`empleado` varchar(201)
,`numero_empleado` varchar(20)
,`dispositivo` varchar(100)
,`fecha` date
,`hora` time
);

-- --------------------------------------------------------

--
-- Estructura para la vista `reporte_asistencia_diaria`
--
DROP TABLE IF EXISTS `reporte_asistencia_diaria`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reporte_asistencia_diaria`  AS SELECT cast(`a`.`fecha_hora` as date) AS `fecha`, `u`.`numero_empleado` AS `numero_empleado`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `empleado`, `u`.`puesto` AS `puesto`, min(case when `a`.`tipo` = 'entrada' then cast(`a`.`fecha_hora` as time) end) AS `primera_entrada`, max(case when `a`.`tipo` = 'salida' then cast(`a`.`fecha_hora` as time) end) AS `ultima_salida`, count(case when `a`.`tipo` = 'entrada' then 1 end) AS `total_entradas`, count(case when `a`.`tipo` = 'salida' then 1 end) AS `total_salidas`, sum(case when `a`.`es_tardanza` = 1 then 1 else 0 end) AS `tardanzas` FROM (`asistencias` `a` join `usuarios` `u` on(`a`.`usuario_id` = `u`.`id`)) GROUP BY cast(`a`.`fecha_hora` as date), `u`.`id` ORDER BY cast(`a`.`fecha_hora` as date) DESC, concat(`u`.`nombres`,' ',`u`.`apellidos`) ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_asistencias`
--
DROP TABLE IF EXISTS `vista_asistencias`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_asistencias`  AS SELECT `a`.`id` AS `id`, `a`.`fecha_hora` AS `fecha_hora`, `a`.`tipo` AS `tipo`, `a`.`es_tardanza` AS `es_tardanza`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `empleado`, `u`.`numero_empleado` AS `numero_empleado`, `d`.`nombre` AS `dispositivo`, cast(`a`.`fecha_hora` as date) AS `fecha`, cast(`a`.`fecha_hora` as time) AS `hora` FROM ((`asistencias` `a` join `usuarios` `u` on(`a`.`usuario_id` = `u`.`id`)) join `dispositivos` `d` on(`a`.`dispositivo_id` = `d`.`id`)) ORDER BY `a`.`fecha_hora` DESC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispositivo_id` (`dispositivo_id`),
  ADD KEY `idx_usuario_fecha` (`usuario_id`,`fecha_hora`),
  ADD KEY `idx_fecha_hora` (`fecha_hora`);

--
-- Indices de la tabla `auditoria_api`
--
ALTER TABLE `auditoria_api`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dispositivo` (`dispositivo_id`),
  ADD KEY `idx_timestamp` (`timestamp_request`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_dispositivo` (`token_dispositivo`),
  ADD KEY `idx_token_dispositivo` (`token_dispositivo`);

--
-- Indices de la tabla `horarios_especiales`
--
ALTER TABLE `horarios_especiales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_periodo` (`usuario_id`,`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `justificaciones_ausencias`
--
ALTER TABLE `justificaciones_ausencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aprobada_por` (`aprobada_por`),
  ADD KEY `idx_usuario_fecha` (`usuario_id`,`fecha_ausencia`);

--
-- Indices de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leida` (`usuario_id`,`leida`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_ultimo_acceso` (`ultimo_acceso`);

--
-- Indices de la tabla `tarjetas_rfid`
--
ALTER TABLE `tarjetas_rfid`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid_tarjeta` (`uid_tarjeta`),
  ADD KEY `idx_uid_tarjeta` (`uid_tarjeta`),
  ADD KEY `idx_usuario_tarjeta` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_empleado` (`numero_empleado`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_numero_empleado` (`numero_empleado`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de la tabla `auditoria_api`
--
ALTER TABLE `auditoria_api`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `horarios_especiales`
--
ALTER TABLE `horarios_especiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `justificaciones_ausencias`
--
ALTER TABLE `justificaciones_ausencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tarjetas_rfid`
--
ALTER TABLE `tarjetas_rfid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`);

--
-- Filtros para la tabla `horarios_especiales`
--
ALTER TABLE `horarios_especiales`
  ADD CONSTRAINT `horarios_especiales_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `justificaciones_ausencias`
--
ALTER TABLE `justificaciones_ausencias`
  ADD CONSTRAINT `justificaciones_ausencias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `justificaciones_ausencias_ibfk_2` FOREIGN KEY (`aprobada_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  ADD CONSTRAINT `sesiones_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tarjetas_rfid`
--
ALTER TABLE `tarjetas_rfid`
  ADD CONSTRAINT `tarjetas_rfid_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
