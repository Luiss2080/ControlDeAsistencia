<?php
/**
 * Controlador de Administración
 * Sistema de Control de Asistencia
 */

namespace App\Controllers;

use App\Models\Database;

class AdminController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar autenticación y rol de administrador
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
            header('Location: /ControlDeAsistencia/?error=' . urlencode('Acceso denegado'));
            exit;
        }
    }

    /**
     * Dashboard principal de administración
     */
    public function dashboard() {
        // Obtener estadísticas del sistema
        $stats = $this->obtenerEstadisticas();
        
        // Obtener actividad reciente
        $actividad_reciente = $this->obtenerActividadReciente();
        
        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    /**
     * Gestión de usuarios
     */
    public function usuarios() {
        $filtros = [
            'search' => $_GET['search'] ?? '',
            'rol' => $_GET['rol'] ?? ''
        ];
        
        $usuarios = $this->buscarUsuarios($filtros);
        
        include __DIR__ . '/../Views/admin/usuarios.php';
    }

    /**
     * Crear nuevo usuario
     */
    public function crearUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = $this->validarDatosUsuario($_POST);
                
                // Verificar que el email no exista
                $existeEmail = $this->db->fetch("SELECT id FROM usuarios WHERE email = ?", [$datos['email']]);
                if ($existeEmail) {
                    throw new \Exception('El email ya está registrado');
                }
                
                // Verificar que el número de empleado no exista
                $existeNumero = $this->db->fetch("SELECT id FROM usuarios WHERE numero_empleado = ?", [$datos['numero_empleado']]);
                if ($existeNumero) {
                    throw new \Exception('El número de empleado ya está registrado');
                }
                
                // Encriptar contraseña
                $datos['password_hash'] = password_hash($datos['password'], PASSWORD_DEFAULT);
                unset($datos['password']);
                
                // Crear usuario
                $usuario_id = $this->db->insert('usuarios', $datos);
                
                if ($usuario_id) {
                    $_SESSION['mensaje'] = 'Usuario creado exitosamente';
                    header('Location: /ControlDeAsistencia/admin/usuarios');
                    exit;
                } else {
                    throw new \Exception('Error al crear el usuario');
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        include __DIR__ . '/../Views/admin/crear_usuario.php';
    }

    /**
     * Gestión de dispositivos ESP32
     */
    public function dispositivos() {
        $dispositivos = $this->db->fetchAll("
            SELECT d.*, 
                   COUNT(a.id) as total_registros,
                   MAX(a.fecha_hora) as ultimo_registro
            FROM dispositivos d
            LEFT JOIN asistencias a ON d.id = a.dispositivo_id
            WHERE d.estado = 'activo'
            GROUP BY d.id
            ORDER BY d.nombre
        ");
        
        include __DIR__ . '/../Views/admin/dispositivos.php';
    }

    /**
     * Crear nuevo dispositivo
     */
    public function crearDispositivo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = [
                    'nombre' => trim($_POST['nombre']),
                    'token_dispositivo' => bin2hex(random_bytes(32)),
                    'ubicacion' => trim($_POST['ubicacion']),
                    'estado' => 'activo'
                ];
                
                if (empty($datos['nombre']) || empty($datos['ubicacion'])) {
                    throw new \Exception('Nombre y ubicación son obligatorios');
                }
                
                $dispositivo_id = $this->db->insert('dispositivos', $datos);
                
                if ($dispositivo_id) {
                    $_SESSION['mensaje'] = 'Dispositivo creado exitosamente. Token: ' . $datos['token_dispositivo'];
                } else {
                    throw new \Exception('Error al crear el dispositivo');
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        header('Location: /ControlDeAsistencia/admin/dispositivos');
        exit;
    }

    /**
     * Gestión de tarjetas RFID
     */
    public function tarjetas() {
        $tarjetas = $this->db->fetchAll("
            SELECT t.*, 
                   CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
                   u.numero_empleado
            FROM tarjetas_rfid t
            LEFT JOIN usuarios u ON t.usuario_id = u.id
            ORDER BY t.fecha_asignacion DESC
        ");
        
        $usuarios_sin_tarjeta = $this->db->fetchAll("
            SELECT u.id, u.nombres, u.apellidos, u.numero_empleado
            FROM usuarios u
            LEFT JOIN tarjetas_rfid t ON u.id = t.usuario_id AND t.estado = 'activa'
            WHERE u.activo = 1 AND t.id IS NULL
            ORDER BY u.apellidos, u.nombres
        ");
        
        include __DIR__ . '/../Views/admin/tarjetas.php';
    }

    /**
     * Asignar tarjeta RFID a usuario
     */
    public function asignarTarjeta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = [
                    'uid_tarjeta' => strtoupper(trim($_POST['uid_tarjeta'])),
                    'usuario_id' => (int)$_POST['usuario_id'],
                    'estado' => 'activa'
                ];
                
                if (empty($datos['uid_tarjeta']) || empty($datos['usuario_id'])) {
                    throw new \Exception('UID de tarjeta y usuario son obligatorios');
                }
                
                // Verificar que el UID no exista
                $tarjeta_existente = $this->db->fetch("SELECT id FROM tarjetas_rfid WHERE uid_tarjeta = ?", [$datos['uid_tarjeta']]);
                if ($tarjeta_existente) {
                    throw new \Exception('El UID de la tarjeta ya está registrado');
                }
                
                // Verificar que el usuario no tenga otra tarjeta activa
                $tarjeta_usuario = $this->db->fetch("SELECT id FROM tarjetas_rfid WHERE usuario_id = ? AND estado = 'activa'", [$datos['usuario_id']]);
                if ($tarjeta_usuario) {
                    throw new \Exception('El usuario ya tiene una tarjeta activa asignada');
                }
                
                $tarjeta_id = $this->db->insert('tarjetas_rfid', $datos);
                
                if ($tarjeta_id) {
                    $_SESSION['mensaje'] = 'Tarjeta RFID asignada exitosamente';
                } else {
                    throw new \Exception('Error al asignar la tarjeta RFID');
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        header('Location: /ControlDeAsistencia/admin/tarjetas');
        exit;
    }

    /**
     * Reportes administrativos
     */
    public function reportes() {
        $filtros = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'usuario' => $_GET['usuario'] ?? ''
        ];
        
        $reporte_data = $this->generarDatosReporte($filtros);
        $usuarios = $this->db->fetchAll("SELECT id, nombres, apellidos, numero_empleado FROM usuarios WHERE activo = 1 ORDER BY apellidos, nombres");
        
        include __DIR__ . '/../Views/admin/reportes.php';
    }

    /**
     * Configuración del sistema
     */
    public function configuracion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $configuraciones = $_POST['config'] ?? [];
                
                foreach ($configuraciones as $clave => $valor) {
                    $this->db->query("
                        INSERT INTO configuracion_sistema (clave, valor, updated_at) 
                        VALUES (?, ?, NOW())
                        ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_at = NOW()
                    ", [$clave, $valor]);
                }
                
                $_SESSION['mensaje'] = 'Configuración actualizada exitosamente';
                
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error al actualizar la configuración: ' . $e->getMessage();
            }
        }
        
        // Obtener configuraciones actuales
        $configuraciones = [];
        $configs = $this->db->fetchAll("SELECT clave, valor FROM configuracion_sistema");
        foreach ($configs as $config) {
            $configuraciones[$config['clave']] = $config['valor'];
        }
        
        include __DIR__ . '/../Views/admin/configuracion.php';
    }

    /**
     * Métodos auxiliares privados
     */
    private function obtenerEstadisticas() {
        $stats = [];
        
        // Usuarios activos
        $stats['total_usuarios'] = $this->db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1")['total'];
        
        // Dispositivos activos
        $stats['dispositivos_activos'] = $this->db->fetch("SELECT COUNT(*) as total FROM dispositivos WHERE estado = 'activo'")['total'];
        
        // Tarjetas asignadas
        $stats['tarjetas_activas'] = $this->db->fetch("SELECT COUNT(*) as total FROM tarjetas_rfid WHERE estado = 'activa' AND usuario_id IS NOT NULL")['total'];
        
        // Registros de hoy
        $stats['marcaciones_hoy'] = $this->db->fetch("SELECT COUNT(*) as total FROM asistencias WHERE DATE(fecha_hora) = CURDATE()")['total'];
        
        return $stats;
    }
    
    private function obtenerActividadReciente() {
        return $this->db->fetchAll("
            SELECT 
                CONCAT(u.nombres, ' ', u.apellidos) as empleado,
                a.tipo,
                a.fecha_hora,
                d.nombre as dispositivo
            FROM asistencias a
            JOIN usuarios u ON a.usuario_id = u.id
            JOIN dispositivos d ON a.dispositivo_id = d.id
            ORDER BY a.fecha_hora DESC
            LIMIT 10
        ");
    }
    
    private function validarDatosUsuario($datos) {
        $errores = [];
        
        if (empty(trim($datos['nombres']))) {
            $errores[] = 'El nombre es obligatorio';
        }
        
        if (empty(trim($datos['apellidos']))) {
            $errores[] = 'Los apellidos son obligatorios';
        }
        
        if (empty(trim($datos['email'])) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email es obligatorio y debe ser válido';
        }
        
        if (empty(trim($datos['numero_empleado']))) {
            $errores[] = 'El número de empleado es obligatorio';
        }
        
        if (empty($datos['password']) || strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña es obligatoria y debe tener al menos 6 caracteres';
        }
        
        if (!empty($errores)) {
            throw new \Exception(implode(', ', $errores));
        }
        
        return [
            'nombres' => trim($datos['nombres']),
            'apellidos' => trim($datos['apellidos']),
            'email' => trim(strtolower($datos['email'])),
            'numero_empleado' => trim($datos['numero_empleado']),
            'telefono' => trim($datos['telefono'] ?? ''),
            'puesto' => trim($datos['puesto'] ?? ''),
            'rol' => $datos['rol'] ?? 'empleado',
            'fecha_ingreso' => !empty($datos['fecha_ingreso']) ? $datos['fecha_ingreso'] : date('Y-m-d'),
            'password' => $datos['password']
        ];
    }
    
    private function buscarUsuarios($filtros) {
        $sql = "SELECT * FROM usuarios WHERE activo = 1";
        $params = [];
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (nombres LIKE ? OR apellidos LIKE ? OR numero_empleado LIKE ? OR email LIKE ?)";
            $search = '%' . $filtros['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }
        
        if (!empty($filtros['rol'])) {
            $sql .= " AND rol = ?";
            $params[] = $filtros['rol'];
        }
        
        $sql .= " ORDER BY apellidos, nombres";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function generarDatosReporte($filtros) {
        $sql = "
            SELECT 
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
                u.puesto,
                DATE(a.fecha_hora) as fecha,
                MIN(CASE WHEN a.tipo = 'entrada' THEN a.fecha_hora END) as primera_entrada,
                MAX(CASE WHEN a.tipo = 'salida' THEN a.fecha_hora END) as ultima_salida,
                COUNT(CASE WHEN a.tipo = 'entrada' THEN 1 END) as total_entradas,
                COUNT(CASE WHEN a.tipo = 'salida' THEN 1 END) as total_salidas,
                SUM(a.es_tardanza) as tardanzas
            FROM usuarios u
            LEFT JOIN asistencias a ON u.id = a.usuario_id
            WHERE u.activo = 1
        ";
        
        $params = [];
        
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(a.fecha_hora) BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicio'];
            $params[] = $filtros['fecha_fin'];
        }
        
        if (!empty($filtros['usuario'])) {
            $sql .= " AND u.id = ?";
            $params[] = $filtros['usuario'];
        }
        
        $sql .= " GROUP BY u.id, DATE(a.fecha_hora) ORDER BY u.apellidos, u.nombres, fecha DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
}
?>

    /**
     * Gestión de usuarios
     */
    public function usuarios() {
        $filtros = [
            'search' => $_GET['search'] ?? '',
            'departamento' => $_GET['departamento'] ?? '',
            'rol' => $_GET['rol'] ?? ''
        ];
        
        // Obtener usuarios con filtros
        $usuarios = $this->buscarUsuarios($filtros);
        
        // Obtener departamentos para filtros
        $departamentos = $this->db->fetchAll("SELECT * FROM departamentos WHERE activo = 1 ORDER BY nombre");
        
        $data = [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'departamentos' => $departamentos,
            'filtros' => $filtros
        ];
        
        include __DIR__ . '/../Views/admin/usuarios.php';
    }

    /**
     * Crear nuevo usuario
     */
    public function crearUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos
                $datos = $this->validarDatosUsuario($_POST);
                
                // Verificar que el email no exista
                if ($this->usuarioModel->emailExists($datos['email'])) {
                    throw new \Exception('El email ya está registrado');
                }
                
                // Verificar que el número de empleado no exista
                if ($this->usuarioModel->numeroEmpleadoExists($datos['numero_empleado'])) {
                    throw new \Exception('El número de empleado ya está registrado');
                }
                
                // Crear usuario
                $usuario_id = $this->usuarioModel->create($datos);
                
                if ($usuario_id) {
                    // Log de actividad
                    $this->registrarLog('CREATE', 'usuarios', $usuario_id, 'Usuario creado: ' . $datos['nombres'] . ' ' . $datos['apellidos']);
                    
                    $_SESSION['mensaje'] = 'Usuario creado exitosamente';
                    header('Location: /admin/usuarios');
                    exit;
                } else {
                    throw new \Exception('Error al crear el usuario');
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: /admin/usuarios');
                exit;
            }
        }
        
        // Mostrar formulario de creación
        $departamentos = $this->db->fetchAll("SELECT * FROM departamentos WHERE activo = 1 ORDER BY nombre");
        $horarios = $this->db->fetchAll("SELECT * FROM horarios_trabajo WHERE activo = 1 ORDER BY nombre");
        
        $data = [
            'title' => 'Crear Usuario',
            'departamentos' => $departamentos,
            'horarios' => $horarios
        ];
        
        include __DIR__ . '/../Views/admin/crear_usuario.php';
    }

    /**
     * Editar usuario existente
     */
    public function editarUsuario($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no válido';
            header('Location: /admin/usuarios');
            exit;
        }
        
        // Obtener usuario
        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: /admin/usuarios');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos
                $datos = $this->validarDatosUsuario($_POST, $id);
                
                // Verificar email único
                if ($this->usuarioModel->emailExists($datos['email'], $id)) {
                    throw new \Exception('El email ya está registrado');
                }
                
                // Verificar número de empleado único
                if ($this->usuarioModel->numeroEmpleadoExists($datos['numero_empleado'], $id)) {
                    throw new \Exception('El número de empleado ya está registrado');
                }
                
                // Actualizar usuario
                if ($this->usuarioModel->updateUser($id, $datos)) {
                    $this->registrarLog('UPDATE', 'usuarios', $id, 'Usuario actualizado: ' . $datos['nombres'] . ' ' . $datos['apellidos']);
                    
                    $_SESSION['mensaje'] = 'Usuario actualizado exitosamente';
                    header('Location: /admin/usuarios');
                    exit;
                } else {
                    throw new \Exception('Error al actualizar el usuario');
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        $departamentos = $this->db->fetchAll("SELECT * FROM departamentos WHERE activo = 1 ORDER BY nombre");
        $horarios = $this->db->fetchAll("SELECT * FROM horarios_trabajo WHERE activo = 1 ORDER BY nombre");
        
        $data = [
            'title' => 'Editar Usuario',
            'usuario' => $usuario,
            'departamentos' => $departamentos,
            'horarios' => $horarios
        ];
        
        include __DIR__ . '/../Views/admin/editar_usuario.php';
    }

    /**
     * Eliminar usuario
     */
    public function eliminarUsuario($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no válido';
            header('Location: /admin/usuarios');
            exit;
        }
        
        try {
            $usuario = $this->usuarioModel->findById($id);
            if (!$usuario) {
                throw new \Exception('Usuario no encontrado');
            }
            
            // No permitir eliminar al propio usuario admin
            if ($id == $_SESSION['usuario_id']) {
                throw new \Exception('No puedes eliminar tu propia cuenta');
            }
            
            // Desactivar en lugar de eliminar
            if ($this->usuarioModel->deactivate($id)) {
                $this->registrarLog('DELETE', 'usuarios', $id, 'Usuario desactivado: ' . $usuario['nombres'] . ' ' . $usuario['apellidos']);
                
                $_SESSION['mensaje'] = 'Usuario desactivado exitosamente';
            } else {
                throw new \Exception('Error al desactivar el usuario');
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: /admin/usuarios');
        exit;
    }

    /**
     * Gestión de dispositivos ESP32
     */
    public function dispositivos() {
        $dispositivos = $this->db->fetchAll("
            SELECT d.*, 
                   COUNT(ra.id) as total_registros,
                   MAX(ra.fecha_hora) as ultimo_registro
            FROM dispositivos d
            LEFT JOIN registros_asistencia ra ON d.id = ra.dispositivo_id
            WHERE d.activo = 1
            GROUP BY d.id
            ORDER BY d.nombre
        ");
        
        $data = [
            'title' => 'Dispositivos ESP32',
            'dispositivos' => $dispositivos
        ];
        
        include __DIR__ . '/../Views/admin/dispositivos.php';
    }

    /**
     * Crear nuevo dispositivo
     */
    public function crearDispositivo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = [
                    'nombre' => trim($_POST['nombre']),
                    'token' => bin2hex(random_bytes(32)),
                    'ubicacion' => trim($_POST['ubicacion']),
                    'configuracion' => json_encode([
                        'led_enable' => isset($_POST['led_enable']),
                        'buzzer_enable' => isset($_POST['buzzer_enable']),
                        'debug' => isset($_POST['debug'])
                    ])
                ];
                
                if (empty($datos['nombre']) || empty($datos['ubicacion'])) {
                    throw new \Exception('Nombre y ubicación son obligatorios');
                }
                
                $dispositivo_id = $this->db->insert('dispositivos', $datos);
                
                if ($dispositivo_id) {
                    $this->registrarLog('CREATE', 'dispositivos', $dispositivo_id, 'Dispositivo creado: ' . $datos['nombre']);
                    
                    $_SESSION['mensaje'] = 'Dispositivo creado exitosamente';
                } else {
                    throw new \Exception('Error al crear el dispositivo');
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        header('Location: /admin/dispositivos');
        exit;
    }

    /**
     * Eliminar dispositivo
     */
    public function eliminarDispositivo($token = null) {
        if (!$token) {
            $_SESSION['error'] = 'Token de dispositivo no válido';
            header('Location: /admin/dispositivos');
            exit;
        }
        
        try {
            $dispositivo = $this->db->fetch("SELECT * FROM dispositivos WHERE token = ?", [$token]);
            if (!$dispositivo) {
                throw new \Exception('Dispositivo no encontrado');
            }
            
            // Desactivar dispositivo
            if ($this->db->update('dispositivos', ['activo' => 0], 'token = ?', [$token])) {
                $this->registrarLog('DELETE', 'dispositivos', $dispositivo['id'], 'Dispositivo desactivado: ' . $dispositivo['nombre']);
                
                $_SESSION['mensaje'] = 'Dispositivo desactivado exitosamente';
            } else {
                throw new \Exception('Error al desactivar el dispositivo');
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: /admin/dispositivos');
        exit;
    }

    /**
     * Gestión de tarjetas RFID
     */
    public function tarjetas() {
        $tarjetas = $this->db->fetchAll("
            SELECT t.*, u.nombres, u.apellidos, u.numero_empleado
            FROM tarjetas_rfid t
            LEFT JOIN usuarios u ON t.usuario_id = u.id
            ORDER BY t.created_at DESC
        ");
        
        $usuarios_sin_tarjeta = $this->db->fetchAll("
            SELECT u.id, u.nombres, u.apellidos, u.numero_empleado
            FROM usuarios u
            LEFT JOIN tarjetas_rfid t ON u.id = t.usuario_id AND t.estado = 'activa'
            WHERE u.activo = 1 AND t.id IS NULL
            ORDER BY u.apellidos, u.nombres
        ");
        
        $data = [
            'title' => 'Tarjetas RFID',
            'tarjetas' => $tarjetas,
            'usuarios_sin_tarjeta' => $usuarios_sin_tarjeta
        ];
        
        include __DIR__ . '/../Views/admin/tarjetas.php';
    }

    /**
     * Crear nueva tarjeta RFID
     */
    public function crearTarjeta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datos = [
                    'uid_tarjeta' => strtoupper(trim($_POST['uid_tarjeta'])),
                    'usuario_id' => (int)$_POST['usuario_id'],
                    'estado' => 'activa',
                    'fecha_asignacion' => date('Y-m-d H:i:s'),
                    'observaciones' => trim($_POST['observaciones'])
                ];
                
                if (empty($datos['uid_tarjeta']) || empty($datos['usuario_id'])) {
                    throw new \Exception('UID de tarjeta y usuario son obligatorios');
                }
                
                // Verificar que el UID no exista
                $tarjeta_existente = $this->db->fetch("SELECT id FROM tarjetas_rfid WHERE uid_tarjeta = ?", [$datos['uid_tarjeta']]);
                if ($tarjeta_existente) {
                    throw new \Exception('El UID de la tarjeta ya está registrado');
                }
                
                // Verificar que el usuario no tenga otra tarjeta activa
                $tarjeta_usuario = $this->db->fetch("SELECT id FROM tarjetas_rfid WHERE usuario_id = ? AND estado = 'activa'", [$datos['usuario_id']]);
                if ($tarjeta_usuario) {
                    throw new \Exception('El usuario ya tiene una tarjeta activa asignada');
                }
                
                $tarjeta_id = $this->db->insert('tarjetas_rfid', $datos);
                
                if ($tarjeta_id) {
                    $this->registrarLog('CREATE', 'tarjetas_rfid', $tarjeta_id, 'Tarjeta RFID creada: ' . $datos['uid_tarjeta']);
                    
                    $_SESSION['mensaje'] = 'Tarjeta RFID asignada exitosamente';
                } else {
                    throw new \Exception('Error al crear la tarjeta RFID');
                }
                
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        header('Location: /admin/tarjetas');
        exit;
    }

    /**
     * Eliminar tarjeta RFID
     */
    public function eliminarTarjeta($uid = null) {
        if (!$uid) {
            $_SESSION['error'] = 'UID de tarjeta no válido';
            header('Location: /admin/tarjetas');
            exit;
        }
        
        try {
            $tarjeta = $this->db->fetch("SELECT * FROM tarjetas_rfid WHERE uid_tarjeta = ?", [$uid]);
            if (!$tarjeta) {
                throw new \Exception('Tarjeta no encontrada');
            }
            
            // Desactivar tarjeta
            $datos = [
                'estado' => 'inactiva',
                'fecha_desasignacion' => date('Y-m-d H:i:s'),
                'observaciones' => ($tarjeta['observaciones'] ? $tarjeta['observaciones'] . ' | ' : '') . 'Desactivada por administrador'
            ];
            
            if ($this->db->update('tarjetas_rfid', $datos, 'uid_tarjeta = ?', [$uid])) {
                $this->registrarLog('DELETE', 'tarjetas_rfid', $tarjeta['id'], 'Tarjeta RFID desactivada: ' . $uid);
                
                $_SESSION['mensaje'] = 'Tarjeta RFID desactivada exitosamente';
            } else {
                throw new \Exception('Error al desactivar la tarjeta');
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: /admin/tarjetas');
        exit;
    }

    /**
     * Reportes administrativos
     */
    public function reportes() {
        $filtros = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'departamento' => $_GET['departamento'] ?? '',
            'usuario' => $_GET['usuario'] ?? ''
        ];
        
        // Obtener datos para el reporte
        $reporte_data = $this->generarDatosReporte($filtros);
        
        $departamentos = $this->db->fetchAll("SELECT * FROM departamentos WHERE activo = 1 ORDER BY nombre");
        $usuarios = $this->db->fetchAll("SELECT id, nombres, apellidos, numero_empleado FROM usuarios WHERE activo = 1 ORDER BY apellidos, nombres");
        
        $data = [
            'title' => 'Reportes Administrativos',
            'reporte_data' => $reporte_data,
            'departamentos' => $departamentos,
            'usuarios' => $usuarios,
            'filtros' => $filtros
        ];
        
        include __DIR__ . '/../Views/admin/reportes.php';
    }

    /**
     * Configuración del sistema
     */
    public function configuracion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $configuraciones = $_POST['config'] ?? [];
                
                foreach ($configuraciones as $clave => $valor) {
                    $this->db->query("
                        INSERT INTO configuracion_sistema (clave, valor, updated_at) 
                        VALUES (?, ?, NOW())
                        ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_at = NOW()
                    ", [$clave, $valor]);
                }
                
                $this->registrarLog('UPDATE', 'configuracion_sistema', null, 'Configuración del sistema actualizada');
                
                $_SESSION['mensaje'] = 'Configuración actualizada exitosamente';
                
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error al actualizar la configuración: ' . $e->getMessage();
            }
        }
        
        // Obtener configuraciones actuales
        $configuraciones = [];
        $configs = $this->db->fetchAll("SELECT clave, valor FROM configuracion_sistema");
        foreach ($configs as $config) {
            $configuraciones[$config['clave']] = $config['valor'];
        }
        
        $data = [
            'title' => 'Configuración del Sistema',
            'configuraciones' => $configuraciones
        ];
        
        include __DIR__ . '/../Views/admin/configuracion.php';
    }

    /**
     * Métodos auxiliares privados
     */
    
    private function obtenerEstadisticas() {
        $stats = [];
        
        // Usuarios
        $stats['usuarios_total'] = $this->db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1")['total'];
        $stats['usuarios_nuevo_mes'] = $this->db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1 AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")['total'];
        
        // Dispositivos
        $stats['dispositivos_total'] = $this->db->fetch("SELECT COUNT(*) as total FROM dispositivos WHERE activo = 1")['total'];
        $stats['dispositivos_online'] = $this->db->fetch("SELECT COUNT(*) as total FROM dispositivos WHERE activo = 1 AND estado = 'online'")['total'];
        
        // Tarjetas
        $stats['tarjetas_total'] = $this->db->fetch("SELECT COUNT(*) as total FROM tarjetas_rfid WHERE estado = 'activa'")['total'];
        $stats['tarjetas_asignadas'] = $this->db->fetch("SELECT COUNT(*) as total FROM tarjetas_rfid WHERE estado = 'activa' AND usuario_id IS NOT NULL")['total'];
        
        // Registros de hoy
        $stats['registros_hoy'] = $this->db->fetch("SELECT COUNT(*) as total FROM registros_asistencia WHERE DATE(fecha_hora) = CURDATE() AND valido = 1")['total'];
        
        return $stats;
    }
    
    private function obtenerActividadReciente() {
        return $this->db->fetchAll("
            SELECT 
                a.fecha_hora,
                a.tipo,
                CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
                d.ubicacion,
                d.nombre as dispositivo_nombre
            FROM asistencias a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
            ORDER BY a.fecha_hora DESC
            LIMIT 10
        ");
    }
    
    private function obtenerAlertas() {
        $alertas = [];
        
        // Dispositivos offline
        $dispositivos_offline = $this->db->fetch("SELECT COUNT(*) as total FROM dispositivos WHERE activo = 1 AND estado != 'online'")['total'];
        if ($dispositivos_offline > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "Hay {$dispositivos_offline} dispositivo(s) fuera de línea"
            ];
        }
        
        // Usuarios sin tarjeta
        $usuarios_sin_tarjeta = $this->db->fetch("
            SELECT COUNT(*) as total
            FROM usuarios u
            LEFT JOIN tarjetas_rfid t ON u.id = t.usuario_id AND t.estado = 'activa'
            WHERE u.activo = 1 AND t.id IS NULL
        ")['total'];
        
        if ($usuarios_sin_tarjeta > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => "Hay {$usuarios_sin_tarjeta} usuario(s) sin tarjeta RFID asignada"
            ];
        }
        
        return $alertas;
    }
    
    private function validarDatosUsuario($datos, $id = null) {
        $errores = [];
        
        // Validaciones básicas
        if (empty(trim($datos['nombres']))) {
            $errores[] = 'El nombre es obligatorio';
        }
        
        if (empty(trim($datos['apellidos']))) {
            $errores[] = 'Los apellidos son obligatorios';
        }
        
        if (empty(trim($datos['email'])) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email es obligatorio y debe ser válido';
        }
        
        if (empty(trim($datos['numero_empleado']))) {
            $errores[] = 'El número de empleado es obligatorio';
        }
        
        if (!$id && (empty($datos['password']) || strlen($datos['password']) < 6)) {
            $errores[] = 'La contraseña es obligatoria y debe tener al menos 6 caracteres';
        }
        
        if (!empty($datos['password']) && strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!empty($errores)) {
            throw new \Exception(implode(', ', $errores));
        }
        
        // Preparar datos limpios
        $datosLimpios = [
            'nombres' => trim($datos['nombres']),
            'apellidos' => trim($datos['apellidos']),
            'email' => trim(strtolower($datos['email'])),
            'numero_empleado' => trim($datos['numero_empleado']),
            'telefono' => trim($datos['telefono'] ?? ''),
            'departamento_id' => !empty($datos['departamento_id']) ? (int)$datos['departamento_id'] : null,
            'horario_id' => !empty($datos['horario_id']) ? (int)$datos['horario_id'] : null,
            'rol' => $datos['rol'] ?? 'empleado',
            'fecha_ingreso' => !empty($datos['fecha_ingreso']) ? $datos['fecha_ingreso'] : date('Y-m-d')
        ];
        
        if (!empty($datos['password'])) {
            $datosLimpios['password'] = $datos['password'];
        }
        
        return $datosLimpios;
    }
    
    private function buscarUsuarios($filtros) {
        $sql = "
            SELECT u.*, d.nombre as departamento_nombre
            FROM usuarios u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE u.activo = 1
        ";
        
        $params = [];
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (u.nombres LIKE ? OR u.apellidos LIKE ? OR u.numero_empleado LIKE ? OR u.email LIKE ?)";
            $search = '%' . $filtros['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }
        
        if (!empty($filtros['departamento'])) {
            $sql .= " AND u.departamento_id = ?";
            $params[] = $filtros['departamento'];
        }
        
        if (!empty($filtros['rol'])) {
            $sql .= " AND u.rol = ?";
            $params[] = $filtros['rol'];
        }
        
        $sql .= " ORDER BY u.apellidos, u.nombres";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function generarDatosReporte($filtros) {
        $sql = "
            SELECT 
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as nombre_completo,
                d.nombre as departamento,
                DATE(ra.fecha_hora) as fecha,
                MIN(CASE WHEN ra.tipo = 'entrada' THEN ra.fecha_hora END) as primera_entrada,
                MAX(CASE WHEN ra.tipo = 'salida' THEN ra.fecha_hora END) as ultima_salida,
                COUNT(CASE WHEN ra.tipo = 'entrada' THEN 1 END) as total_entradas,
                COUNT(CASE WHEN ra.tipo = 'salida' THEN 1 END) as total_salidas
            FROM usuarios u
            LEFT JOIN registros_asistencia ra ON u.id = ra.usuario_id AND ra.valido = 1
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE u.activo = 1
        ";
        
        $params = [];
        
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(ra.fecha_hora) BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicio'];
            $params[] = $filtros['fecha_fin'];
        }
        
        if (!empty($filtros['departamento'])) {
            $sql .= " AND u.departamento_id = ?";
            $params[] = $filtros['departamento'];
        }
        
        if (!empty($filtros['usuario'])) {
            $sql .= " AND u.id = ?";
            $params[] = $filtros['usuario'];
        }
        
        $sql .= " GROUP BY u.id, DATE(ra.fecha_hora) ORDER BY u.apellidos, u.nombres, fecha DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function registrarLog($accion, $tabla, $id_registro, $descripcion) {
        try {
            $this->db->insert('logs_sistema', [
                'usuario_id' => $_SESSION['usuario_id'],
                'accion' => $accion,
                'tabla_afectada' => $tabla,
                'id_registro' => $id_registro,
                'descripcion' => $descripcion,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (\Exception $e) {
            // Log silencioso para evitar interrumpir el flujo principal
            error_log("Error registrando log: " . $e->getMessage());
        }
    }
}
?>