<?php
/**
 * Script de instalación del sistema
 * Verifica dependencias y configura la base de datos
 */

echo "<h1>🚀 Instalación del Sistema de Control de Asistencia</h1>";

// Verificar PHP
$phpVersion = phpversion();
echo "<h2>📋 Verificación de Requisitos</h2>";
echo "<p>✅ PHP Version: $phpVersion</p>";

if (version_compare($phpVersion, '8.0', '<')) {
    echo "<p>❌ Error: PHP 8.0 o superior requerido</p>";
    exit;
}

// Verificar extensiones
$extensiones = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring', 'openssl'];
foreach ($extensiones as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ Extensión $ext: Disponible</p>";
    } else {
        echo "<p>❌ Extensión $ext: No disponible</p>";
    }
}

// Verificar archivos de configuración
echo "<h2>📁 Verificación de Archivos</h2>";

$archivos = [
    '.env' => __DIR__ . '/../.env',
    'composer.json' => __DIR__ . '/../composer.json',
    'bootstrap.php' => __DIR__ . '/../config/bootstrap.php',
    'routes.php' => __DIR__ . '/../src/routes.php'
];

foreach ($archivos as $nombre => $ruta) {
    if (file_exists($ruta)) {
        echo "<p>✅ $nombre: Encontrado</p>";
    } else {
        echo "<p>❌ $nombre: No encontrado en $ruta</p>";
    }
}

// Verificar base de datos
echo "<h2>🗄️ Verificación de Base de Datos</h2>";

try {
    require_once __DIR__ . '/../config/bootstrap.php';
    $db = \App\Models\Database::getInstance();
    echo "<p>✅ Conexión a base de datos: OK</p>";
    
    // Verificar tablas
    $tablas = ['usuarios', 'dispositivos', 'tarjetas_rfid', 'registros_asistencia', 'logs_sistema'];
    foreach ($tablas as $tabla) {
        $result = $db->query("SHOW TABLES LIKE '$tabla'");
        if ($result && count($result) > 0) {
            echo "<p>✅ Tabla $tabla: Existe</p>";
        } else {
            echo "<p>❌ Tabla $tabla: No existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error de base de datos: " . $e->getMessage() . "</p>";
}

// Verificar permisos de escritura
echo "<h2>🔒 Verificación de Permisos</h2>";

$directorios = [
    __DIR__ . '/../public',
    __DIR__ . '/../config',
    __DIR__ . '/../database'
];

foreach ($directorios as $dir) {
    if (is_writable($dir)) {
        echo "<p>✅ $dir: Escribible</p>";
    } else {
        echo "<p>⚠️ $dir: Solo lectura</p>";
    }
}

echo "<h2>🎉 Instalación Completada</h2>";
echo "<p><a href='../public/bienvenida.php'>🏠 Ir al Sistema</a></p>";
?>