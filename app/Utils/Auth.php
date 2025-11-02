<?php
/**
 * Clase para manejo de autenticación y sesiones
 * Sistema de Control de Asistencia
 */

namespace App\Utils;

use App\Models\Usuario;
use App\Models\Database;

class Auth {
    private static $usuario = null;
    private static $sesion_iniciada = false;

    /**
     * Inicializar sesión
     */
    public static function iniciar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$sesion_iniciada = true;
        }
        
        // Verificar si hay un usuario en sesión
        if (isset($_SESSION['usuario_id'])) {
            self::cargarUsuario($_SESSION['usuario_id']);
        }
    }

    /**
     * Intentar login
     */
    public static function login($email, $password, $recordar = false) {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorEmail($email);
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        if (!$usuarioModel->verificarPassword($password, $usuario['password_hash'])) {
            self::registrarIntentoFallido($email);
            return ['success' => false, 'message' => 'Contraseña incorrecta'];
        }

        // Verificar si la cuenta está activa
        if (!$usuario['activo']) {
            return ['success' => false, 'message' => 'Cuenta desactivada'];
        }

        // Verificar intentos fallidos
        if (self::cuentaBloqueada($email)) {
            return ['success' => false, 'message' => 'Cuenta temporalmente bloqueada por múltiples intentos fallidos'];
        }

        // Login exitoso
        self::establecerSesion($usuario);
        
        // Actualizar último login
        $usuarioModel->actualizarUltimoLogin($usuario['id']);
        
        // Limpiar intentos fallidos
        self::limpiarIntentosFallidos($email);

        // Configurar cookie de recordar si se solicita
        if ($recordar) {
            self::establecerCookieRecordar($usuario['id']);
        }

        // Registrar login en logs
        self::registrarLog($usuario['id'], 'LOGIN', 'Inicio de sesión exitoso');

        return ['success' => true, 'message' => 'Login exitoso', 'usuario' => self::$usuario];
    }

    /**
     * Cerrar sesión
     */
    public static function logout() {
        if (self::$usuario) {
            self::registrarLog(self::$usuario['id'], 'LOGOUT', 'Cierre de sesión');
        }

        // Limpiar datos de sesión
        $_SESSION = [];
        
        // Destruir la sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        // Limpiar cookie de recordar
        if (isset($_COOKIE['recordar_token'])) {
            setcookie('recordar_token', '', time() - 3600, '/');
            self::eliminarTokenRecordar($_COOKIE['recordar_token']);
        }
        
        self::$usuario = null;
        self::$sesion_iniciada = false;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function estaAutenticado() {
        return self::$usuario !== null;
    }

    /**
     * Obtener usuario actual
     */
    public static function usuario() {
        return self::$usuario;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function tieneRol($rol) {
        if (!self::$usuario) return false;
        return self::$usuario['rol_nombre'] === $rol;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public static function tienePermiso($permiso) {
        if (!self::$usuario) return false;
        
        $permisos = json_decode(self::$usuario['permisos'], true);
        
        // Si tiene permiso total
        if (in_array('*', $permisos)) return true;
        
        // Verificar permiso específico
        if (in_array($permiso, $permisos)) return true;
        
        // Verificar permiso con comodín
        foreach ($permisos as $p) {
            if (strpos($p, '*') !== false) {
                $patron = str_replace('*', '.*', $p);
                if (preg_match("/^{$patron}$/", $permiso)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Requerir autenticación
     */
    public static function requerir() {
        if (!self::estaAutenticado()) {
            if (self::esAjax()) {
                http_response_code(401);
                echo json_encode(['error' => 'No autenticado']);
                exit;
            } else {
                header('Location: /login');
                exit;
            }
        }
    }

    /**
     * Requerir rol específico
     */
    public static function requerirRol($rol) {
        self::requerir();
        
        if (!self::tieneRol($rol)) {
            if (self::esAjax()) {
                http_response_code(403);
                echo json_encode(['error' => 'Sin permisos']);
                exit;
            } else {
                header('Location: /acceso-denegado');
                exit;
            }
        }
    }

    /**
     * Requerir permiso específico
     */
    public static function requerirPermiso($permiso) {
        self::requerir();
        
        if (!self::tienePermiso($permiso)) {
            if (self::esAjax()) {
                http_response_code(403);
                echo json_encode(['error' => 'Sin permisos para esta acción']);
                exit;
            } else {
                header('Location: /acceso-denegado');
                exit;
            }
        }
    }

    /**
     * Generar token CSRF
     */
    public static function generarTokenCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verificar token CSRF
     */
    public static function verificarTokenCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Regenerar ID de sesión
     */
    public static function regenerarSesion() {
        session_regenerate_id(true);
    }

    // Métodos privados
    
    private static function cargarUsuario($userId) {
        $usuarioModel = new Usuario();
        self::$usuario = $usuarioModel->obtenerPorId($userId);
    }

    private static function establecerSesion($usuario) {
        self::regenerarSesion();
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol_nombre'];
        $_SESSION['ultimo_acceso'] = time();
        $_SESSION['ip_login'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        self::$usuario = $usuario;
        
        // Guardar sesión en base de datos
        self::guardarSesionDB($usuario['id']);
    }

    private static function guardarSesionDB($userId) {
        $db = Database::getInstance();
        
        $datos = [
            'id' => session_id(),
            'usuario_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'datos_sesion' => json_encode($_SESSION)
        ];
        
        // Insertar o actualizar sesión
        $sql = "INSERT INTO sesiones (id, usuario_id, ip_address, user_agent, datos_sesion) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                last_activity = CURRENT_TIMESTAMP, 
                datos_sesion = VALUES(datos_sesion)";
        
        $db->query($sql, array_values($datos));
    }

    private static function registrarIntentoFallido($email) {
        if (!isset($_SESSION['intentos_fallidos'])) {
            $_SESSION['intentos_fallidos'] = [];
        }
        
        if (!isset($_SESSION['intentos_fallidos'][$email])) {
            $_SESSION['intentos_fallidos'][$email] = [];
        }
        
        $_SESSION['intentos_fallidos'][$email][] = time();
    }

    private static function cuentaBloqueada($email) {
        if (!isset($_SESSION['intentos_fallidos'][$email])) {
            return false;
        }
        
        $intentos = $_SESSION['intentos_fallidos'][$email];
        $ahora = time();
        $ventana = 900; // 15 minutos
        $maxIntentos = 5;
        
        // Filtrar intentos recientes
        $intentosRecientes = array_filter($intentos, function($tiempo) use ($ahora, $ventana) {
            return ($ahora - $tiempo) < $ventana;
        });
        
        return count($intentosRecientes) >= $maxIntentos;
    }

    private static function limpiarIntentosFallidos($email) {
        if (isset($_SESSION['intentos_fallidos'][$email])) {
            unset($_SESSION['intentos_fallidos'][$email]);
        }
    }

    private static function establecerCookieRecordar($userId) {
        $token = bin2hex(random_bytes(32));
        $expira = time() + (30 * 24 * 60 * 60); // 30 días
        
        setcookie('recordar_token', $token, $expira, '/', '', true, true);
        
        // Guardar token en base de datos
        $db = Database::getInstance();
        $sql = "INSERT INTO tokens_recordar (token, usuario_id, expira) VALUES (?, ?, ?)";
        $db->query($sql, [$token, $userId, date('Y-m-d H:i:s', $expira)]);
    }

    private static function eliminarTokenRecordar($token) {
        $db = Database::getInstance();
        $sql = "DELETE FROM tokens_recordar WHERE token = ?";
        $db->query($sql, [$token]);
    }

    private static function registrarLog($userId, $accion, $descripcion) {
        $db = Database::getInstance();
        
        $datos = [
            'usuario_id' => $userId,
            'accion' => $accion,
            'tabla_afectada' => 'sesiones',
            'datos_nuevos' => json_encode(['descripcion' => $descripcion]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $db->insert('logs_sistema', $datos);
    }

    private static function esAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}