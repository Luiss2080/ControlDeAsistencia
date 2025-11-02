<?php
/**
 * Configuración General del Sistema
 * Sistema de Control de Asistencia
 */

return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Sistema de Control de Asistencia',
        'version' => '1.0.0',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'env' => $_ENV['APP_ENV'] ?? 'production'
    ],
    
    'security' => [
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'su_clave_jwt_muy_segura_aqui',
        'jwt_expiration' => 3600, // 1 hora
        'session_lifetime' => 7200, // 2 horas
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
        'csrf_protection' => true
    ],
    
    'api' => [
        'rate_limit' => 100, // peticiones por minuto
        'rate_limit_window' => 60, // ventana en segundos
        'cors_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'api_version' => 'v1'
    ],
    
    'asistencia' => [
        'tolerancia_entrada' => 15, // minutos
        'tolerancia_salida' => 30, // minutos
        'minimo_entre_marcaciones' => 5, // minutos
        'horario_default' => [
            'entrada' => '08:00:00',
            'salida' => '17:00:00'
        ],
        'dias_laborables' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
    ],
    
    'email' => [
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
        'port' => $_ENV['MAIL_PORT'] ?? 587,
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@empresa.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Sistema de Asistencia'
    ],
    
    'storage' => [
        'uploads_path' => 'uploads/',
        'max_file_size' => 5 * 1024 * 1024, // 5MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
        'backup_path' => 'backups/',
        'logs_path' => 'logs/'
    ],
    
    'reports' => [
        'pdf_engine' => 'mpdf',
        'excel_engine' => 'phpspreadsheet',
        'max_records_excel' => 10000,
        'cache_reports' => true,
        'cache_duration' => 300 // 5 minutos
    ],
    
    'notifications' => [
        'tardanza_threshold' => 15, // minutos
        'ausencia_notification' => true,
        'email_on_tardanza' => false,
        'email_supervisores' => true
    ]
];