<?php
/**
 * Script de limpieza del sistema
 * Elimina archivos temporales, logs y cachés
 */

echo "🧹 Iniciando limpieza del sistema...\n";

// Directorios a limpiar
$directoriosLimpieza = [
    __DIR__ . '/../app/cache/',
    __DIR__ . '/../app/logs/',
    __DIR__ . '/../public/uploads/temp/',
    __DIR__ . '/../storage/temp/'
];

// Extensiones de archivos a eliminar
$extensionesTemporales = ['.log', '.tmp', '.cache', '.bak', '~'];

$archivosEliminados = 0;

foreach ($directoriosLimpieza as $directorio) {
    if (is_dir($directorio)) {
        echo "📁 Limpiando: $directorio\n";
        
        $archivos = glob($directorio . '*');
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                $extension = '.' . pathinfo($archivo, PATHINFO_EXTENSION);
                
                // Eliminar archivos temporales
                if (in_array($extension, $extensionesTemporales)) {
                    unlink($archivo);
                    $archivosEliminados++;
                    echo "  ❌ Eliminado: " . basename($archivo) . "\n";
                }
                
                // Eliminar archivos antiguos (más de 7 días)
                if (filemtime($archivo) < strtotime('-7 days')) {
                    unlink($archivo);
                    $archivosEliminados++;
                    echo "  🗑️ Eliminado (antiguo): " . basename($archivo) . "\n";
                }
            }
        }
    }
}

// Limpiar sesiones PHP expiradas
if (ini_get('session.save_path')) {
    $sesionPath = ini_get('session.save_path');
    if (is_dir($sesionPath)) {
        echo "🔐 Limpiando sesiones expiradas...\n";
        $sesiones = glob($sesionPath . '/sess_*');
        foreach ($sesiones as $sesion) {
            if (filemtime($sesion) < strtotime('-1 hour')) {
                unlink($sesion);
                $archivosEliminados++;
            }
        }
    }
}

echo "\n✅ Limpieza completada!\n";
echo "📊 Archivos eliminados: $archivosEliminados\n";
echo "💾 Espacio liberado aproximado: " . ($archivosEliminados * 5) . " KB\n";