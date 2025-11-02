<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Sistema de Asistencia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { max-width: 500px; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center; }
        h1 { color: #e74c3c; font-size: 3em; margin-bottom: 20px; }
        p { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .btn { display: inline-block; background: #3498db; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; margin: 5px; transition: all 0.3s; font-weight: bold; }
        .btn:hover { background: #2980b9; transform: translateY(-2px); }
        .error-code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️</h1>
        <h2>Error del Sistema</h2>
        <p>Ha ocurrido un error inesperado. Nuestro equipo técnico ha sido notificado.</p>
        <div class="error-code">Error ID: <?php echo date('YmdHis') . rand(100, 999); ?></div>
        <a href="/" class="btn">🏠 Volver al Inicio</a>
        <a href="/login" class="btn">🔐 Iniciar Sesión</a>
    </div>
</body>
</html>