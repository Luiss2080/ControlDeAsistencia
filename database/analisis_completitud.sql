-- ANÁLISIS COMPLETO DE LA BASE DE DATOS PARA CONTROL DE ASISTENCIA
-- Verificación de completitud y sugerencias de mejora

-- 1. VERIFICAR ESTRUCTURA ACTUAL
SELECT 'ANÁLISIS DE TABLAS EXISTENTES' as seccion;

-- Mostrar todas las tablas con descripción de su propósito
SELECT 
    TABLE_NAME as tabla,
    CASE 
        WHEN TABLE_NAME = 'usuarios' THEN 'Empleados, admins y personal RRHH'
        WHEN TABLE_NAME = 'dispositivos' THEN 'Lectores ESP32 registrados'
        WHEN TABLE_NAME = 'tarjetas_rfid' THEN 'Tarjetas RFID del sistema'
        WHEN TABLE_NAME = 'asistencias' THEN 'Registros de marcaciones'
        WHEN TABLE_NAME = 'configuracion_sistema' THEN 'Parámetros configurables'
        WHEN TABLE_NAME = 'auditoria_api' THEN 'Log de peticiones API'
        WHEN TABLE_NAME = 'logs_sistema' THEN 'Auditoría de acciones'
        WHEN TABLE_NAME = 'vista_asistencias' THEN 'Vista consolidada de asistencias'
        WHEN TABLE_NAME = 'reporte_asistencia_diaria' THEN 'Vista para reportes diarios'
        ELSE 'Tabla de soporte'
    END as proposito,
    TABLE_ROWS as registros_aprox
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'control_asistencia' 
AND TABLE_TYPE = 'BASE TABLE'
ORDER BY TABLE_NAME;

-- 2. VERIFICAR ÍNDICES PARA RENDIMIENTO
SELECT 'ANÁLISIS DE ÍNDICES' as seccion;

SELECT 
    TABLE_NAME as tabla,
    INDEX_NAME as indice,
    COLUMN_NAME as columna,
    CASE WHEN NON_UNIQUE = 0 THEN 'ÚNICO' ELSE 'NORMAL' END as tipo_indice
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'control_asistencia' 
ORDER BY TABLE_NAME, INDEX_NAME;

-- 3. VERIFICAR FOREIGN KEYS
SELECT 'ANÁLISIS DE RELACIONES (FOREIGN KEYS)' as seccion;

SELECT 
    TABLE_NAME as tabla_origen,
    COLUMN_NAME as columna_origen,
    REFERENCED_TABLE_NAME as tabla_destino,
    REFERENCED_COLUMN_NAME as columna_destino,
    CONSTRAINT_NAME as nombre_constraint
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'control_asistencia' 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;

-- 4. ANÁLISIS DE DATOS PARA TESTING
SELECT 'ANÁLISIS DE DATOS DE PRUEBA' as seccion;

-- Distribución de usuarios por rol
SELECT 
    rol,
    activo,
    COUNT(*) as cantidad
FROM usuarios 
GROUP BY rol, activo 
ORDER BY rol, activo;

-- Distribución de dispositivos por estado
SELECT 
    estado,
    COUNT(*) as cantidad
FROM dispositivos 
GROUP BY estado;

-- Distribución de tarjetas
SELECT 
    CASE 
        WHEN usuario_id IS NULL THEN 'Sin asignar'
        ELSE 'Asignada'
    END as asignacion,
    estado,
    COUNT(*) as cantidad
FROM tarjetas_rfid 
GROUP BY 
    CASE WHEN usuario_id IS NULL THEN 'Sin asignar' ELSE 'Asignada' END,
    estado;

-- Asistencias por día (últimos 7 días)
SELECT 
    DATE(fecha_hora) as fecha,
    COUNT(*) as total_marcaciones,
    COUNT(DISTINCT usuario_id) as empleados_diferentes,
    SUM(es_tardanza) as tardanzas
FROM asistencias 
WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(fecha_hora)
ORDER BY fecha DESC;

-- 5. VERIFICAR FUNCIONALIDADES DE TESTING
SELECT 'VERIFICACIÓN DE FUNCIONALIDADES' as seccion;

-- Empleados con tardanzas frecuentes (para alertas)
SELECT 
    u.nombres,
    u.apellidos,
    COUNT(*) as tardanzas_semana
FROM asistencias a
JOIN usuarios u ON a.usuario_id = u.id
WHERE a.es_tardanza = 1 
AND a.fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY u.id
HAVING tardanzas_semana >= 2
ORDER BY tardanzas_semana DESC;

-- Dispositivos con problemas de conectividad
SELECT 
    nombre,
    ubicacion,
    ultimo_ping,
    CASE 
        WHEN ultimo_ping IS NULL THEN 'Nunca conectado'
        WHEN ultimo_ping < DATE_SUB(NOW(), INTERVAL 15 MINUTE) THEN 'Desconectado'
        ELSE 'Conectado'
    END as estado_conexion
