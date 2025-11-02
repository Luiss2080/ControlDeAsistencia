<?php
/**
 * Modelo para Dispositivos Lectores
 * Sistema de Control de Asistencia
 */

namespace App\Models;

use App\Models\Database;

class Dispositivo {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener dispositivo por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM dispositivos WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Obtener dispositivo por token
     */
    public function obtenerPorToken($token) {
        $sql = "SELECT * FROM dispositivos WHERE token_dispositivo = ?";
        return $this->db->fetch($sql, [$token]);
    }

    /**
     * Obtener dispositivo por MAC address
     */
    public function obtenerPorMAC($macAddress) {
        $sql = "SELECT * FROM dispositivos WHERE mac_address = ?";
        return $this->db->fetch($sql, [$macAddress]);
    }

    /**
     * Crear nuevo dispositivo
     */
    public function crear($datos) {
        // Generar token único para el dispositivo
        $datos['token_dispositivo'] = $this->generarToken();
        
        return $this->db->insert('dispositivos', $datos);
    }

    /**
     * Actualizar dispositivo
     */
    public function actualizar($id, $datos) {
        return $this->db->update('dispositivos', $datos, 'id = ?', [$id]);
    }

    /**
     * Obtener todos los dispositivos
     */
    public function obtenerTodos($filtros = []) {
        $where = ["1 = 1"];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[] = "estado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['busqueda'])) {
            $where[] = "(nombre LIKE ? OR ubicacion LIKE ? OR ip_address LIKE ?)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params = array_merge($params, [$busqueda, $busqueda, $busqueda]);
        }

        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT *, 
                CASE 
                    WHEN ultimo_ping >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'online'
                    WHEN ultimo_ping >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'warning'
                    ELSE 'offline'
                END as status_conexion
                FROM dispositivos 
                WHERE {$whereClause}
                ORDER BY nombre";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtener dispositivos activos
     */
    public function obtenerActivos() {
        $sql = "SELECT * FROM dispositivos WHERE estado = 'activo' ORDER BY nombre";
        return $this->db->fetchAll($sql);
    }

    /**
     * Actualizar último ping
     */
    public function actualizarPing($id, $ipAddress = null) {
        $datos = ['ultimo_ping' => date('Y-m-d H:i:s')];
        
        if ($ipAddress) {
            $datos['ip_address'] = $ipAddress;
        }
        
        return $this->db->update('dispositivos', $datos, 'id = ?', [$id]);
    }

    /**
     * Cambiar estado del dispositivo
     */
    public function cambiarEstado($id, $estado) {
        $estadosValidos = ['activo', 'inactivo', 'mantenimiento'];
        
        if (!in_array($estado, $estadosValidos)) {
            throw new \InvalidArgumentException('Estado no válido');
        }
        
        return $this->db->update('dispositivos', ['estado' => $estado], 'id = ?', [$id]);
    }

    /**
     * Regenerar token del dispositivo
     */
    public function regenerarToken($id) {
        $nuevoToken = $this->generarToken();
        
        $resultado = $this->db->update(
            'dispositivos', 
            ['token_dispositivo' => $nuevoToken], 
            'id = ?', 
            [$id]
        );
        
        return $resultado ? $nuevoToken : false;
    }

    /**
     * Eliminar dispositivo (soft delete)
     */
    public function eliminar($id) {
        return $this->cambiarEstado($id, 'inactivo');
    }

    /**
     * Obtener estadísticas de dispositivos
     */
    public function obtenerEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_dispositivos,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as dispositivos_activos,
                    COUNT(CASE WHEN ultimo_ping >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 END) as online,
                    COUNT(CASE WHEN ultimo_ping < DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                              AND ultimo_ping >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as warning,
                    COUNT(CASE WHEN ultimo_ping < DATE_SUB(NOW(), INTERVAL 1 HOUR) 
                              OR ultimo_ping IS NULL THEN 1 END) as offline
                FROM dispositivos 
                WHERE estado = 'activo'";
        
        return $this->db->fetch($sql);
    }

    /**
     * Obtener actividad reciente de dispositivos
     */
    public function obtenerActividadReciente($limite = 50) {
        $sql = "SELECT d.nombre, d.ubicacion, a.fecha_hora, a.tipo,
                       CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre
                FROM asistencias a
                JOIN dispositivos d ON a.dispositivo_id = d.id
                JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.valido = 1
                ORDER BY a.fecha_hora DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limite]);
    }

    /**
     * Verificar si MAC address ya existe
     */
    public function macExiste($macAddress, $excluirId = null) {
        $sql = "SELECT id FROM dispositivos WHERE mac_address = ?";
        $params = [$macAddress];

        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }

        $resultado = $this->db->fetch($sql, $params);
        return !empty($resultado);
    }

    /**
     * Obtener dispositivos offline
     */
    public function obtenerOffline($minutos = 60) {
        $sql = "SELECT * FROM dispositivos 
                WHERE estado = 'activo' 
                AND (ultimo_ping < DATE_SUB(NOW(), INTERVAL ? MINUTE) OR ultimo_ping IS NULL)
                ORDER BY nombre";
        
        return $this->db->fetchAll($sql, [$minutos]);
    }

    /**
     * Obtener uso de dispositivos por periodo
     */
    public function obtenerUsoPorPeriodo($fechaInicio, $fechaFin) {
        $sql = "SELECT d.nombre, d.ubicacion,
                       COUNT(a.id) as total_marcaciones,
                       COUNT(DISTINCT a.usuario_id) as usuarios_unicos,
                       COUNT(CASE WHEN a.tipo = 'entrada' THEN 1 END) as entradas,
                       COUNT(CASE WHEN a.tipo = 'salida' THEN 1 END) as salidas
                FROM dispositivos d
                LEFT JOIN asistencias a ON d.id = a.dispositivo_id 
                    AND DATE(a.fecha_hora) BETWEEN ? AND ?
                    AND a.valido = 1
                WHERE d.estado = 'activo'
                GROUP BY d.id, d.nombre, d.ubicacion
                ORDER BY total_marcaciones DESC";
        
        return $this->db->fetchAll($sql, [$fechaInicio, $fechaFin]);
    }

    // Métodos privados

    /**
     * Generar token único para dispositivo
     */
    private function generarToken($longitud = 64) {
        do {
            $token = bin2hex(random_bytes($longitud / 2));
            
            // Verificar que el token no exista
            $existe = $this->db->fetch(
                "SELECT id FROM dispositivos WHERE token_dispositivo = ?",
                [$token]
            );
        } while ($existe);
        
        return $token;
    }

    /**
     * Validar MAC address
     */
    private function validarMAC($macAddress) {
        return preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macAddress);
    }

    /**
     * Normalizar MAC address
     */
    private function normalizarMAC($macAddress) {
        // Convertir a formato estándar XX:XX:XX:XX:XX:XX
        $mac = strtoupper(str_replace(['-', '.'], ':', $macAddress));
        return $mac;
    }
}