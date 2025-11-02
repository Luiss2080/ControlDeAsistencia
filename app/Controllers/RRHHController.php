<?php
/**
 * Controlador de RRHH
 * Sistema de Control de Asistencia
 */

namespace App\Controllers;

use App\Models\Database;
use App\Utils\Auth;

class RRHHController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar autenticación y rol de RRHH
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'rrhh') {
            header('Location: /login?error=Acceso denegado');
            exit;
        }
    }

    /**
     * Dashboard de RRHH
     */
    public function dashboard() {
        // Obtener estadísticas
        $empleados_activos = $this->db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1 AND rol = 'empleado'")['total'];
        $presentes_hoy = $this->db->fetch("SELECT COUNT(DISTINCT usuario_id) as total FROM asistencias WHERE DATE(fecha_hora) = CURDATE()")['total'];
        $tardanzas_hoy = $this->db->fetch("SELECT COUNT(*) as total FROM asistencias WHERE DATE(fecha_hora) = CURDATE() AND es_tardanza = 1")['total'];
        $ausentes_hoy = $empleados_activos - $presentes_hoy;
        
        // Obtener asistencias recientes
        $asistencias_recientes = $this->db->fetchAll("
            SELECT a.*, CONCAT(u.nombres, ' ', u.apellidos) as nombre_usuario, d.nombre as dispositivo_nombre
            FROM asistencias a
            JOIN usuarios u ON a.usuario_id = u.id
            LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
            WHERE DATE(a.fecha_hora) = CURDATE()
            ORDER BY a.fecha_hora DESC
            LIMIT 10
        ");

        echo '<!DOCTYPE html>
<html lang="es">
<head>
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