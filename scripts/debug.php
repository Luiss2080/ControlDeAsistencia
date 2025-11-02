<?php
/**
 * Archivo de prueba de rutas
 */

echo "<h1>🧪 Prueba de Rutas</h1>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'No definido') . "</p>";

// Incluir configuración
require_once __DIR__ . '/config/bootstrap.php';

// Incluir router
require_once __DIR__ . '/routes.php';

echo "<p>✅ Bootstrap y router incluidos</p>";

try {
    $router = new Router();
    echo "<p>✅ Router creado correctamente</p>";
    
    echo "<h2>🔍 Procesando ruta...</h2>";
    $router->procesarRuta();
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}
?>