<?php
/**
 * Punto de entrada principal del sistema
 * Sistema de Control de Asistencia
 */

// Incluir configuración
require_once __DIR__ . '/config/bootstrap.php';

// Incluir el archivo de rutas
require_once __DIR__ . '/src/routes.php';

// Crear router y procesar la ruta
try {
    $router = new Router();
    $router->procesarRuta();
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en router: " . $e->getMessage());
    
    // Mostrar error en modo debug
    if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo "<h1>Error en el sistema</h1>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>Archivo: " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        // En producción, mostrar página de error amigable
        http_response_code(500);
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Sistema de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; margin-bottom: 20px; }
        .btn { background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Error del Sistema</h1>
        <p>Lo sentimos, ha ocurrido un error interno. Por favor, intenta nuevamente.</p>
        <a href="/ControlDeAsistencia/" class="btn">🏠 Volver al inicio</a>
    </div>
</body>
</html>';
    }
}