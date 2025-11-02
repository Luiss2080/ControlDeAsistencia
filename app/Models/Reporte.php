<?php

namespace App\Models;

use PDO;
use DateTime;

class Reporte
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener resumen de asistencia por usuario y rango de fechas
     */
    public function obtenerResumenAsistencia($usuarioId = null, $fechaInicio = null, $fechaFin = null)
    {
        $whereConditions = ['u.activo = 1'];
        $params = [];
        
        if ($usuarioId) {
            $whereConditions[] = 'u.id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        
        if ($fechaInicio) {
            $whereConditions[] = 'DATE(ra.fecha_hora) >= :fecha_inicio';
            $params['fecha_inicio'] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $whereConditions[] = 'DATE(ra.fecha_hora) <= :fecha_fin';
            $params['fecha_fin'] = $fechaFin;
        }
        
        $sql = "
            SELECT 
                u.id,
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
                d.nombre as departamento,
                COUNT(DISTINCT DATE(ra.fecha_hora)) as dias_trabajados,
                COUNT(CASE WHEN ra.tipo = 'entrada' THEN 1 END) as total_entradas,
                COUNT(CASE WHEN ra.tipo = 'salida' THEN 1 END) as total_salidas,
                COUNT(CASE WHEN ra.fuera_horario = 1 THEN 1 END) as marcaciones_fuera_horario,
                AVG(CASE WHEN ra.tipo = 'entrada' THEN TIME_TO_SEC(TIME(ra.fecha_hora)) END) as promedio_entrada,
                AVG(CASE WHEN ra.tipo = 'salida' THEN TIME_TO_SEC(TIME(ra.fecha_hora)) END) as promedio_salida
            FROM usuarios u
            LEFT JOIN registros_asistencia ra ON u.id = ra.usuario_id AND ra.valido = 1
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE " . implode(' AND ', $whereConditions) . "
            GROUP BY u.id
            ORDER BY u.numero_empleado
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir promedios de segundos a formato HH:MM
        foreach ($resultados as &$resultado) {
            if ($resultado['promedio_entrada']) {
                $resultado['promedio_entrada'] = gmdate('H:i', $resultado['promedio_entrada']);
            }
            if ($resultado['promedio_salida']) {
                $resultado['promedio_salida'] = gmdate('H:i', $resultado['promedio_salida']);
            }
        }
        
        return $resultados;
    }
    
    /**
     * Obtener detalle diario de asistencia
     */
    public function obtenerDetalleAsistenciaDiaria($fecha, $departamentoId = null)
    {
        $whereConditions = ['DATE(ra.fecha_hora) = :fecha', 'ra.valido = 1'];
        $params = ['fecha' => $fecha];
        
        if ($departamentoId) {
            $whereConditions[] = 'u.departamento_id = :departamento_id';
            $params['departamento_id'] = $departamentoId;
        }
        
        $sql = "
            SELECT 
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
                d.nombre as departamento,
                GROUP_CONCAT(
                    CONCAT(TIME(ra.fecha_hora), ' (', ra.tipo, ')')
                    ORDER BY ra.fecha_hora SEPARATOR ', '
                ) as marcaciones,
                MIN(CASE WHEN ra.tipo = 'entrada' THEN TIME(ra.fecha_hora) END) as primera_entrada,
                MAX(CASE WHEN ra.tipo = 'salida' THEN TIME(ra.fecha_hora) END) as ultima_salida,
                ht.hora_entrada as hora_entrada_programada,
                ht.hora_salida as hora_salida_programada,
                COUNT(CASE WHEN ra.tipo = 'entrada' THEN 1 END) as total_entradas,
                COUNT(CASE WHEN ra.tipo = 'salida' THEN 1 END) as total_salidas
            FROM usuarios u
            LEFT JOIN registros_asistencia ra ON u.id = ra.usuario_id AND " . implode(' AND ', $whereConditions) . "
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN horarios_trabajo ht ON u.horario_id = ht.id
            WHERE u.activo = 1
            GROUP BY u.id
            HAVING total_entradas > 0 OR total_salidas > 0
            ORDER BY u.numero_empleado
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de dispositivos
     */
    public function obtenerEstadisticasDispositivos($fechaInicio = null, $fechaFin = null)
    {
        $whereConditions = [];
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $whereConditions[] = 'DATE(ra.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin';
            $params['fecha_inicio'] = $fechaInicio;
            $params['fecha_fin'] = $fechaFin;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "
            SELECT 
                d.id,
                d.nombre,
                d.ubicacion,
                d.estado,
                d.ultima_conexion,
                COALESCE(COUNT(ra.id), 0) as total_marcaciones,
                COALESCE(COUNT(CASE WHEN DATE(ra.fecha_hora) = CURDATE() THEN 1 END), 0) as marcaciones_hoy,
                COALESCE(COUNT(CASE WHEN ra.tipo = 'entrada' THEN 1 END), 0) as entradas,
                COALESCE(COUNT(CASE WHEN ra.tipo = 'salida' THEN 1 END), 0) as salidas,
                MAX(ra.fecha_hora) as ultima_marcacion
            FROM dispositivos d
            LEFT JOIN registros_asistencia ra ON d.id = ra.dispositivo_id AND ra.valido = 1 
            $whereClause
            WHERE d.activo = 1
            GROUP BY d.id
            ORDER BY d.nombre
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener empleados con llegadas tardías
     */
    public function obtenerLlegadasTardias($fechaInicio, $fechaFin, $departamentoId = null)
    {
        $whereConditions = [
            'DATE(ra.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin',
            'ra.tipo = "entrada"',
            'ra.valido = 1',
            'TIME(ra.fecha_hora) > ht.hora_entrada'
        ];
        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
        
        if ($departamentoId) {
            $whereConditions[] = 'u.departamento_id = :departamento_id';
            $params['departamento_id'] = $departamentoId;
        }
        
        $sql = "
            SELECT 
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
                d.nombre as departamento,
                DATE(ra.fecha_hora) as fecha,
                TIME(ra.fecha_hora) as hora_entrada,
                ht.hora_entrada as hora_programada,
                TIMEDIFF(TIME(ra.fecha_hora), ht.hora_entrada) as minutos_retraso
            FROM registros_asistencia ra
            INNER JOIN usuarios u ON ra.usuario_id = u.id
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN horarios_trabajo ht ON u.horario_id = ht.id
            WHERE " . implode(' AND ', $whereConditions) . "
            ORDER BY ra.fecha_hora DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener empleados ausentes
     */
    public function obtenerEmpleadosAusentes($fecha, $departamentoId = null)
    {
        $whereConditions = ['u.activo = 1'];
        $params = ['fecha' => $fecha];
        
        if ($departamentoId) {
            $whereConditions[] = 'u.departamento_id = :departamento_id';
            $params['departamento_id'] = $departamentoId;
        }
        
        $sql = "
            SELECT 
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
                d.nombre as departamento,
                u.telefono,
                u.email
            FROM usuarios u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN registros_asistencia ra ON u.id = ra.usuario_id 
                AND DATE(ra.fecha_hora) = :fecha 
                AND ra.valido = 1
            WHERE " . implode(' AND ', $whereConditions) . "
            AND ra.id IS NULL
            ORDER BY u.numero_empleado
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener horas trabajadas por empleado
     */
    public function obtenerHorasTrabajadas($usuarioId = null, $fechaInicio = null, $fechaFin = null)
    {
        $whereConditions = ['u.activo = 1'];
        $params = [];
        
        if ($usuarioId) {
            $whereConditions[] = 'u.id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        
        if ($fechaInicio) {
            $whereConditions[] = 'DATE(ra.fecha_hora) >= :fecha_inicio';
            $params['fecha_inicio'] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $whereConditions[] = 'DATE(ra.fecha_hora) <= :fecha_fin';
            $params['fecha_fin'] = $fechaFin;
        }
        
        $sql = "
            SELECT 
                u.id,
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
                d.nombre as departamento,
                DATE(ra.fecha_hora) as fecha,
                MIN(CASE WHEN ra.tipo = 'entrada' THEN ra.fecha_hora END) as primera_entrada,
                MAX(CASE WHEN ra.tipo = 'salida' THEN ra.fecha_hora END) as ultima_salida,
                CASE 
                    WHEN MIN(CASE WHEN ra.tipo = 'entrada' THEN ra.fecha_hora END) IS NOT NULL 
                    AND MAX(CASE WHEN ra.tipo = 'salida' THEN ra.fecha_hora END) IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, 
                        MIN(CASE WHEN ra.tipo = 'entrada' THEN ra.fecha_hora END),
                        MAX(CASE WHEN ra.tipo = 'salida' THEN ra.fecha_hora END)
                    )
                    ELSE 0
                END as minutos_trabajados
            FROM usuarios u
            LEFT JOIN registros_asistencia ra ON u.id = ra.usuario_id AND ra.valido = 1
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE " . implode(' AND ', $whereConditions) . "
            GROUP BY u.id, DATE(ra.fecha_hora)
            HAVING primera_entrada IS NOT NULL
            ORDER BY u.numero_empleado, fecha
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir minutos a formato HH:MM
        foreach ($resultados as &$resultado) {
            $horas = floor($resultado['minutos_trabajados'] / 60);
            $minutos = $resultado['minutos_trabajados'] % 60;
            $resultado['horas_trabajadas'] = sprintf('%02d:%02d', $horas, $minutos);
        }
        
        return $resultados;
    }
    
    /**
     * Generar reporte en formato exportable
     */
    public function generarReporteExportable($tipo, $parametros, $usuarioId)
    {
        // Registrar el reporte solicitado
        $stmt = $this->db->prepare("
            INSERT INTO reportes_generados (usuario_id, tipo_reporte, parametros, fecha_inicio, fecha_fin)
            VALUES (:usuario_id, :tipo_reporte, :parametros, :fecha_inicio, :fecha_fin)
        ");
        
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'tipo_reporte' => $tipo,
            'parametros' => json_encode($parametros),
            'fecha_inicio' => $parametros['fecha_inicio'] ?? null,
            'fecha_fin' => $parametros['fecha_fin'] ?? null
        ]);
        
        $reporteId = $this->db->lastInsertId();
        
        // Generar el reporte según el tipo
        switch ($tipo) {
            case 'asistencia_resumen':
                $datos = $this->obtenerResumenAsistencia(
                    $parametros['usuario_id'] ?? null,
                    $parametros['fecha_inicio'] ?? null,
                    $parametros['fecha_fin'] ?? null
                );
                break;
                
            case 'asistencia_detalle':
                $datos = $this->obtenerDetalleAsistenciaDiaria(
                    $parametros['fecha'],
                    $parametros['departamento_id'] ?? null
                );
                break;
                
            case 'llegadas_tardias':
                $datos = $this->obtenerLlegadasTardias(
                    $parametros['fecha_inicio'],
                    $parametros['fecha_fin'],
                    $parametros['departamento_id'] ?? null
                );
                break;
                
            case 'empleados_ausentes':
                $datos = $this->obtenerEmpleadosAusentes(
                    $parametros['fecha'],
                    $parametros['departamento_id'] ?? null
                );
                break;
                
            case 'horas_trabajadas':
                $datos = $this->obtenerHorasTrabajadas(
                    $parametros['usuario_id'] ?? null,
                    $parametros['fecha_inicio'] ?? null,
                    $parametros['fecha_fin'] ?? null
                );
                break;
                
            default:
                throw new \Exception("Tipo de reporte no válido: $tipo");
        }
        
        // Marcar el reporte como completado
        $stmt = $this->db->prepare("
            UPDATE reportes_generados 
            SET estado = 'completado' 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $reporteId]);
        
        return [
            'reporte_id' => $reporteId,
            'datos' => $datos,
            'total_registros' => count($datos)
        ];
    }
    
    /**
     * Obtener lista de departamentos para filtros
     */
    public function obtenerDepartamentos()
    {
        $stmt = $this->db->prepare("
            SELECT id, nombre 
            FROM departamentos 
            WHERE activo = 1 
            ORDER BY nombre
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}