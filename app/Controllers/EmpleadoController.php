<?php
/**
 * Controlador de Empleado
 * Sistema de Control de Asistencia
 */

namespace App\Controllers;

use App\Models\Database;
use App\Utils\Auth;

class EmpleadoController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar autenticación y rol de empleado
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'empleado') {
            header('Location: /login?error=Acceso denegado');
            exit;
        }
    }

    /**
     * Dashboard de empleado
     */
    public function dashboard() {
        $usuario_id = $_SESSION['usuario_id'];
        
        // Obtener asistencias del mes actual
        $asistencias_mes = $this->db->fetchAll("
            SELECT a.*, d.nombre as dispositivo_nombre
            FROM asistencias a
            LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
            WHERE a.usuario_id = ? 
            AND MONTH(a.fecha_hora) = MONTH(CURDATE()) 
            AND YEAR(a.fecha_hora) = YEAR(CURDATE())
            ORDER BY a.fecha_hora DESC
        ", [$usuario_id]);
        
        // Calcular estadísticas
        $total_dias = count($asistencias_mes);
        $tardanzas = count(array_filter($asistencias_mes, function($a) { return $a['es_tardanza']; }));
        $puntuales = $total_dias - $tardanzas;
        
        // Obtener horario del empleado
        $empleado = $this->db->fetch("SELECT * FROM usuarios WHERE id = ?", [$usuario_id]);
        $horario_entrada = $empleado['horario_entrada'] ?? '08:00:00';
        $horario_salida = $empleado['horario_salida'] ?? '17:00:00';

        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Empleado - Sistema de Asistencia</title>
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
        .bg-info-gradient { background: linear-gradient(45deg, #17a2b8, #138496); }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-user"></i> Panel del Empleado
            </span>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    ' . htmlspecialchars($_SESSION['usuario']['nombres']) . ' ' . htmlspecialchars($_SESSION['usuario']['apellidos']) . '
                </span>
                <a href="/logout" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Estadísticas del Mes -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-primary-gradient">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">' . $total_dias . '</h4>
                            <small class="text-muted">Total Asistencias</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-success-gradient">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">' . $puntuales . '</h4>
                            <small class="text-muted">Puntuales</small>
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
                            <h4 class="mb-0">' . $tardanzas . '</h4>
                            <small class="text-muted">Tardanzas</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-info-gradient">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">' . ($total_dias > 0 ? round(($puntuales / $total_dias) * 100) : 0) . '%</h4>
                            <small class="text-muted">Puntualidad</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Historial de Asistencias -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Historial de Asistencias (Este Mes)</h5>
                        <a href="/empleado/historial" class="btn btn-outline-primary btn-sm">Ver Todo</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Estado</th>
                                        <th>Dispositivo</th>
                                    </tr>
                                </thead>
                                <tbody>';
        
        if (!empty($asistencias_mes)) {
            // Agrupar por fecha
            $por_fecha = [];
            foreach ($asistencias_mes as $asistencia) {
                $fecha = date('Y-m-d', strtotime($asistencia['fecha_hora']));
                if (!isset($por_fecha[$fecha])) {
                    $por_fecha[$fecha] = [];
                }
                $por_fecha[$fecha][] = $asistencia;
            }
            
            foreach ($por_fecha as $fecha => $asistencias_dia) {
                $entrada = null;
                $salida = null;
                $estado = 'Incompleto';
                $dispositivo = '';
                
                foreach ($asistencias_dia as $asistencia) {
                    if ($asistencia['tipo'] === 'entrada') {
                        $entrada = $asistencia;
                    } else {
                        $salida = $asistencia;
                    }
                }
                
                if ($entrada) {
                    $estado = $entrada['es_tardanza'] ? 'Tardanza' : 'Puntual';
                    $dispositivo = $entrada['dispositivo_nombre'] ?? 'Desconocido';
                }
                
                $badge_estado = $estado === 'Puntual' ? 'success' : ($estado === 'Tardanza' ? 'warning' : 'secondary');
                
                echo '<tr>
                    <td>' . date('d/m/Y', strtotime($fecha)) . '</td>
                    <td>' . ($entrada ? date('H:i', strtotime($entrada['fecha_hora'])) : '-') . '</td>
                    <td>' . ($salida ? date('H:i', strtotime($salida['fecha_hora'])) : '-') . '</td>
                    <td><span class="badge bg-' . $badge_estado . '">' . $estado . '</span></td>
                    <td>' . htmlspecialchars($dispositivo) . '</td>
                </tr>';
            }
        } else {
            echo '<tr><td colspan="5" class="text-center text-muted">No hay registros este mes</td></tr>';
        }
        
        echo '              </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Información del Empleado -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Mi Información</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Número:</strong> ' . htmlspecialchars($empleado['numero_empleado']) . '</p>
                        <p><strong>Email:</strong> ' . htmlspecialchars($empleado['email']) . '</p>
                        <p><strong>Puesto:</strong> ' . htmlspecialchars($empleado['puesto'] ?? 'No asignado') . '</p>
                        <p><strong>Teléfono:</strong> ' . htmlspecialchars($empleado['telefono'] ?? 'No registrado') . '</p>
                    </div>
                </div>

                <!-- Horarios -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Mi Horario</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h6 class="text-muted">Entrada</h6>
                                <h4 class="text-success">' . date('H:i', strtotime($horario_entrada)) . '</h4>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted">Salida</h6>
                                <h4 class="text-primary">' . date('H:i', strtotime($horario_salida)) . '</h4>
                            </div>
                        </div>
                        <hr>
                        <p class="text-muted text-center mb-0">
                            <small><i class="fas fa-info-circle"></i> Tolerancia: 15 minutos</small>
                        </p>
                    </div>
                </div>

                <!-- Mi Tarjeta RFID -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-id-card"></i> Mi Tarjeta</h5>
                    </div>
                    <div class="card-body">';
                    
        $tarjeta = $this->db->fetch("SELECT uid_tarjeta FROM tarjetas_rfid WHERE usuario_id = ? AND estado = 'activa'", [$usuario_id]);
        
        if ($tarjeta) {
            echo '<p class="text-center">
                    <code class="fs-5">' . htmlspecialchars($tarjeta['uid_tarjeta']) . '</code>
                  </p>
                  <p class="text-muted text-center mb-0">
                    <small>Usa esta tarjeta para marcar asistencia</small>
                  </p>';
        } else {
            echo '<p class="text-muted text-center mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i><br>
                    No tienes tarjeta asignada<br>
                    <small>Contacta a RRHH</small>
                  </p>';
        }
        
        echo '      </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    }

    /**
     * Ver historial completo
     */
    public function historial() {
        $usuario_id = $_SESSION['usuario_id'];
        
        // Obtener todas las asistencias del usuario
        $asistencias = $this->db->fetchAll("
            SELECT a.*, d.nombre as dispositivo_nombre
            FROM asistencias a
            LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
            WHERE a.usuario_id = ?
            ORDER BY a.fecha_hora DESC
            LIMIT 100
        ", [$usuario_id]);
        
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Asistencias</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .header { background: #27ae60; color: white; padding: 20px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background-color: #f8f9fa; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 12px; }
        .badge.success { background: #27ae60; }
        .badge.warning { background: #f39c12; }
        .badge.primary { background: #3498db; }
        .badge.danger { background: #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Historial Completo de Asistencias</h1>
            <a href="/empleado" style="float: right; background: white; color: #27ae60; padding: 10px 20px; text-decoration: none; border-radius: 5px;">← Volver</a>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Dispositivo</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>';
        
        if (empty($asistencias)) {
            echo '<tr><td colspan="6" style="text-align: center; color: #666;">No hay registros de asistencia</td></tr>';
        } else {
            foreach ($asistencias as $asistencia) {
                $fecha = date('d/m/Y', strtotime($asistencia['fecha_hora']));
                $hora = date('H:i:s', strtotime($asistencia['fecha_hora']));
                $tipo = ucfirst($asistencia['tipo']);
                $estado = $asistencia['es_tardanza'] ? 'Tardanza' : 'Puntual';
                $badge_class = $asistencia['es_tardanza'] ? 'warning' : 'success';
                $tipo_badge = $asistencia['tipo'] === 'entrada' ? 'primary' : 'danger';
                
                echo '<tr>
                    <td>' . $fecha . '</td>
                    <td>' . $hora . '</td>
                    <td><span class="badge ' . $tipo_badge . '">' . $tipo . '</span></td>
                    <td><span class="badge ' . $badge_class . '">' . $estado . '</span></td>
                    <td>' . htmlspecialchars($asistencia['dispositivo_nombre'] ?? 'Desconocido') . '</td>
                    <td>' . htmlspecialchars($asistencia['observaciones'] ?? '') . '</td>
                </tr>';
            }
        }
        
        echo '</tbody>
        </table>
    </div>
</body>
</html>';
    }
}
?>