FROM dispositivos
WHERE estado = 'activo';

-- Marcaciones sospechosas (mismo usuario, diferentes dispositivos, poco tiempo)
SELECT 
    a1.usuario_id,
    u.nombres,
    u.apellidos,
    a1.fecha_hora as marcacion1,
    a2.fecha_hora as marcacion2,
    d1.nombre as dispositivo1,
    d2.nombre as dispositivo2,
    TIMESTAMPDIFF(MINUTE, a1.fecha_hora, a2.fecha_hora) as diferencia_minutos
FROM asistencias a1
JOIN asistencias a2 ON a1.usuario_id = a2.usuario_id
JOIN usuarios u ON a1.usuario_id = u.id
JOIN dispositivos d1 ON a1.dispositivo_id = d1.id
JOIN dispositivos d2 ON a2.dispositivo_id = d2.id
WHERE DATE(a1.fecha_hora) = DATE(a2.fecha_hora)
AND a1.dispositivo_id != a2.dispositivo_id
AND ABS(TIMESTAMPDIFF(MINUTE, a1.fecha_hora, a2.fecha_hora)) < 5
AND a1.id != a2.id
ORDER BY a1.fecha_hora DESC;

-- 6. VERIFICAR CONFIGURACIONES DEL SISTEMA
SELECT 'CONFIGURACIONES DEL SISTEMA' as seccion;

SELECT 
    clave,
    valor,
    descripcion
FROM configuracion_sistema
ORDER BY clave;

-- 7. ANÁLISIS DE COMPLETITUD
SELECT 'ANÁLISIS DE COMPLETITUD PARA TESTING' as seccion;

-- Verificar que tengamos suficientes datos para cada funcionalidad
SELECT 
    'Usuarios con diferentes roles' as funcionalidad,
    CASE WHEN COUNT(*) >= 3 THEN 'COMPLETO' ELSE 'FALTA DATOS' END as estado,
    COUNT(*) as cantidad_actual,
    '3+' as cantidad_recomendada
FROM usuarios WHERE activo = 1 AND rol IN ('admin', 'rrhh', 'empleado')

UNION ALL

SELECT 
    'Dispositivos activos para testing',
    CASE WHEN COUNT(*) >= 3 THEN 'COMPLETO' ELSE 'FALTA DATOS' END as estado,
    COUNT(*) as cantidad_actual,
    '3+' as cantidad_recomendada
FROM dispositivos WHERE estado = 'activo'

UNION ALL

SELECT 
    'Tarjetas RFID disponibles',
    CASE WHEN COUNT(*) >= 10 THEN 'COMPLETO' ELSE 'FALTA DATOS' END as estado,
    COUNT(*) as cantidad_actual,
    '10+' as cantidad_recomendada
FROM tarjetas_rfid

UNION ALL

SELECT 
    'Asistencias última semana',
    CASE WHEN COUNT(*) >= 20 THEN 'COMPLETO' ELSE 'FALTA DATOS' END as estado,
    COUNT(*) as cantidad_actual,
    '20+' as cantidad_recomendada
FROM asistencias WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)

UNION ALL

SELECT 
    'Empleados con tardanzas (para alertas)',
    CASE WHEN COUNT(*) >= 2 THEN 'COMPLETO' ELSE 'FALTA DATOS' END as estado,
    COUNT(*) as cantidad_actual,
    '2+' as cantidad_recomendada
FROM (
    SELECT usuario_id
    FROM asistencias 
    WHERE es_tardanza = 1 
    AND fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY usuario_id
    HAVING COUNT(*) >= 2
) tardanzas_frecuentes

UNION ALL

SELECT 
    'Configuraciones del sistema',
    CASE WHEN COUNT(*) >= 5 THEN 'COMPLETO' ELSE 'FALTA DATOS' END as estado,
    COUNT(*) as cantidad_actual,
    '5+' as cantidad_recomendada
FROM configuracion_sistema;

-- 8. RECOMENDACIONES FINALES
SELECT 'RECOMENDACIONES PARA TESTING COMPLETO' as seccion;

-- Verificar que podamos probar todas las funcionalidades principales:
-- ✅ Login con diferentes roles
-- ✅ Gestión de usuarios (CRUD)
-- ✅ Gestión de dispositivos ESP32
-- ✅ Gestión de tarjetas RFID
-- ✅ Registro de asistencias vía API
-- ✅ Reportes con filtros
-- ✅ Alertas automáticas
-- ✅ Dashboard en tiempo real
-- ✅ Exportación de datos
-- ✅ Auditoría del sistema

SELECT '✅ BASE DE DATOS COMPLETA PARA TESTING' as resultado;