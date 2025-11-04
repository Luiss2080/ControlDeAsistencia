<?php
/**
 * Script de migración para corregir la estructura de la base de datos
 * Sistema de Control de Asistencia
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Incluir configuración
    require_once __DIR__ . '/config/database.php';
    $config = require __DIR__ . '/config/database.php';
    
    // Conectar a la base de datos
    $db = $config['database'];
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);
    
    echo "<h1>🔧 Migración de Base de Datos</h1>";
    echo "<p>Verificando y corrigiendo la estructura de la base de datos...</p>";
    
    $migraciones = [];
    
    // 1. Verificar si existe la columna activo en usuarios
    echo "<h2>1. Verificando tabla usuarios</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'activo'");
    if (!$stmt->fetch()) {
        echo "❌ Columna 'activo' no existe en usuarios. Agregando...<br>";
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN activo TINYINT(1) DEFAULT 1");
        echo "✅ Columna 'activo' agregada a usuarios<br>";
        $migraciones[] = "Agregada columna 'activo' a usuarios";
    } else {
        echo "✅ Columna 'activo' existe en usuarios<br>";
    }
    
    // 2. Verificar estructura de dispositivos
    echo "<h2>2. Verificando tabla dispositivos</h2>";
    $columnas_dispositivos = ['ultimo_ping', 'ip_address'];
    foreach ($columnas_dispositivos as $columna) {
        $stmt = $pdo->query("SHOW COLUMNS FROM dispositivos LIKE '$columna'");
        if (!$stmt->fetch()) {
            echo "❌ Columna '$columna' no existe en dispositivos. Agregando...<br>";
            if ($columna === 'ultimo_ping') {
                $pdo->exec("ALTER TABLE dispositivos ADD COLUMN ultimo_ping TIMESTAMP NULL DEFAULT NULL");
            } elseif ($columna === 'ip_address') {
                $pdo->exec("ALTER TABLE dispositivos ADD COLUMN ip_address VARCHAR(15) DEFAULT NULL");
            }
            echo "✅ Columna '$columna' agregada a dispositivos<br>";
            $migraciones[] = "Agregada columna '$columna' a dispositivos";
        } else {
            echo "✅ Columna '$columna' existe en dispositivos<br>";
        }
    }
    
    // 3. Verificar tabla tarjetas_rfid
    echo "<h2>3. Verificando tabla tarjetas_rfid</h2>";
    $stmt = $pdo->query("SHOW COLUMNS FROM tarjetas_rfid LIKE 'fecha_asignacion'");
    if (!$stmt->fetch()) {
        echo "❌ Columna 'fecha_asignacion' no existe en tarjetas_rfid. Agregando...<br>";
        $pdo->exec("ALTER TABLE tarjetas_rfid ADD COLUMN fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "✅ Columna 'fecha_asignacion' agregada a tarjetas_rfid<br>";
        $migraciones[] = "Agregada columna 'fecha_asignacion' a tarjetas_rfid";
    } else {
        echo "✅ Columna 'fecha_asignacion' existe en tarjetas_rfid<br>";
    }
    
    // 4. Verificar tabla configuracion_sistema
    echo "<h2>4. Verificando tabla configuracion_sistema</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'configuracion_sistema'");
    if (!$stmt->fetch()) {
        echo "❌ Tabla 'configuracion_sistema' no existe. Creando...<br>";
        $pdo->exec("
            CREATE TABLE configuracion_sistema (
                id INT AUTO_INCREMENT PRIMARY KEY,
                clave VARCHAR(100) UNIQUE NOT NULL,
                valor TEXT,
                descripcion TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ Tabla 'configuracion_sistema' creada<br>";
        $migraciones[] = "Creada tabla 'configuracion_sistema'";
        
        // Insertar configuraciones por defecto
        $configuraciones_default = [
            ['minutos_entre_marcaciones', '5', 'Minutos mínimos entre marcaciones'],
            ['hora_entrada_standard', '08:00', 'Hora de entrada estándar'],
            ['hora_salida_standard', '17:00', 'Hora de salida estándar'],
            ['tolerancia_tardanza', '10', 'Minutos de tolerancia para tardanzas']
        ];
        
        foreach ($configuraciones_default as $config) {
            $pdo->prepare("INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES (?, ?, ?)")->execute($config);
        }
        echo "✅ Configuraciones por defecto insertadas<br>";
    } else {
        echo "✅ Tabla 'configuracion_sistema' existe<br>";
    }
    
    // 5. Actualizar datos existentes
    echo "<h2>5. Actualizando datos existentes</h2>";
    
    // Actualizar usuarios sin columna activo
    $pdo->exec("UPDATE usuarios SET activo = 1 WHERE activo IS NULL");
    echo "✅ Usuarios actualizados con estado activo<br>";
    
    // Verificar si hay tarjetas sin fecha_asignacion
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tarjetas_rfid WHERE fecha_asignacion IS NULL");
    $result = $stmt->fetch();
    if ($result['total'] > 0) {
        $pdo->exec("UPDATE tarjetas_rfid SET fecha_asignacion = created_at WHERE fecha_asignacion IS NULL");
        echo "✅ Fechas de asignación actualizadas en tarjetas RFID<br>";
    }
    
    echo "<h2>✅ Migración completada exitosamente</h2>";
    
    if (!empty($migraciones)) {
        echo "<h3>📋 Cambios realizados:</h3>";
        echo "<ul>";
        foreach ($migraciones as $migracion) {
            echo "<li>$migracion</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>✅ No se requirieron cambios en la base de datos.</p>";
    }
    
    echo "<p><strong>🎉 La base de datos está ahora actualizada y lista para usar.</strong></p>";
    echo "<p><a href='/ControlDeAsistencia/'>← Volver al sistema</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Error durante la migración</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Por favor, verifica la conexión a la base de datos y los permisos.</p>";
} catch (Exception $e) {
    echo "<h2>❌ Error inesperado</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>