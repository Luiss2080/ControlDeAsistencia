<?php

/**
 * Dashboard del Administrador
 * Sistema de Control de Asistencia
 */
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total_usuarios'] ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-users"></i> Total Usuarios</div>
    </div>
    <div class="stat-card success">
        <div class="stat-number"><?= $stats['dispositivos_activos'] ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-microchip"></i> Dispositivos Activos</div>
    </div>
    <div class="stat-card info">
        <div class="stat-number"><?= $stats['tarjetas_activas'] ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-id-card"></i> Tarjetas RFID</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-number"><?= $stats['marcaciones_hoy'] ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-clock"></i> Marcaciones Hoy</div>
    </div>
</div>

<div class="nav-tabs">
    <button class="nav-tab active" onclick="cambiarTab(event, 'tab-gestion')">
        <i class="fas fa-tachometer-alt"></i> Panel Principal
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-dispositivos')">
        <i class="fas fa-microchip"></i> Estado de Dispositivos
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-actividad')">
        <i class="fas fa-activity"></i> Actividad Reciente
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-accesos')">
        <i class="fas fa-tools"></i> Herramientas
    </button>
</div>

<!-- TAB: Panel Principal -->
<div id="tab-gestion" class="tab-content active">
    <div class="grid-3-cols mb-4">
        <!-- Gestión de Usuarios -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-users"></i> Gestión de Usuarios</h6>
            </div>
            <div class="card-body">
                <div class="flex-column">
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

        <!-- Gestión de Dispositivos -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-microchip"></i> Gestión de Dispositivos</h6>
            </div>
            <div class="card-body">
                <div class="flex-column">
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

    <!-- Accesos Rápidos -->
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-tachometer-alt"></i> Accesos Rápidos</h6>
        </div>
        <div class="card-body">
            <div class="btn-grid">
                <a href="/admin/reportes" class="btn btn-primary">
                    <i class="fas fa-chart-bar"></i><br>
                    Reportes
                </a>
                <a href="/admin/usuarios/importar" class="btn btn-success">
                    <i class="fas fa-file-import"></i><br>
                    Importar Usuarios
                </a>
                <a href="/admin/sistema/backup" class="btn btn-warning">
                    <i class="fas fa-database"></i><br>
                    Backup Sistema
                </a>
                <a href="/admin/logs" class="btn btn-info">
                    <i class="fas fa-list-alt"></i><br>
                    Ver Logs
                </a>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Estado de Dispositivos -->
<div id="tab-dispositivos" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-microchip"></i> Estado de Dispositivos en Tiempo Real</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="dispositivos-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-microchip"></i> Dispositivo</th>
                            <th><i class="fas fa-map-marker-alt"></i> Ubicación</th>
                            <th><i class="fas fa-signal"></i> Estado</th>
                            <th><i class="fas fa-clock"></i> Última Conexión</th>
                            <th><i class="fas fa-tools"></i> Acciones</th>
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

<!-- TAB: Actividad Reciente -->
<div id="tab-actividad" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-activity"></i> Actividad Reciente del Sistema</h6>
        </div>
        <div class="card-body scrollable-content">
            <?php if (!empty($actividad_reciente)): ?>
                <?php foreach ($actividad_reciente as $actividad): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php if ($actividad['tipo'] === 'entrada'): ?>
                                <i class="fas fa-sign-in-alt text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-sign-out-alt text-danger"></i>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <?= htmlspecialchars($actividad['usuario_nombre'] ?? 'Usuario desconocido') ?>
                            </div>
                            <div class="activity-subtitle">
                                <?= ucfirst($actividad['tipo']) ?> - <?= htmlspecialchars($actividad['ubicacion'] ?? 'Ubicación no especificada') ?>
                            </div>
                            <div class="activity-time">
                                <?= date('H:i:s', strtotime($actividad['fecha_hora'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No hay actividad reciente</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB: Herramientas -->
<div id="tab-accesos" class="tab-content">
    <div class="grid-3-cols">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-line"></i> Reportes y Análisis</h6>
            </div>
            <div class="card-body">
                <a href="/admin/reportes/asistencias" class="btn btn-primary btn-block">
                    <i class="fas fa-chart-bar"></i> Reporte de Asistencias
                </a>
                <a href="/admin/reportes/tardanzas" class="btn btn-warning btn-block">
                    <i class="fas fa-clock"></i> Reporte de Tardanzas
                </a>
                <a href="/admin/reportes/ausencias" class="btn btn-danger btn-block">
                    <i class="fas fa-calendar-times"></i> Reporte de Ausencias
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-tools"></i> Herramientas del Sistema</h6>
            </div>
            <div class="card-body">
                <button class="btn btn-success btn-block" onclick="mostrarModal('modal-backup')">
                    <i class="fas fa-download"></i> Backup de Base de Datos
                </button>
                <a href="/admin/sistema/mantenimiento" class="btn btn-warning btn-block">
                    <i class="fas fa-wrench"></i> Mantenimiento del Sistema
                </a>
                <a href="/admin/logs" class="btn btn-info btn-block">
                    <i class="fas fa-file-alt"></i> Ver Logs del Sistema
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Backup -->
<div id="modal-backup" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modal-backup')">&times;</span>
        <h3><i class="fas fa-download"></i> Backup de Base de Datos</h3>
        <p>¿Deseas generar un backup completo de la base de datos?</p>
        <div class="footer-actions">
            <button class="btn btn-secondary" onclick="cerrarModal('modal-backup')">Cancelar</button>
            <button class="btn btn-success" onclick="generarBackup()">
                <i class="fas fa-download"></i> Generar Backup
            </button>
        </div>
    </div>
</div>

<script>
    // Auto-refresh cada 30 segundos
    setTimeout(function() {
        location.reload();
    }, 30000);

    // Función para generar backup
    function generarBackup() {
        if (confirm('¿Estás seguro de generar un backup? Este proceso puede tomar varios minutos.')) {
            Sistema.mostrarLoading('.modal-content');

            // Simular proceso de backup
            setTimeout(() => {
                Sistema.ocultarLoading();
                mostrarAlerta('Backup generado exitosamente', 'success');
                cerrarModal('modal-backup');
            }, 3000);
        }
    }

    // Inicializar DataTable cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Solo inicializar si jQuery y DataTables están disponibles
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#dispositivos-table').DataTable({
                "pageLength": 10,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });
        }
    });
</script>