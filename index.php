<?php
/**
 * Punto de entrada principal del sistema
 * Sistema de Control de Asistencia
 */

// Configuración de errores para producción
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Incluir configuración
require_once __DIR__ . '/config/bootstrap.php';

// Incluir el archivo de rutas
require_once __DIR__ . '/src/routes.php';

// Obtener la ruta actual
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Normalizar la URI
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace('/ControlDeAsistencia', '', $uri);
$uri = rtrim($uri, '/');
if (empty($uri)) $uri = '/';

// Enrutamiento simple y directo
try {
    if ($requestMethod === 'GET') {
        switch ($uri) {
            case '/':
            case '/login':
                $controller = new \App\Controllers\AuthController();
                $controller->mostrarLogin();
                break;
                
            case '/admin':
            case '/admin/dashboard':
                \App\Controllers\AuthController::requerirRol('admin');
                $controller = new \App\Controllers\AdminController();
                $controller->dashboard();
                break;
                
            case '/rrhh':
            case '/rrhh/dashboard':
                \App\Controllers\AuthController::requerirRol('rrhh');
                $controller = new \App\Controllers\RRHHController();
                $controller->dashboard();
                break;
                
            case '/empleado':
            case '/empleado/dashboard':
                \App\Controllers\AuthController::requerirRol('empleado');
                $controller = new \App\Controllers\EmpleadoController();
                $controller->dashboard();
                break;
                
            case '/logout':
                $controller = new \App\Controllers\AuthController();
                $controller->logout();
                break;
                
            default:
                http_response_code(404);
                echo '<h1>404 - Página no encontrada</h1>';
                echo '<p>La ruta "' . htmlspecialchars($uri) . '" no existe.</p>';
                echo '<a href="/ControlDeAsistencia/">Volver al inicio</a>';
        }
    } elseif ($requestMethod === 'POST') {
        switch ($uri) {
            case '/login':
                $controller = new \App\Controllers\AuthController();
                $controller->procesarLogin();
                break;
                
            default:
                http_response_code(405);
                echo '<h1>405 - Método no permitido</h1>';
        }
    }
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en router: " . $e->getMessage());
    
    // Mostrar error en modo debug
    echo "<h1>Error en el sistema</h1>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Archivo: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}