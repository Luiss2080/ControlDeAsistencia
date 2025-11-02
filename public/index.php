<?php
/**
 * Punto de entrada principal del sistema
 * Sistema de Control de Asistencia
 */

// Incluir configuración
require_once __DIR__ . '/../config/bootstrap.php';

// Incluir el archivo de rutas
require_once __DIR__ . '/../src/routes.php';

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Limpiar la ruta
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace('/ControlDeAsistencia/public', '', $path);
$path = rtrim($path, '/');
if (empty($path)) $path = '/';

// Crear router
try {
    $router = new Router();
    
    // Simular la REQUEST_URI para el router
    $_SERVER['REQUEST_URI'] = $path;
    
    $router->procesarRuta();
    
} catch (Exception $e) {
    // Mostrar error en modo debug
    if ($_ENV['APP_DEBUG'] ?? false) {
        echo "<h1>Error en el sistema</h1>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>Archivo: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    } else {
        // En producción, redirigir a página de error
        header('HTTP/1.1 500 Internal Server Error');
        include __DIR__ . '/error.php';
    }
}