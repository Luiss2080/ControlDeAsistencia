<?php
/**
 * Controlador de Autenticación
 * Sistema de Control de Asistencia
 */

namespace App\Controllers;

use App\Models\Database;
use App\Utils\Auth;

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Mostrar formulario de login
     */
    public function mostrarLogin() {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            $this->redirigirSegunRol();
            return;
        }

        // Variables para la vista
        $titulo = 'Iniciar Sesión - Sistema de Asistencia';
        $csrf_token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;

        // Incluir vista de login
        include __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Procesar login
     */
    public function procesarLogin() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            header('Location: /login?error=Email y contraseña son requeridos');
            exit;
        }

        // Buscar usuario en la base de datos
        $sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
        $usuario = $this->db->fetch($sql, [$email]);

        if (!$usuario) {
            header('Location: /login?error=Usuario no encontrado');
            exit;
        }

        // Verificar contraseña
        if (!password_verify($password, $usuario['password_hash'])) {
            header('Location: /login?error=Contraseña incorrecta');
            exit;
        }

        // Establecer sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nombres' => $usuario['nombres'],
            'apellidos' => $usuario['apellidos'],
            'email' => $usuario['email'],
            'rol' => $usuario['rol']
        ];

        // Actualizar último login
        $this->db->query("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?", [$usuario['id']]);

        // Redirigir según rol
        $this->redirigirSegunRol();
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        header('Location: /login?mensaje=Sesión cerrada correctamente');
        exit;
    }

    /**
     * Redirigir según el rol del usuario
     */
    private function redirigirSegunRol() {
        $rol = $_SESSION['usuario_rol'] ?? '';
        
        switch ($rol) {
            case 'admin':
                header('Location: /admin/dashboard');
                break;
            case 'rrhh':
                header('Location: /rrhh/dashboard');
                break;
            case 'empleado':
                header('Location: /empleado/dashboard');
                break;
            default:
                header('Location: /login?error=Rol no válido');
        }
        exit;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function estaAutenticado() {
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Requerir autenticación
     */
    public static function requerir() {
        if (!self::estaAutenticado()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Requerir rol específico
     */
    public static function requerirRol($rol) {
        self::requerir();
        
        if ($_SESSION['usuario_rol'] !== $rol) {
            header('Location: /login?error=Acceso denegado');
            exit;
        }
    }
}
?>