<?php
/**
 * Modelo para Tarjetas RFID
 * Sistema de Control de Asistencia
 */

namespace App\Models;

use App\Models\Database;

class TarjetaRFID {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener tarjeta por UID
     */
    public function obtenerPorUID($uid) {
        $sql = "SELECT tr.*, u.nombres, u.apellidos, u.numero_empleado, u.activo as usuario_activo,
                       d.nombre as departamento_nombre
                FROM tarjetas_rfid tr 
                LEFT JOIN usuarios u ON tr.usuario_id = u.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE tr.uid_tarjeta = ?";
        
        return $this->db->fetch($sql, [$uid]);
    }

    /**
     * Obtener tarjeta por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT tr.*, u.nombres, u.apellidos, u.numero_empleado, u.activo as usuario_activo,
                       d.nombre as departamento_nombre
                FROM tarjetas_rfid tr 
                LEFT JOIN usuarios u ON tr.usuario_id = u.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE tr.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Obtener tarjetas de un usuario
     */
    public function obtenerPorUsuario($usuarioId) {
        $sql = "SELECT * FROM tarjetas_rfid WHERE usuario_id = ? ORDER BY fecha_asignacion DESC";
        return $this->db->fetchAll($sql, [$usuarioId]);
    }

    /**
     * Crear nueva tarjeta
     */
    public function crear($datos) {
        // Verificar que el UID no exista
        if ($this->uidExiste($datos['uid_tarjeta'])) {
            throw new \Exception('El UID de la tarjeta ya existe');
        }
        
        return $this->db->insert('tarjetas_rfid', $datos);
    }

    /**
     * Actualizar tarjeta
     */
    public function actualizar($id, $datos) {
        return $this->db->update('tarjetas_rfid', $datos, 'id = ?', [$id]);
    }

