<?php
/**
 * Archivo de configuración principal
 * Sistema de Control de Asistencia
 */

// Cargar variables de entorno
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Configuración de errores
if ($_ENV['APP_DEBUG'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de zona horaria
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

// Autoloader simple
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    // Buscar las clases a partir de la raíz del proyecto (config/..)
    $projectRoot = dirname(__DIR__);
    $file = $projectRoot . DIRECTORY_SEPARATOR . str_replace('App' . DIRECTORY_SEPARATOR, 'app' . DIRECTORY_SEPARATOR, $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Inicializar sesión
session_start();

// Función helper para redireccionar
function redirect($url, $permanent = false) {
    $code = $permanent ? 301 : 302;
    header("Location: $url", true, $code);
    exit();
}

// Función helper para obtener URL base
function baseUrl($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . $host;
    
    return $baseUrl . '/' . ltrim($path, '/');
}

// Función helper para assets
function asset($path) {
    return baseUrl('public/' . ltrim($path, '/'));
}

// Función helper para CSRF token
function csrfToken() {
    return \App\Utils\Auth::generarTokenCSRF();
}

// Función helper para verificar autenticación
function auth() {
    return \App\Utils\Auth::usuario();
}

// Función helper para verificar permisos
function can($permission) {
    return \App\Utils\Auth::tienePermiso($permission);
}

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Inicializar autenticación
\App\Utils\Auth::iniciar();