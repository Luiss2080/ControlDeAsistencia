-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-11-2025 a las 16:57:57
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `usuario_id`, `dispositivo_id`, `fecha_hora`, `tipo`, `es_tardanza`, `created_at`) VALUES
(1, 1, 1, '2025-11-02 12:05:00', 'entrada', 0, '2025-11-02 16:10:19'),
(2, 2, 1, '2025-11-02 12:25:00', 'entrada', 0, '2025-11-02 16:10:19'),
(3, 3, 1, '2025-11-02 11:55:00', 'entrada', 0, '2025-11-02 16:10:19'),
(4, 1, 1, '2025-11-02 12:15:00', 'entrada', 1, '2025-11-02 17:12:30'),
(5, 3, 1, '2025-11-02 11:55:00', 'entrada', 0, '2025-11-02 17:12:30'),
(6, 1, 1, '2025-11-02 21:05:00', 'salida', 0, '2025-11-02 17:12:30'),
(7, 3, 2, '2025-11-01 12:30:00', 'entrada', 1, '2025-11-02 17:12:30');

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
(1, 'minutos_entre_marcaciones', '5', 'Minutos mÝnimos entre marcaciones para evitar duplicados', '2025-11-02 16:17:40', '2025-11-02 16:17:40'),
(2, 'tolerancia_tardanza', '15', 'Minutos de tolerancia antes de marcar tardanza', '2025-11-02 16:17:40', '2025-11-02 16:17:40'),
(3, 'horario_corte_dia', '04:00:00', 'Hora que marca el cambio de dÝa laboral', '2025-11-02 16:17:40', '2025-11-02 16:17:40');

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
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id`, `nombre`, `ubicacion`, `token_dispositivo`, `ip_address`, `ultimo_ping`, `estado`, `created_at`) VALUES
(1, 'Entrada Principal', 'Recepci¾n - Piso 1', '38a3267b7436fa3f2c635799fa6e80e56a30be809636b74c80c122f0c5253950', NULL, NULL, 'activo', '2025-11-02 01:48:33'),
(2, 'Lector Principal', 'Entrada Principal', 'ESP32_TEST_001', NULL, NULL, 'activo', '2025-11-02 17:07:52'),
(3, 'Lector Secundario', 'Entrada Trasera', 'ESP32_TEST_002', NULL, NULL, 'activo', '2025-11-02 17:07:52');

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
(4, 'M3N4O5P6', NULL, 'activa', '2025-11-02 16:08:00');

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
(1, 'ADM001', 'Administrador', 'del Sistema', 'admin@empresa.com', NULL, 'Administrador', '2025-11-01', '08:00:00', '17:00:00', '$2y$10$4tw1iNEByr/MJufYn40zxewaGojYqzSTi9ZaBP9PZod2vxuFQuoma', 'admin', 1, '2025-11-02 01:47:13', '2025-11-04 15:21:40'),
(2, 'RH001', 'MarÝa', 'GarcÝa', 'rrhh@empresa.com', NULL, 'Jefe de RRHH', '2025-11-01', '08:00:00', '17:00:00', '$2y$10$IVzEhZp9Pu4pQceTU9ujkOl0lcIgKiWVkK3QZB02c2ruiEVg/aIpK', 'rrhh', 1, '2025-11-02 01:47:43', NULL),
(3, 'EMP001', 'Juan', 'PÚrez', 'juan@empresa.com', NULL, 'Empleado', '2025-11-01', '08:00:00', '17:00:00', '$2y$10$y10dYg22VgmUmLl3gkLDnuLAIM2N9GXtUqzzqo1eBY4h5m/RF4JVu', 'empleado', 1, '2025-11-02 01:48:19', NULL);

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
-- Indices de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `auditoria_api`
--
ALTER TABLE `auditoria_api`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarjetas_rfid`
--
ALTER TABLE `tarjetas_rfid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- Filtros para la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tarjetas_rfid`
--
ALTER TABLE `tarjetas_rfid`
  ADD CONSTRAINT `tarjetas_rfid_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
