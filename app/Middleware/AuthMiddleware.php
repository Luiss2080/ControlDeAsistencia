<?php
/**
 * Middleware de Autenticación
 * Sistema de Control de Asistencia
 */

namespace App\Middleware;

use App\Utils\Auth;

class AuthMiddleware {
    
    /**
     * Verificar autenticación básica
     */
    public static function verificarAutenticacion() {
        Auth::iniciar();
        Auth::requerir();
    }

    /**
     * Verificar rol de administrador
     */
    public static function verificarAdmin() {
        Auth::iniciar();
        Auth::requerirRol('administrador');
    }

    /**
     * Verificar rol de RRHH
     */
    public static function verificarRRHH() {
        Auth::iniciar();
        
        $usuario = Auth::usuario();
        if (!$usuario || !in_array($usuario['rol_nombre'], ['administrador', 'rrhh'])) {
            if (Auth::esAjax()) {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                exit;
            } else {
                header('Location: /acceso-denegado');
                exit;
            }
        }
    }

    /**
     * Verificar acceso a empleado (propio perfil o admin/rrhh)
     */
    public static function verificarAccesoEmpleado($empleadoId = null) {
        Auth::iniciar();
        Auth::requerir();
        
        $usuario = Auth::usuario();
        
        // Admin y RRHH pueden ver cualquier empleado
        if (in_array($usuario['rol_nombre'], ['administrador', 'rrhh'])) {
            return true;
        }
        
        // Empleado solo puede ver su propio perfil
        if ($empleadoId && $usuario['id'] != $empleadoId) {
            if (Auth::esAjax()) {
                http_response_code(403);
                echo json_encode(['error' => 'Solo puedes acceder a tu propia información']);
                exit;
            } else {
                header('Location: /acceso-denegado');
                exit;
            }
        }
        
        return true;
    }

    /**
     * Verificar token CSRF
     */
    public static function verificarCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if (!Auth::verificarTokenCSRF($token)) {
                if (Auth::esAjax()) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Token CSRF inválido']);
                    exit;
                } else {
                    header('Location: /error/csrf');
                    exit;
                }
            }
        }
    }

    /**
     * Verificar límite de velocidad (rate limiting)
     */
    public static function verificarRateLimit($clave = null, $limite = 60, $ventana = 60) {
        if (!$clave) {
            $clave = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $clave = 'rate_limit_' . md5($clave);
        
        if (!isset($_SESSION[$clave])) {
            $_SESSION[$clave] = ['count' => 0, 'window_start' => time()];
        }
        
        $data = $_SESSION[$clave];
        $ahora = time();
        
        // Resetear ventana si ha pasado el tiempo
        if (($ahora - $data['window_start']) >= $ventana) {
            $_SESSION[$clave] = ['count' => 1, 'window_start' => $ahora];
            return true;
        }
        
        // Incrementar contador
        $_SESSION[$clave]['count']++;
        
        // Verificar límite
        if ($data['count'] >= $limite) {
            if (Auth::esAjax()) {
                http_response_code(429);
                echo json_encode(['error' => 'Demasiadas peticiones. Intenta más tarde.']);
                exit;
            } else {
                header('HTTP/1.1 429 Too Many Requests');
                echo 'Demasiadas peticiones. Intenta más tarde.';
                exit;
            }
        }
        
        return true;
    }

    /**
     * Verificar sesión activa y renovar si es necesario
     */
    public static function verificarSesionActiva() {
        Auth::iniciar();
        
        if (!Auth::estaAutenticado()) {
            return false;
        }
        
        // Verificar timeout de sesión
        $config = require_once __DIR__ . '/../../config/app.php';
        $timeout = $config['security']['session_lifetime'] ?? 7200; // 2 horas por defecto
        
        if (isset($_SESSION['ultimo_acceso'])) {
            if ((time() - $_SESSION['ultimo_acceso']) > $timeout) {
                Auth::logout();
                
                if (Auth::esAjax()) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Sesión expirada']);
                    exit;
                } else {
                    header('Location: /login?expired=1');
                    exit;
                }
            }
        }
        
        // Actualizar último acceso
        $_SESSION['ultimo_acceso'] = time();
        
        return true;
    }

    /**
     * Verificar integridad de sesión
     */
    public static function verificarIntegridadSesion() {
        Auth::iniciar();
        
        if (!Auth::estaAutenticado()) {
            return false;
        }
        
        // Verificar que la IP no haya cambiado (opcional, puede ser problemático con proxies)
        $config = require_once __DIR__ . '/../../config/app.php';
        
        if ($config['security']['check_ip'] ?? false) {
            $ipLogin = $_SESSION['ip_login'] ?? '';
            $ipActual = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            if ($ipLogin !== $ipActual) {
                Auth::logout();
                
                if (Auth::esAjax()) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Sesión comprometida']);
                    exit;
                } else {
                    header('Location: /login?security=1');
                    exit;
                }
            }
        }
        
        return true;
    }

    /**
     * Limpiar sesiones expiradas
     */
    public static function limpiarSesionesExpiradas() {
        $db = \App\Models\Database::getInstance();
        
        // Eliminar sesiones inactivas de más de 24 horas
        $sql = "DELETE FROM sesiones WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $db->query($sql);
        
        // Eliminar tokens de recordar expirados
        $sql = "DELETE FROM tokens_recordar WHERE expira < NOW()";
        $db->query($sql);
    }

    /**
     * Middleware completo de seguridad
     */
    public static function seguridadCompleta() {
        self::verificarRateLimit();
        self::verificarSesionActiva();
        self::verificarIntegridadSesion();
        self::verificarCSRF();
    }
}