    /**
     * Asignar tarjeta a usuario
     */
    public function asignarAUsuario($tarjetaId, $usuarioId) {
        try {
            $this->db->beginTransaction();
            
            // Desasignar tarjetas activas del usuario
            $this->db->update(
                'tarjetas_rfid',
                [
                    'usuario_id' => null,
                    'fecha_desasignacion' => date('Y-m-d H:i:s'),
                    'estado' => 'inactiva'
                ],
                'usuario_id = ? AND estado = ?',
                [$usuarioId, 'activa']
            );
            
            // Asignar nueva tarjeta
            $resultado = $this->db->update(
                'tarjetas_rfid',
                [
                    'usuario_id' => $usuarioId,
                    'fecha_asignacion' => date('Y-m-d H:i:s'),
                    'fecha_desasignacion' => null,
                    'estado' => 'activa'
                ],
                'id = ?',
                [$tarjetaId]
            );
            
            $this->db->commit();
            return $resultado;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Desasignar tarjeta de usuario
     */
    public function desasignarDeUsuario($tarjetaId, $motivo = '') {
        $datos = [
            'usuario_id' => null,
            'fecha_desasignacion' => date('Y-m-d H:i:s'),
            'estado' => 'inactiva',
            'observaciones' => $motivo
        ];
        
        return $this->db->update('tarjetas_rfid', $datos, 'id = ?', [$tarjetaId]);
    }

    /**
     * Cambiar estado de tarjeta
     */
    public function cambiarEstado($id, $estado, $observaciones = '') {
        $estadosValidos = ['activa', 'inactiva', 'perdida', 'dañada'];
        
        if (!in_array($estado, $estadosValidos)) {
            throw new \InvalidArgumentException('Estado no válido');
        }
        
        $datos = ['estado' => $estado];
        
        if ($observaciones) {
            $datos['observaciones'] = $observaciones;
        }
        
        // Si se desactiva, quitar asignación
        if ($estado !== 'activa') {
            $datos['fecha_desasignacion'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->update('tarjetas_rfid', $datos, 'id = ?', [$id]);
    }

    /**
     * Obtener todas las tarjetas con filtros
     */
    public function obtenerTodas($filtros = []) {
        $where = ["1 = 1"];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[] = "tr.estado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['asignada'])) {
            if ($filtros['asignada'] === 'si') {
                $where[] = "tr.usuario_id IS NOT NULL";
            } else {
                $where[] = "tr.usuario_id IS NULL";
            }
        }

        if (!empty($filtros['busqueda'])) {
            $where[] = "(tr.uid_tarjeta LIKE ? OR u.nombres LIKE ? OR u.apellidos LIKE ? OR u.numero_empleado LIKE ?)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params = array_merge($params, [$busqueda, $busqueda, $busqueda, $busqueda]);
        }

        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT tr.*, 
                       CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
                       u.numero_empleado,
                       u.activo as usuario_activo,
                       d.nombre as departamento_nombre
                FROM tarjetas_rfid tr 
                LEFT JOIN usuarios u ON tr.usuario_id = u.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE {$whereClause}
                ORDER BY tr.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtener tarjetas disponibles (sin asignar)
     */
    public function obtenerDisponibles() {
        $sql = "SELECT * FROM tarjetas_rfid 
                WHERE usuario_id IS NULL AND estado = 'activa'
                ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Verificar si UID existe
     */
    public function uidExiste($uid, $excluirId = null) {
        $sql = "SELECT id FROM tarjetas_rfid WHERE uid_tarjeta = ?";
        $params = [$uid];

        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }

        $resultado = $this->db->fetch($sql, $params);
        return !empty($resultado);
    }

    /**
     * Obtener estadísticas de tarjetas
     */
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_tarjetas,
                    COUNT(CASE WHEN estado = 'activa' THEN 1 END) as tarjetas_activas,
                    COUNT(CASE WHEN usuario_id IS NOT NULL AND estado = 'activa' THEN 1 END) as tarjetas_asignadas,
                    COUNT(CASE WHEN usuario_id IS NULL AND estado = 'activa' THEN 1 END) as tarjetas_disponibles,
                    COUNT(CASE WHEN estado = 'perdida' THEN 1 END) as tarjetas_perdidas,
                    COUNT(CASE WHEN estado = 'dañada' THEN 1 END) as tarjetas_danadas
                FROM tarjetas_rfid";
        
        return $this->db->fetch($sql);
    }

    /**
     * Obtener historial de asignaciones de una tarjeta
     */
    public function obtenerHistorialAsignaciones($tarjetaId) {
        $sql = "SELECT ls.*
                FROM logs_sistema ls
                WHERE ls.tabla_afectada = 'tarjetas_rfid' 
                AND ls.id_registro = ?
                AND ls.accion IN ('INSERT', 'UPDATE')
                ORDER BY ls.created_at DESC";
        
        return $this->db->fetchAll($sql, [$tarjetaId]);
    }

    /**
     * Buscar tarjetas para autocompletado
     */
    public function buscar($termino, $limite = 10) {
        $sql = "SELECT tr.id, tr.uid_tarjeta, tr.estado,
                       CASE 
                           WHEN u.id IS NOT NULL THEN CONCAT(u.nombres, ' ', u.apellidos, ' (', u.numero_empleado, ')')
                           ELSE 'Sin asignar'
                       END as descripcion
                FROM tarjetas_rfid tr 
                LEFT JOIN usuarios u ON tr.usuario_id = u.id 
                WHERE tr.uid_tarjeta LIKE ? OR u.nombres LIKE ? OR u.apellidos LIKE ?
                ORDER BY tr.uid_tarjeta
                LIMIT ?";
        
        $busqueda = '%' . $termino . '%';
        return $this->db->fetchAll($sql, [$busqueda, $busqueda, $busqueda, $limite]);
    }

    /**
     * Obtener tarjetas por rango de fechas
     */
    public function obtenerPorFechas($fechaInicio, $fechaFin, $incluirAsignaciones = true) {
        $sql = "SELECT tr.*, 
                       CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
                       u.numero_empleado
                FROM tarjetas_rfid tr 
                LEFT JOIN usuarios u ON tr.usuario_id = u.id 
                WHERE DATE(tr.created_at) BETWEEN ? AND ?";
        
        if ($incluirAsignaciones) {
            $sql .= " OR (tr.fecha_asignacion IS NOT NULL AND DATE(tr.fecha_asignacion) BETWEEN ? AND ?)";
            return $this->db->fetchAll($sql, [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);
        }
        
        return $this->db->fetchAll($sql, [$fechaInicio, $fechaFin]);
    }

    /**
     * Validar formato de UID
     */
    public function validarUID($uid) {
        // Validar que el UID tenga un formato válido (hexadecimal)
        return preg_match('/^[0-9A-Fa-f]+$/', $uid) && strlen($uid) >= 8 && strlen($uid) <= 20;
    }

    /**
     * Generar reporte de uso de tarjetas
     */
    public function obtenerReporteUso($fechaInicio, $fechaFin) {
        $sql = "SELECT tr.uid_tarjeta,
                       CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
                       u.numero_empleado,
                       COUNT(a.id) as total_marcaciones,
                       COUNT(CASE WHEN a.tipo = 'entrada' THEN 1 END) as entradas,
                       COUNT(CASE WHEN a.tipo = 'salida' THEN 1 END) as salidas,
                       MIN(a.fecha_hora) as primera_marcacion,
                       MAX(a.fecha_hora) as ultima_marcacion
                FROM tarjetas_rfid tr
                LEFT JOIN usuarios u ON tr.usuario_id = u.id
                LEFT JOIN asistencias a ON tr.id = a.tarjeta_id 
                    AND DATE(a.fecha_hora) BETWEEN ? AND ?
                    AND a.valido = 1
                WHERE tr.estado = 'activa'
                GROUP BY tr.id, tr.uid_tarjeta, usuario_nombre, u.numero_empleado
                ORDER BY total_marcaciones DESC";
        
        return $this->db->fetchAll($sql, [$fechaInicio, $fechaFin]);
    }
}