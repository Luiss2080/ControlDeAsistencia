<?php
/**
 * Tests básicos del sistema
 */

class SystemTests {
    private $db;
    private $results = [];
    
    public function __construct() {
        require_once __DIR__ . '/../config/bootstrap.php';
        $this->db = \App\Models\Database::getInstance();
    }
    
    public function runAllTests() {
        echo "<h1>🧪 Tests del Sistema de Control de Asistencia</h1>";
        
        $this->testDatabaseConnection();
        $this->testAuthClass();
        $this->testControllers();
        $this->testRoutes();
        $this->testAPI();
        
        $this->showResults();
    }
    
    private function testDatabaseConnection() {
        echo "<h2>🗄️ Test: Conexión a Base de Datos</h2>";
        
        try {
            $users = $this->db->fetchAll("SELECT COUNT(*) as count FROM usuarios");
            $this->addResult("DB Connection", true, "Conexión exitosa - {$users[0]['count']} usuarios");
        } catch (Exception $e) {
            $this->addResult("DB Connection", false, $e->getMessage());
        }
    }
    
    private function testAuthClass() {
        echo "<h2>🔐 Test: Clase de Autenticación</h2>";
        
        try {
            \App\Utils\Auth::iniciar();
            $token = \App\Utils\Auth::generarTokenCSRF();
            $this->addResult("Auth Class", true, "Token CSRF generado: " . substr($token, 0, 10) . "...");
        } catch (Exception $e) {
            $this->addResult("Auth Class", false, $e->getMessage());
        }
    }
    
    private function testControllers() {
        echo "<h2>🎛️ Test: Controladores</h2>";
        
        $controllers = [
            'AuthController' => '\App\Controllers\AuthController',
            'AdminController' => '\App\Controllers\AdminController',
            'RRHHController' => '\App\Controllers\RRHHController',
            'EmpleadoController' => '\App\Controllers\EmpleadoController'
        ];
        
        foreach ($controllers as $name => $class) {
            try {
                if (class_exists($class)) {
                    $this->addResult($name, true, "Clase existe y es accesible");
                } else {
                    $this->addResult($name, false, "Clase no encontrada");
                }
            } catch (Exception $e) {
                $this->addResult($name, false, $e->getMessage());
            }
        }
    }
    
    private function testRoutes() {
        echo "<h2>🛣️ Test: Sistema de Rutas</h2>";
        
        try {
            require_once __DIR__ . '/../src/routes.php';
            $router = new Router();
            $this->addResult("Router", true, "Router creado correctamente");
        } catch (Exception $e) {
            $this->addResult("Router", false, $e->getMessage());
        }
    }
    
    private function testAPI() {
        echo "<h2>🌐 Test: API Endpoints</h2>";
        
        // Test básico de estructura de API
        if (file_exists(__DIR__ . '/../api/index.php')) {
            $this->addResult("API File", true, "Archivo API existe");
        } else {
            $this->addResult("API File", false, "Archivo API no encontrado");
        }
    }
    
    private function addResult($test, $success, $message) {
        $this->results[] = [
            'test' => $test,
            'success' => $success,
            'message' => $message
        ];
        
        $icon = $success ? "✅" : "❌";
        echo "<p>$icon $test: $message</p>";
    }
    
    private function showResults() {
        $total = count($this->results);
        $passed = count(array_filter($this->results, function($r) { return $r['success']; }));
        $failed = $total - $passed;
        
        echo "<h2>📊 Resumen de Tests</h2>";
        echo "<p><strong>Total:</strong> $total</p>";
        echo "<p><strong>✅ Exitosos:</strong> $passed</p>";
        echo "<p><strong>❌ Fallidos:</strong> $failed</p>";
        
        if ($failed == 0) {
            echo "<h3 style='color: green;'>🎉 ¡Todos los tests pasaron!</h3>";
        } else {
            echo "<h3 style='color: red;'>⚠️ Algunos tests fallaron. Revisa la configuración.</h3>";
        }
    }
}

// Ejecutar tests
$tests = new SystemTests();
$tests->runAllTests();
?>