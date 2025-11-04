<?php
/**
 * Modelo de Usuario Simplificado
 * Sistema de Control de Asistencia
 */

namespace App\Models;

class Usuario extends Database {
    
    public function __construct() {
        parent::getInstance();
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
        return $this->fetch($sql, [$email]);
    }

    /**
     * Buscar usuario por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        return $this->fetch($sql, [$id]);
    }

    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        return $this->insert('usuarios', $data);
    }

    /**
     * Actualizar usuario
     */
    public function updateUser($id, $data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        return $this->update('usuarios', $data, 'id = ?', [$id]);
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
        $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
        return $this->query($sql, [$userId]);
    }

    /**
     * Obtener todos los usuarios activos
     */
    public function getAllActive() {
        $sql = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY apellidos, nombres";
        return $this->fetchAll($sql);
    }

    /**
     * Buscar usuarios con filtros
     */
    public function search($filters = []) {
        $sql = "SELECT * FROM usuarios WHERE activo = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (nombres LIKE ? OR apellidos LIKE ? OR email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search]);
        }

        if (!empty($filters['rol'])) {
            $sql .= " AND rol = ?";
            $params[] = $filters['rol'];
        }

        $sql .= " ORDER BY apellidos, nombres";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->fetch($sql, $params);
        return !empty($result);
    }

    /**
     * Desactivar usuario
     */
    public function deactivate($id) {
        return $this->query("UPDATE usuarios SET activo = 0 WHERE id = ?", [$id]);
    }

    /**
     * Activar usuario
     */
    public function activate($id) {
        return $this->query("UPDATE usuarios SET activo = 1 WHERE id = ?", [$id]);
    }
}
?>