<?php
/**
 * Test del AdminController
 * Para verificar que todas las funciones trabajen correctamente
 */

// Configurar errores para ver problemas
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Simular sesión de admin
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_rol'] = 'admin';
$_SESSION['usuario'] = [
    'id' => 1,
    'nombres' => 'Test',
    'apellidos' => 'Admin',
    'email' => 'admin@test.com',
    'rol' => 'admin'
];

echo "<h1>🧪 Test del AdminController</h1>";

try {
    require_once __DIR__ . '/app/Models/Database.php';
    require_once __DIR__ . '/app/Controllers/AdminController.php';
    
    $adminController = new \App\Controllers\AdminController();
    
    echo "<h2>✅ AdminController creado exitosamente</h2>";
    
    // Test 1: Obtener estadísticas
    echo "<h3>Test 1: Estadísticas</h3>";
    ob_start();
    try {
        $reflection = new ReflectionClass($adminController);
        $method = $reflection->getMethod('obtenerEstadisticas');
        $method->setAccessible(true);
        $stats = $method->invoke($adminController);
        
        echo "✅ Estadísticas obtenidas:<br>";
        foreach ($stats as $key => $value) {
            echo "&nbsp;&nbsp;- {$key}: {$value}<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error en estadísticas: " . $e->getMessage() . "<br>";
    }
    ob_end_flush();
    
    // Test 2: Actividad reciente
    echo "<h3>Test 2: Actividad Reciente</h3>";
    ob_start();
    try {
        $reflection = new ReflectionClass($adminController);
        $method = $reflection->getMethod('obtenerActividadReciente');
        $method->setAccessible(true);
        $actividad = $method->invoke($adminController);
        
        echo "✅ Actividad reciente obtenida: " . count($actividad) . " registros<br>";
        if (count($actividad) > 0) {
            echo "&nbsp;&nbsp;Primer registro: " . json_encode($actividad[0]) . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error en actividad reciente: " . $e->getMessage() . "<br>";
    }
    ob_end_flush();
    
    // Test 3: Buscar usuarios
    echo "<h3>Test 3: Buscar Usuarios</h3>";
    ob_start();
    try {
        $reflection = new ReflectionClass($adminController);
        $method = $reflection->getMethod('buscarUsuarios');
        $method->setAccessible(true);
        $usuarios = $method->invoke($adminController, ['search' => '', 'rol' => '']);
        
        echo "✅ Búsqueda de usuarios exitosa: " . count($usuarios) . " usuarios encontrados<br>";
        if (count($usuarios) > 0) {
            echo "&nbsp;&nbsp;Primer usuario: {$usuarios[0]['nombres']} {$usuarios[0]['apellidos']}<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error en búsqueda de usuarios: " . $e->getMessage() . "<br>";
    }
    ob_end_flush();
    
    echo "<h2>🎉 Tests completados</h2>";
    echo "<p><a href='/ControlDeAsistencia/admin'>← Ir al dashboard admin</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error critico</h2>";
    echo "<p>No se pudo crear el AdminController: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "</p>";
}
?>