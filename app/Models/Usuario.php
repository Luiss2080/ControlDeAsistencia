<?php
/**
 * Modelo de Usuario
 * Sistema de Control de Asistencia
 */

namespace App\Models;

use PDO;

class Usuario extends Database {
    
    public function __construct() {
        parent::getInstance();
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos, d.nombre as departamento_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE u.email = :email AND u.activo = 1";
        
        return $this->fetch($sql, ['email' => $email]);
    }

    /**
     * Buscar usuario por número de empleado
     */
    public function findByNumeroEmpleado($numero) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos, d.nombre as departamento_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE u.numero_empleado = :numero AND u.activo = 1";
        
        return $this->fetch($sql, ['numero' => $numero]);
    }

    /**
     * Buscar usuario por ID
     */
    public function findById($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos, d.nombre as departamento_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE u.id = :id";
        
        return $this->fetch($sql, ['id' => $id]);
    }

    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        // Hash de la contraseña
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        return $this->insert('usuarios', $data);
    }

    /**
     * Actualizar usuario
     */
    public function updateUser($id, $data) {
        // Hash de la contraseña si se está actualizando
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update('usuarios', $data, 'id = :id', ['id' => $id]);
    }

    /**
     * Verificar contraseña
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Actualizar último login
     */
    public function updateLastLogin($userId) {
        $data = ['ultimo_login' => date('Y-m-d H:i:s')];
        return $this->update('usuarios', $data, 'id = :id', ['id' => $userId]);
    }

    /**
     * Obtener todos los usuarios activos
     */
    public function getAllActive($limit = null, $offset = 0) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, d.nombre as departamento_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE u.activo = 1 
                ORDER BY u.apellidos, u.nombres";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            return $this->fetchAll($sql, ['limit' => $limit, 'offset' => $offset]);
        }
        
        return $this->fetchAll($sql);
    }

    /**
     * Buscar usuarios con filtros
     */
    public function search($filters = []) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, d.nombre as departamento_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE u.activo = 1";
        
        $params = [];

        if (!empty($filters['departamento_id'])) {
            $sql .= " AND u.departamento_id = :departamento_id";
            $params['departamento_id'] = $filters['departamento_id'];
        }

        if (!empty($filters['rol_id'])) {
            $sql .= " AND u.rol_id = :rol_id";
            $params['rol_id'] = $filters['rol_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.nombres LIKE :search OR u.apellidos LIKE :search OR u.numero_empleado LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY u.apellidos, u.nombres";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function getEstadisticas() {
        $stats = [];

        // Total usuarios activos
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1";
        $stats['total_activos'] = $this->fetch($sql)['total'];

        // Total por departamento
        $sql = "SELECT d.nombre as departamento, COUNT(u.id) as total 
                FROM usuarios u 
                LEFT JOIN departamentos d ON u.departamento_id = d.id 
                WHERE u.activo = 1 
                GROUP BY d.nombre";
        $stats['por_departamento'] = $this->fetchAll($sql);

        // Total por rol
        $sql = "SELECT r.nombre as rol, COUNT(u.id) as total 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE u.activo = 1 
                GROUP BY r.nombre";
        $stats['por_rol'] = $this->fetchAll($sql);

        return $stats;
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->fetch($sql, $params);
        return !empty($result);
    }

    /**
     * Verificar si el número de empleado ya existe
     */
    public function numeroEmpleadoExists($numero, $excludeId = null) {
        $sql = "SELECT id FROM usuarios WHERE numero_empleado = :numero";
        $params = ['numero' => $numero];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->fetch($sql, $params);
        return !empty($result);
    }

    /**
     * Generar número de empleado automático
     */
    public function generateNumeroEmpleado() {
        $sql = "SELECT numero_empleado FROM usuarios ORDER BY numero_empleado DESC LIMIT 1";
        $last = $this->fetch($sql);
        
        if ($last) {
            $numero = intval(substr($last['numero_empleado'], 3)) + 1;
        } else {
            $numero = 1;
        }
        
        return 'EMP' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Desactivar usuario
     */
    public function deactivate($id) {
        return $this->update('usuarios', ['activo' => 0], 'id = :id', ['id' => $id]);
    }

    /**
     * Activar usuario
     */
    public function activate($id) {
        return $this->update('usuarios', ['activo' => 1], 'id = :id', ['id' => $id]);
    }
}