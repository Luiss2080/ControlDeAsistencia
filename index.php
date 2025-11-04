<?php
/**
 * Punto de entrada principal - Simplificado
 */

// Incluir configuración
require_once __DIR__ . '/config/bootstrap.php';

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace('/ControlDeAsistencia', '', $path);
$path = rtrim($path, '/');
if (empty($path)) $path = '/';

// Rutas básicas
if ($path === '/' || $path === '/login') {
    // Mostrar formulario de login
    if ($_POST) {
        // Procesar login
        require_once __DIR__ . '/app/Controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->procesarLogin();
    } else {
        // Mostrar login
        require_once __DIR__ . '/app/Controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->mostrarLogin();
    }
} elseif ($path === '/logout') {
    require_once __DIR__ . '/app/Controllers/AuthController.php';
    $controller = new \App\Controllers\AuthController();
    $controller->logout();
} elseif (strpos($path, '/admin') === 0) {
    require_once __DIR__ . '/app/Controllers/AdminController.php';
    $controller = new \App\Controllers\AdminController();
    $controller->dashboard();
} elseif (strpos($path, '/rrhh') === 0) {
    require_once __DIR__ . '/app/Controllers/RRHHController.php';
    $controller = new \App\Controllers\RRHHController();
    $controller->dashboard();
} elseif (strpos($path, '/empleado') === 0) {
    require_once __DIR__ . '/app/Controllers/EmpleadoController.php';
    $controller = new \App\Controllers\EmpleadoController();
    $controller->dashboard();
} else {
    // Página no encontrada
    http_response_code(404);
    echo '<h1>404 - Página no encontrada</h1>';
    echo '<p>La página que buscas no existe.</p>';
    echo '<a href="/ControlDeAsistencia">Volver al inicio</a>';
}
?>