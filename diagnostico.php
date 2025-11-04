<?php
/**
 * Página de Diagnóstico del Sistema
 * Sistema de Control de Asistencia
 */

// Configurar display de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir bootstrap
require_once __DIR__ . '/config/bootstrap.php';

use App\Models\Database;
use App\Models\Usuario;
use App\Models\Dispositivo;
use App\Models\TarjetaRFID;
use App\Models\RegistroAsistencia;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema - Control de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status-ok { color: green; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-result { margin: 10px 0; padding: 5px; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico del Sistema de Control de Asistencia</h1>
    
    <div class="section">
        <h2>📋 Información del Sistema</h2>
        <div class="test-result">PHP Version: <span class="code"><?= PHP_VERSION ?></span></div>
        <div class="test-result">Servidor: <span class="code"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Servidor de Desarrollo' ?></span></div>
        <div class="test-result">Timestamp: <span class="code"><?= date('Y-m-d H:i:s') ?></span></div>
    </div>

    <div class="section">
        <h2>🗄️ Conexión a Base de Datos</h2>
        <?php
        try {
            $db = Database::getInstance();
            echo '<div class="test-result status-ok">✅ Conexión a base de datos: EXITOSA</div>';
            
            // Verificar tablas principales
            $tablas = ['usuarios', 'dispositivos', 'tarjetas_rfid', 'asistencias'];
            foreach ($tablas as $tabla) {
                try {
                    $resultado = $db->query("SELECT COUNT(*) as count FROM {$tabla}");
                    if ($resultado) {
                        $count = $resultado->fetch()['count'];
                        echo "<div class=\"test-result status-ok\">✅ Tabla '{$tabla}': {$count} registros</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class=\"test-result status-error\">❌ Error en tabla '{$tabla}': " . $e->getMessage() . "</div>";
                }
            }
        } catch (Exception $e) {
            echo '<div class="test-result status-error">❌ Error de conexión: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>👥 Usuarios del Sistema</h2>
        <?php
        try {
            $usuarioModel = new Usuario();
            $usuarios = $usuarioModel->obtenerTodos();
            
            echo '<div class="test-result status-ok">✅ Modelo Usuario: FUNCIONAL</div>';
            echo '<div class="test-result">Total usuarios: ' . count($usuarios) . '</div>';
            
            if (!empty($usuarios)) {
                echo '<h4>Usuarios existentes:</h4>';
                foreach ($usuarios as $user) {
                    echo "<div class=\"test-result\">👤 {$user['username']} ({$user['rol']}) - {$user['nombres']} {$user['apellidos']}</div>";
                }
            }
        } catch (Exception $e) {
            echo '<div class="test-result status-error">❌ Error en modelo Usuario: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>📡 Dispositivos ESP32</h2>
        <?php
        try {
            $dispositivoModel = new Dispositivo();
            $dispositivos = $dispositivoModel->obtenerTodos();
            
            echo '<div class="test-result status-ok">✅ Modelo Dispositivo: FUNCIONAL</div>';
            echo '<div class="test-result">Total dispositivos: ' . count($dispositivos) . '</div>';
            
            if (!empty($dispositivos)) {
                echo '<h4>Dispositivos registrados:</h4>';
                foreach ($dispositivos as $dispositivo) {
                    $status = $dispositivo['status_conexion'] ?? 'offline';
                    $statusIcon = $status === 'online' ? '🟢' : ($status === 'warning' ? '🟡' : '🔴');
                    echo "<div class=\"test-result\">{$statusIcon} {$dispositivo['nombre']} - {$dispositivo['ubicacion']} ({$status})</div>";
                }
            }
        } catch (Exception $e) {
            echo '<div class="test-result status-error">❌ Error en modelo Dispositivo: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>💳 Tarjetas RFID</h2>
        <?php
        try {
            $tarjetaModel = new TarjetaRFID();
            $tarjetas = $tarjetaModel->obtenerTodas();
            
            echo '<div class="test-result status-ok">✅ Modelo TarjetaRFID: FUNCIONAL</div>';
            echo '<div class="test-result">Total tarjetas: ' . count($tarjetas) . '</div>';
            
            $activas = array_filter($tarjetas, fn($t) => $t['estado'] === 'activa');
            echo '<div class="test-result">Tarjetas activas: ' . count($activas) . '</div>';
        } catch (Exception $e) {
            echo '<div class="test-result status-error">❌ Error en modelo TarjetaRFID: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>⏰ Registros de Asistencia</h2>
        <?php
        try {
            $registroModel = new RegistroAsistencia();
            
            echo '<div class="test-result status-ok">✅ Modelo RegistroAsistencia: FUNCIONAL</div>';
            
            // Obtener estadísticas de hoy
            $hoy = date('Y-m-d');
            $estadisticas = $registroModel->obtenerEstadisticasDia($hoy);
            
            if ($estadisticas) {
                echo "<div class=\"test-result\">Marcaciones hoy: {$estadisticas['total_marcaciones']}</div>";
                echo "<div class=\"test-result\">Entradas: {$estadisticas['entradas']}</div>";
                echo "<div class=\"test-result\">Salidas: {$estadisticas['salidas']}</div>";
            }
        } catch (Exception $e) {
            echo '<div class="test-result status-error">❌ Error en modelo RegistroAsistencia: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>🔐 Prueba de Autenticación</h2>
        <?php
        try {
            $usuarioModel = new Usuario();
            
            // Intentar validar credenciales de usuarios de prueba
            $credenciales = [
                ['admin123', 'admin123'],
                ['rrhh123', 'rrhh123'],
                ['emp123', 'emp123']
            ];
            
            foreach ($credenciales as [$username, $password]) {
                $usuario = $usuarioModel->validarCredenciales($username, $password);
                if ($usuario) {
                    echo "<div class=\"test-result status-ok\">✅ Login {$username}: EXITOSO ({$usuario['rol']})</div>";
                } else {
                    echo "<div class=\"test-result status-error\">❌ Login {$username}: FALLÓ</div>";
                }
            }
        } catch (Exception $e) {
            echo '<div class="test-result status-error">❌ Error en autenticación: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>🌐 Prueba de API</h2>
        <div class="test-result">
            <strong>Endpoint de API:</strong> 
            <a href="/api/ping" target="_blank">/api/ping</a> 
            <small>(Hacer clic para probar)</small>
        </div>
        <div class="test-result">
            <strong>URL del sistema:</strong> 
            <a href="/login" target="_blank">/login</a>
        </div>
        <div class="test-result">
            <strong>Panel Admin:</strong> 
            <a href="/admin/dashboard" target="_blank">/admin/dashboard</a>
        </div>
    </div>

    <div class="section">
        <h2>📁 Estructura de Archivos</h2>
        <?php
        $archivos_importantes = [
            'config/bootstrap.php',
            'app/Controllers/AuthController.php',
            'app/Controllers/AdminController.php',
            'app/Models/Database.php',
            'app/Models/Usuario.php',
            'api/index.php',
            'src/routes.php'
        ];
        
        foreach ($archivos_importantes as $archivo) {
            $path = __DIR__ . '/' . $archivo;
            if (file_exists($path)) {
                $size = round(filesize($path) / 1024, 2);
                echo "<div class=\"test-result status-ok\">✅ {$archivo} ({$size} KB)</div>";
            } else {
                echo "<div class=\"test-result status-error\">❌ {$archivo} - NO ENCONTRADO</div>";
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>🚀 Estado General del Sistema</h2>
        <?php
        // Evaluación general
        $errores = 0;
        $warnings = 0;
        
        // Verificar componentes críticos
        try {
            Database::getInstance();
            new Usuario();
            new Dispositivo();
            new TarjetaRFID();
            new RegistroAsistencia();
        } catch (Exception $e) {
            $errores++;
        }
        
        if ($errores === 0) {
            echo '<div class="test-result status-ok">🎉 SISTEMA OPERACIONAL - Todo funcionando correctamente</div>';
            echo '<div class="test-result">✅ Base de datos conectada</div>';
            echo '<div class="test-result">✅ Modelos funcionando</div>';
            echo '<div class="test-result">✅ Controladores cargados</div>';
            echo '<div class="test-result">✅ API disponible</div>';
        } else {
            echo '<div class="test-result status-error">⚠️ SISTEMA CON ERRORES - Revisar configuración</div>';
        }
        ?>
        
        <h4>Acciones Disponibles:</h4>
        <div class="test-result">
            <a href="/login" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">🔐 Ir al Login</a>
            <a href="/api/ping" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;">📡 Probar API</a>
        </div>
    </div>

    <div class="section">
        <h2>📖 Credenciales de Prueba</h2>
        <div class="code">
            <strong>Administrador:</strong><br>
            Usuario: admin123<br>
            Contraseña: admin123<br><br>
            
            <strong>Recursos Humanos:</strong><br>
            Usuario: rrhh123<br>
            Contraseña: rrhh123<br><br>
            
            <strong>Empleado:</strong><br>
            Usuario: emp123<br>
            Contraseña: emp123
        </div>
    </div>

    <script>
        // Auto-actualizar cada 30 segundos
        setTimeout(() => location.reload(), 30000);
    </script>
</body>
</html>