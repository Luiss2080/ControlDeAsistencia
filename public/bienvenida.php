<?php
/**
 * Página de Bienvenida y Guía del Sistema
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏢 Sistema de Control de Asistencia - Bienvenida</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; color: white; margin-bottom: 30px; }
        .header h1 { font-size: 3em; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .header p { font-size: 1.2em; opacity: 0.9; }
        
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card h3 { color: #2c3e50; margin-bottom: 15px; font-size: 1.5em; }
        .card .credentials { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #3498db; }
        .card .credentials strong { color: #2c3e50; }
        
        .btn { display: inline-block; background: #3498db; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; margin: 5px; transition: all 0.3s; font-weight: bold; }
        .btn:hover { background: #2980b9; transform: translateY(-2px); }
        .btn.admin { background: #e74c3c; }
        .btn.admin:hover { background: #c0392b; }
        .btn.rrhh { background: #f39c12; }
        .btn.rrhh:hover { background: #d68910; }
        .btn.empleado { background: #27ae60; }
        .btn.empleado:hover { background: #229954; }
        
        .features { background: white; border-radius: 15px; padding: 30px; margin: 20px 0; }
        .features h2 { color: #2c3e50; margin-bottom: 20px; text-align: center; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .feature { text-align: center; padding: 20px; }
        .feature .icon { font-size: 3em; margin-bottom: 10px; }
        .feature h4 { color: #2c3e50; margin-bottom: 10px; }
        
        .quick-links { text-align: center; margin: 30px 0; }
        .system-info { background: rgba(255,255,255,0.1); border-radius: 10px; padding: 20px; margin: 20px 0; color: white; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 5px; margin: 5px; font-weight: bold; }
        .status.online { background: #27ae60; }
        .status.offline { background: #e74c3c; }
        
        @media (max-width: 768px) {
            .header h1 { font-size: 2em; }
            .cards { grid-template-columns: 1fr; }
            .container { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Sistema de Control de Asistencia</h1>
            <p>Gestión inteligente de asistencia con tecnología RFID y ESP32</p>
        </div>

        <div class="system-info">
            <h3>📊 Estado del Sistema</h3>
            <p>
                <span class="status online">✅ Apache Online</span>
                <span class="status online">✅ MySQL Online</span>
                <span class="status online">✅ PHP 8.1.25</span>
                <span class="status online">✅ Base de Datos Conectada</span>
            </p>
        </div>

        <div class="cards">
            <div class="card">
                <h3>👨‍💼 Panel Administrador</h3>
                <div class="credentials">
                    <strong>Email:</strong> admin@empresa.com<br>
                    <strong>Contraseña:</strong> admin123
                </div>
                <p><strong>Funciones:</strong></p>
                <ul>
                    <li>👥 Gestión completa de usuarios</li>
                    <li>📱 Configuración de dispositivos ESP32</li>
                    <li>🏷️ Administración de tarjetas RFID</li>
                    <li>📈 Reportes avanzados</li>
                    <li>⚙️ Configuración del sistema</li>
                </ul>
                <a href="index.php" class="btn admin">🚀 Acceder como Admin</a>
            </div>

            <div class="card">
                <h3>👩‍💼 Panel Recursos Humanos</h3>
                <div class="credentials">
                    <strong>Email:</strong> rrhh@empresa.com<br>
                    <strong>Contraseña:</strong> rrhh123
                </div>
                <p><strong>Funciones:</strong></p>
                <ul>
                    <li>📊 Reportes de asistencia detallados</li>
                    <li>📈 Estadísticas de puntualidad</li>
                    <li>👤 Seguimiento por empleado</li>
                    <li>📄 Exportación a PDF/Excel</li>
                    <li>🔍 Análisis de tendencias</li>
                </ul>
                <a href="index.php" class="btn rrhh">📊 Acceder como RRHH</a>
            </div>

            <div class="card">
                <h3>👨‍💻 Panel Empleado</h3>
                <div class="credentials">
                    <strong>Email:</strong> juan@empresa.com<br>
                    <strong>Contraseña:</strong> emp123
                </div>
                <p><strong>Funciones:</strong></p>
                <ul>
                    <li>🕐 Ver mi registro de asistencia</li>
                    <li>📅 Historial personal</li>
                    <li>⏱️ Horas trabajadas</li>
                    <li>📈 Estadísticas personales</li>
                    <li>🔔 Notificaciones</li>
                </ul>
                <a href="index.php" class="btn empleado">👤 Acceder como Empleado</a>
            </div>
        </div>

        <div class="features">
            <h2>🚀 Características del Sistema</h2>
            <div class="feature-grid">
                <div class="feature">
                    <div class="icon">📱</div>
                    <h4>Tecnología ESP32</h4>
                    <p>Lectores RFID conectados por WiFi para registro automático de asistencia</p>
                </div>
                <div class="feature">
                    <div class="icon">🏷️</div>
                    <h4>Tarjetas RFID</h4>
                    <p>Sistema de identificación seguro y rápido para cada empleado</p>
                </div>
                <div class="feature">
                    <div class="icon">📊</div>
                    <h4>Reportes Inteligentes</h4>
                    <p>Análisis detallado de puntualidad, ausencias y productividad</p>
                </div>
                <div class="feature">
                    <div class="icon">🔒</div>
                    <h4>Sistema Seguro</h4>
                    <p>Autenticación por roles y registro completo de actividades</p>
                </div>
                <div class="feature">
                    <div class="icon">⚡</div>
                    <h4>Tiempo Real</h4>
                    <p>Registro instantáneo y sincronización automática</p>
                </div>
                <div class="feature">
                    <div class="icon">📈</div>
                    <h4>Analítica</h4>
                    <p>Estadísticas avanzadas y tendencias de asistencia</p>
                </div>
            </div>
        </div>

        <div class="quick-links">
            <h3 style="color: white; margin-bottom: 20px;">🔗 Enlaces del Sistema</h3>
            <a href="index.php" class="btn">🏠 Acceder al Sistema</a>
            <a href="../scripts/install.php" class="btn">⚙️ Verificar Instalación</a>
            <a href="../tests/SystemTest.php" class="btn">🧪 Ejecutar Tests</a>
            <a href="../scripts/verificar_sistema.php" class="btn">� Verificación Completa</a>
        </div>

        <div class="system-info">
            <h3>💡 Primeros Pasos</h3>
            <ol style="text-align: left; max-width: 600px; margin: 0 auto;">
                <li><strong>Inicia como Administrador</strong> para configurar usuarios y dispositivos</li>
                <li><strong>Registra empleados</strong> en el panel de administración</li>
                <li><strong>Asigna tarjetas RFID</strong> a cada empleado</li>
                <li><strong>Configura dispositivos ESP32</strong> en las ubicaciones de acceso</li>
                <li><strong>¡Comienza a registrar asistencia!</strong> con las tarjetas RFID</li>
            </ol>
        </div>
    </div>
</body>
</html>