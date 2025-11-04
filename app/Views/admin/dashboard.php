<?php
/**
 * Dashboard del Administrador
 * Sistema de Control de Asistencia
 */

$titulo = 'Panel de Administración';
$breadcrumb = 'Dashboard';
ob_start();
?>

<div class="row">
    <!-- Estadísticas generales -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Usuarios
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['total_usuarios'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Dispositivos Activos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['dispositivos_activos'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-microchip fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tarjetas RFID
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['tarjetas_activas'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-id-card fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Marcaciones Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['marcaciones_hoy'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gestión de Usuarios -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Gestión de Usuarios</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Acciones:</div>
                        <a class="dropdown-item" href="/admin/usuarios/crear">Crear Usuario</a>
                        <a class="dropdown-item" href="/admin/usuarios">Ver Todos</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/usuarios" class="btn btn-primary">
                        <i class="fas fa-users"></i> Gestionar Usuarios
                    </a>
                    <a href="/admin/usuarios/crear" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
                    </a>
                    <a href="/admin/tarjetas" class="btn btn-info">
                        <i class="fas fa-id-card"></i> Asignar Tarjetas RFID
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Gestión de Dispositivos -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-success">Gestión de Dispositivos</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink2"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink2">
                        <div class="dropdown-header">Acciones:</div>
                        <a class="dropdown-item" href="/admin/dispositivos/crear">Registrar Dispositivo</a>
                        <a class="dropdown-item" href="/admin/dispositivos">Ver Estado</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/dispositivos" class="btn btn-primary">
                        <i class="fas fa-microchip"></i> Estado de Dispositivos
                    </a>
                    <a href="/admin/dispositivos/crear" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Registrar Dispositivo
                    </a>
                    <a href="/admin/configuracion" class="btn btn-warning">
                        <i class="fas fa-cog"></i> Configuración del Sistema
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Estado de Dispositivos en Tiempo Real -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Estado de Dispositivos en Tiempo Real</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dispositivos-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Dispositivo</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Última Conexión</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dispositivos_activos)): ?>
                                <?php foreach ($dispositivos_activos as $dispositivo): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($dispositivo['nombre']) ?></td>
                                        <td><?= htmlspecialchars($dispositivo['ubicacion']) ?></td>
                                        <td>
                                            <?php
                                            $status = $dispositivo['status_conexion'] ?? 'offline';
                                            $badgeClass = [
                                                'online' => 'success',
                                                'warning' => 'warning',
                                                'offline' => 'danger'
                                            ][$status] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $badgeClass ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($dispositivo['ultima_conexion']): ?>
                                                <small><?= date('d/m/Y H:i:s', strtotime($dispositivo['ultima_conexion'])) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Nunca</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/admin/dispositivos/<?= $dispositivo['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/dispositivos/<?= $dispositivo['id'] ?>/editar" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay dispositivos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if (!empty($actividad_reciente)): ?>
                    <?php foreach ($actividad_reciente as $actividad): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <?php if ($actividad['tipo'] === 'entrada'): ?>
                                    <i class="fas fa-sign-in-alt text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-sign-out-alt text-danger"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small font-weight-bold">
                                    <?= htmlspecialchars($actividad['usuario_nombre'] ?? 'Usuario desconocido') ?>
                                </div>
                                <div class="small text-muted">
                                    <?= ucfirst($actividad['tipo']) ?> - <?= htmlspecialchars($actividad['ubicacion'] ?? 'Ubicación no especificada') ?>
                                </div>
                                <div class="small text-muted">
                                    <?= date('H:i:s', strtotime($actividad['fecha_hora'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No hay actividad reciente</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Accesos Rápidos -->
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Accesos Rápidos</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="/admin/reportes" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-chart-bar"></i><br>
                            Reportes
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/admin/usuarios/importar" class="btn btn-outline-success btn-block">
                            <i class="fas fa-file-import"></i><br>
                            Importar Usuarios
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/admin/sistema/backup" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-database"></i><br>
                            Backup Sistema
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/admin/logs" class="btn btn-outline-info btn-block">
                            <i class="fas fa-list-alt"></i><br>
                            Ver Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh cada 30 segundos
setTimeout(function() {
    location.reload();
}, 30000);

// Tabla de dispositivos responsiva
$(document).ready(function() {
    $('#dispositivos-table').DataTable({
        "pageLength": 5,
        "lengthChange": false,
        "searching": false,
        "info": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        }
    });
});
</script>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>