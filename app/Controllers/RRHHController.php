<?php
/**
 * Controlador de RRHH
 * Sistema de Control de Asistencia
 */

namespace App\Controllers;

use App\Models\Database;

class RRHHController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar autenticación y rol de RRHH
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'rrhh') {
            header('Location: /ControlDeAsistencia/?error=' . urlencode('Acceso denegado'));
            exit;
        }
    }

    /**
     * Dashboard de RRHH
     */
    public function dashboard() {
        // Obtener estadísticas del día
        $estadisticas = $this->obtenerEstadisticasHoy();
        
        // Obtener asistencias del día
        $asistencias_hoy = $this->obtenerAsistenciasHoy();
        
        // Obtener alertas y tardanzas
        $alertas = $this->obtenerAlertas();
        
        include __DIR__ . '/../Views/rrhh/dashboard.php';
    }

    /**
     * Generar reporte de asistencia
     */
    public function reporte() {
        $filtros = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'empleado' => $_GET['empleado'] ?? '',
            'tipo_reporte' => $_GET['tipo_reporte'] ?? 'diario'
        ];
        
        $datos_reporte = $this->generarReporte($filtros);
        $empleados = $this->db->fetchAll("SELECT id, nombres, apellidos, numero_empleado FROM usuarios WHERE activo = 1 AND rol = 'empleado' ORDER BY apellidos, nombres");
        
        include __DIR__ . '/../Views/rrhh/reportes.php';
    }

    /**
     * Ver detalle de empleado específico
     */
    public function verEmpleado($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de empleado no válido';
            header('Location: /ControlDeAsistencia/rrhh');
            exit;
        }
        
        // Obtener información del empleado
        $empleado = $this->db->fetch("SELECT * FROM usuarios WHERE id = ? AND activo = 1", [$id]);
        if (!$empleado) {
            $_SESSION['error'] = 'Empleado no encontrado';
            header('Location: /ControlDeAsistencia/rrhh');
            exit;
        }
        
        // Obtener asistencias del mes actual
        $asistencias_mes = $this->db->fetchAll("
            SELECT a.*, d.nombre as dispositivo_nombre
            FROM asistencias a
            LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
            WHERE a.usuario_id = ? 
            AND MONTH(a.fecha_hora) = MONTH(CURDATE()) 
            AND YEAR(a.fecha_hora) = YEAR(CURDATE())
            ORDER BY a.fecha_hora DESC
        ", [$id]);
        
        // Calcular estadísticas del empleado
        $estadisticas_empleado = $this->calcularEstadisticasEmpleado($id);
        
        include __DIR__ . '/../Views/rrhh/detalle_empleado.php';
    }

    /**
     * Exportar reporte a Excel/PDF
     */
    public function exportarReporte() {
        $filtros = [
            'fecha_inicio' => $_POST['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_POST['fecha_fin'] ?? date('Y-m-d'),
            'empleado' => $_POST['empleado'] ?? '',
            'formato' => $_POST['formato'] ?? 'excel'
        ];
        
        $datos_reporte = $this->generarReporte($filtros);
        
        if ($filtros['formato'] === 'excel') {
            $this->exportarExcel($datos_reporte, $filtros);
        } else {
            $this->exportarPDF($datos_reporte, $filtros);
        }
    }

    /**
     * Métodos auxiliares privados
     */
    private function obtenerEstadisticasHoy() {
        $stats = [];
        
        // Total de empleados activos
        $stats['empleados_total'] = $this->db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1 AND rol = 'empleado'")['total'];
        
        // Empleados presentes hoy (que han marcado entrada)
        $stats['presentes_hoy'] = $this->db->fetch("
            SELECT COUNT(DISTINCT usuario_id) as total 
            FROM asistencias 
            WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'entrada'
        ")['total'];
        
        // Tardanzas de hoy
        $stats['tardanzas_hoy'] = $this->db->fetch("
            SELECT COUNT(*) as total 
            FROM asistencias 
            WHERE DATE(fecha_hora) = CURDATE() AND es_tardanza = 1
        ")['total'];
        
        // Ausentes (empleados que no han marcado entrada hoy)
        $stats['ausentes_hoy'] = $stats['empleados_total'] - $stats['presentes_hoy'];
        
        return $stats;
    }
    
    private function obtenerAsistenciasHoy() {
        return $this->db->fetchAll("
            SELECT 
                a.*,
                CONCAT(u.nombres, ' ', u.apellidos) as empleado,
                u.numero_empleado,
                d.nombre as dispositivo,
                TIME(a.fecha_hora) as hora
            FROM asistencias a
            JOIN usuarios u ON a.usuario_id = u.id
            LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
            WHERE DATE(a.fecha_hora) = CURDATE()
            ORDER BY a.fecha_hora DESC
        ");
    }
    
    private function obtenerAlertas() {
        $alertas = [];
        
        // Empleados con múltiples tardanzas esta semana
        $tardanzas_semana = $this->db->fetchAll("
            SELECT 
                u.nombres,
                u.apellidos,
                u.numero_empleado,
                COUNT(*) as total_tardanzas
            FROM asistencias a
            JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.es_tardanza = 1 
            AND WEEK(a.fecha_hora) = WEEK(CURDATE())
            AND YEAR(a.fecha_hora) = YEAR(CURDATE())
            GROUP BY u.id
            HAVING total_tardanzas >= 3
            ORDER BY total_tardanzas DESC
        ");
        
        foreach ($tardanzas_semana as $tardanza) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "Empleado {$tardanza['nombres']} {$tardanza['apellidos']} ({$tardanza['numero_empleado']}) tiene {$tardanza['total_tardanzas']} tardanzas esta semana"
            ];
        }
        
        // Empleados ausentes hoy
        $ausentes_hoy = $this->db->fetchAll("
            SELECT u.nombres, u.apellidos, u.numero_empleado
            FROM usuarios u
            LEFT JOIN asistencias a ON u.id = a.usuario_id AND DATE(a.fecha_hora) = CURDATE()
            WHERE u.activo = 1 AND u.rol = 'empleado' AND a.id IS NULL
            ORDER BY u.apellidos, u.nombres
        ");
        
        if (count($ausentes_hoy) > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => count($ausentes_hoy) . " empleado(s) ausente(s) hoy"
            ];
        }
        
        return $alertas;
    }
    
    private function generarReporte($filtros) {
        $sql = "
            SELECT 
                u.numero_empleado,
                CONCAT(u.nombres, ' ', u.apellidos) as empleado,
                u.puesto,
                DATE(a.fecha_hora) as fecha,
                MIN(CASE WHEN a.tipo = 'entrada' THEN TIME(a.fecha_hora) END) as primera_entrada,
                MAX(CASE WHEN a.tipo = 'salida' THEN TIME(a.fecha_hora) END) as ultima_salida,
                COUNT(CASE WHEN a.tipo = 'entrada' THEN 1 END) as total_entradas,
                COUNT(CASE WHEN a.tipo = 'salida' THEN 1 END) as total_salidas,
                SUM(a.es_tardanza) as tardanzas,
                CASE 
                    WHEN COUNT(CASE WHEN a.tipo = 'entrada' THEN 1 END) = 0 THEN 'Ausente'
                    WHEN SUM(a.es_tardanza) > 0 THEN 'Tardanza'
                    ELSE 'Puntual'
                END as estado
            FROM usuarios u
            LEFT JOIN asistencias a ON u.id = a.usuario_id 
                AND DATE(a.fecha_hora) BETWEEN ? AND ?
            WHERE u.activo = 1 AND u.rol = 'empleado'
        ";
        
        $params = [$filtros['fecha_inicio'], $filtros['fecha_fin']];
        
        if (!empty($filtros['empleado'])) {
            $sql .= " AND u.id = ?";
            $params[] = $filtros['empleado'];
        }
        
        $sql .= " GROUP BY u.id, DATE(a.fecha_hora) ORDER BY fecha DESC, u.apellidos, u.nombres";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function calcularEstadisticasEmpleado($usuario_id) {
        $stats = [];
        
        // Estadísticas del mes actual
        $stats['dias_trabajados'] = $this->db->fetch("
            SELECT COUNT(DISTINCT DATE(fecha_hora)) as total
            FROM asistencias 
            WHERE usuario_id = ? 
            AND MONTH(fecha_hora) = MONTH(CURDATE())
            AND YEAR(fecha_hora) = YEAR(CURDATE())
            AND tipo = 'entrada'
        ", [$usuario_id])['total'];
        
        $stats['tardanzas_mes'] = $this->db->fetch("
            SELECT COUNT(*) as total
            FROM asistencias 
            WHERE usuario_id = ? 
            AND MONTH(fecha_hora) = MONTH(CURDATE())
            AND YEAR(fecha_hora) = YEAR(CURDATE())
            AND es_tardanza = 1
        ", [$usuario_id])['total'];
        
        $stats['puntualidad'] = $stats['dias_trabajados'] > 0 ? 
            round((($stats['dias_trabajados'] - $stats['tardanzas_mes']) / $stats['dias_trabajados']) * 100, 2) : 100;
        
        return $stats;
    }
    
    private function exportarExcel($datos, $filtros) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="reporte_asistencia_' . date('Y-m-d') . '.xls"');
        
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>Número Empleado</th>";
        echo "<th>Empleado</th>";
        echo "<th>Puesto</th>";
        echo "<th>Fecha</th>";
        echo "<th>Primera Entrada</th>";
        echo "<th>Última Salida</th>";
        echo "<th>Total Entradas</th>";
        echo "<th>Total Salidas</th>";
        echo "<th>Tardanzas</th>";
        echo "<th>Estado</th>";
        echo "</tr>";
        
        foreach ($datos as $fila) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fila['numero_empleado']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['empleado']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['puesto']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['primera_entrada']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['ultima_salida']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['total_entradas']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['total_salidas']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['tardanzas']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['estado']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    }
    
    private function exportarPDF($datos, $filtros) {
        // Implementación básica de PDF (se puede mejorar con librerías como TCPDF)
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="reporte_asistencia_' . date('Y-m-d') . '.pdf"');
        
        // Por ahora, generar HTML que se puede convertir a PDF
        echo "<!DOCTYPE html><html><head><title>Reporte de Asistencia</title></head><body>";
        echo "<h1>Reporte de Asistencia</h1>";
        echo "<p>Período: " . $filtros['fecha_inicio'] . " al " . $filtros['fecha_fin'] . "</p>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Número Empleado</th><th>Empleado</th><th>Fecha</th><th>Estado</th>";
        echo "</tr>";
        
        foreach ($datos as $fila) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fila['numero_empleado']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['empleado']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['estado']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</body></html>";
        exit;
    }
}
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel RRHH - Sistema de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stats-card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .stats-card:hover { transform: translateY(-5px); }
        .stats-card .icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
        .bg-primary-gradient { background: linear-gradient(45deg, #007bff, #0056b3); }
        .bg-success-gradient { background: linear-gradient(45deg, #28a745, #1e7e34); }
        .bg-warning-gradient { background: linear-gradient(45deg, #ffc107, #d39e00); }
        .bg-danger-gradient { background: linear-gradient(45deg, #dc3545, #bd2130); }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-chart-line"></i> Panel de Recursos Humanos
            </span>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    Bienvenido, ' . htmlspecialchars($_SESSION['usuario']['nombres']) . '
                </span>
                <a href="/logout" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-primary-gradient">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">' . $empleados_activos . '</h4>
                            <small class="text-muted">Empleados Activos</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-success-gradient">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">' . $presentes_hoy . '</h4>
                            <small class="text-muted">Presentes Hoy</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-warning-gradient">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">' . $tardanzas_hoy . '</h4>
                            <small class="text-muted">Tardanzas</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-danger-gradient">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">' . $ausentes_hoy . '</h4>
                            <small class="text-muted">Ausencias</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asistencias de Hoy -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Asistencias de Hoy</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Tipo</th>
                                <th>Hora</th>
                                <th>Dispositivo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>';

        if (!empty($asistencias_recientes)) {
            foreach ($asistencias_recientes as $asistencia) {
                $hora = date('H:i', strtotime($asistencia['fecha_hora']));
                $tipo = ucfirst($asistencia['tipo']);
                $badge_tipo = $asistencia['tipo'] === 'entrada' ? 'success' : 'primary';
                $estado = $asistencia['es_tardanza'] ? 'Tardanza' : 'Puntual';
                $badge_estado = $asistencia['es_tardanza'] ? 'warning' : 'success';
                
                echo '<tr>
                    <td>' . htmlspecialchars($asistencia['nombre_usuario']) . '</td>
                    <td><span class="badge bg-' . $badge_tipo . '">' . $tipo . '</span></td>
                    <td>' . $hora . '</td>
                    <td>' . htmlspecialchars($asistencia['dispositivo_nombre'] ?? 'Desconocido') . '</td>
                    <td><span class="badge bg-' . $badge_estado . '">' . $estado . '</span></td>
                </tr>';
            }
        } else {
            echo '<tr><td colspan="5" class="text-center text-muted">No hay registros de hoy</td></tr>';
        }

        echo '          </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Enlaces rápidos -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-2x text-primary mb-3"></i>
                        <h5>Reportes</h5>
                        <p class="text-muted">Generar reportes detallados</p>
                        <button class="btn btn-primary" disabled>Próximamente</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-download fa-2x text-success mb-3"></i>
                        <h5>Exportar Datos</h5>
                        <p class="text-muted">Descargar en Excel/PDF</p>
                        <button class="btn btn-success" disabled>Próximamente</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-cog fa-2x text-warning mb-3"></i>
                        <h5>Configuración</h5>
                        <p class="text-muted">Ajustes del sistema</p>
                        <a href="/admin" class="btn btn-warning">Ir a Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    }
}
?>