<?php

/**
 * Dashboard de Recursos Humanos
 * Sistema de Control de Asistencia
 */
?>

<!-- Alertas en tiempo real -->
<?php if (!empty($alertas)): ?>
    <div class="alertas-tiempo-real mb-4">
        <?php foreach ($alertas as $alerta): ?>
            <div class="alert alert-<?= $alerta['tipo'] ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $alerta['icono'] ?>"></i>
                <strong><?= $alerta['titulo'] ?></strong> <?= $alerta['mensaje'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="stats-grid" id="stats-container">
    <div class="stat-card success" id="stat-empleados">
        <div class="stat-number"><?php echo ($estadisticas['total_empleados'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-users"></i> Total Empleados</div>
        <div class="stat-trend">
            <small class="text-muted">Activos en sistema</small>
        </div>
    </div>
    <div class="stat-card" id="stat-presentes">
        <div class="stat-number"><?php echo ($estadisticas['presentes_hoy'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-user-check"></i> Presentes Hoy</div>
        <div class="stat-trend">
            <small class="text-success">
                <?= round((($estadisticas['presentes_hoy'] ?? 0) / max(($estadisticas['total_empleados'] ?? 1), 1)) * 100, 1) ?>% asistencia
            </small>
        </div>
    </div>
    <div class="stat-card warning" id="stat-tardanzas">
        <div class="stat-number"><?php echo ($estadisticas['tardanzas_hoy'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-clock"></i> Tardanzas Hoy</div>
        <div class="stat-trend">
            <small class="text-warning">
                <?php if (($estadisticas['tardanzas_hoy'] ?? 0) > 0): ?>
                    <i class="fas fa-arrow-up"></i> Requiere atención
                <?php else: ?>
                    <i class="fas fa-check"></i> Sin tardanzas
                <?php endif; ?>
            </small>
        </div>
    </div>
    <div class="stat-card danger" id="stat-ausentes">
        <div class="stat-number"><?php echo ($estadisticas['ausentes_hoy'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-user-times"></i> Ausentes Hoy</div>
        <div class="stat-trend">
            <small class="text-danger">
                <?php if (($estadisticas['ausentes_hoy'] ?? 0) > 0): ?>
                    <i class="fas fa-exclamation-triangle"></i> Revisar ausencias
                <?php else: ?>
                    <i class="fas fa-check"></i> Todos presentes
                <?php endif; ?>
            </small>
        </div>
    </div>
</div>

<!-- Indicador de última actualización -->
<div class="update-indicator mb-3">
    <small class="text-muted">
        <i class="fas fa-clock"></i> Última actualización:
        <span id="last-update"><?= date('H:i:s') ?></span>
        <button class="btn btn-link btn-sm p-0 ms-2" onclick="actualizarDashboard()" id="btn-actualizar">
            <i class="fas fa-sync-alt"></i> Actualizar ahora
        </button>
    </small>
</div>

<div class="nav-tabs">
    <button class="nav-tab active" onclick="cambiarTab(event, 'tab-hoy')">
        <i class="fas fa-calendar-day"></i> Asistencias de Hoy
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-reportes')">
        <i class="fas fa-chart-bar"></i> Reportes
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-tardanzas')">
        <i class="fas fa-exclamation-triangle"></i> Empleados con Tardanzas
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-semanal')">
        <i class="fas fa-chart-line"></i> Reporte Semanal
    </button>
</div>

<!-- TAB: Asistencias de Hoy -->
<div id="tab-hoy" class="tab-content active">
    <div class="card">
        <div class="card-header flex-space-between">
            <h6><i class="fas fa-calendar-day"></i> Asistencias de Hoy (<?php echo date('d/m/Y'); ?>)</h6>
            <div>
                <button class="btn btn-success btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
                <button class="btn btn-primary btn-sm" onclick="exportarAsistenciasHoy()">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="tabla-asistencias-hoy">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Empleado</th>
                            <th><i class="fas fa-clock"></i> Hora Entrada</th>
                            <th><i class="fas fa-info-circle"></i> Estado</th>
                            <th><i class="fas fa-mobile-alt"></i> Dispositivo</th>
                            <th><i class="fas fa-sticky-note"></i> Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($asistencias_hoy)): ?>
                            <?php foreach ($asistencias_hoy as $asistencia): ?>
                                <?php
                                $estado_clase = '';
                                $estado_texto = $asistencia['estado'];

                                switch ($asistencia['estado']) {
                                    case 'puntual':
                                        $estado_clase = 'success';
                                        $estado_texto = '<i class="fas fa-check-circle"></i> Puntual';
                                        break;
                                    case 'tardanza':
                                        $estado_clase = 'warning';
                                        $estado_texto = '<i class="fas fa-exclamation-triangle"></i> Tardanza';
                                        break;
                                    case 'ausente':
                                        $estado_clase = 'danger';
                                        $estado_texto = '<i class="fas fa-times-circle"></i> Ausente';
                                        break;
                                }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($asistencia['usuario_nombre']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($asistencia['usuario_email']); ?></small>
                                    </td>
                                    <td><?php echo ($asistencia['hora_entrada'] ? '<strong>' . date('H:i', strtotime($asistencia['hora_entrada'])) . '</strong>' : '-'); ?></td>
                                    <td><span class="badge badge-<?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></span></td>
                                    <td><?php echo htmlspecialchars($asistencia['dispositivo_nombre'] ?? 'No registrado'); ?></td>
                                    <td><?php echo htmlspecialchars($asistencia['observaciones'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-clipboard-list fa-3x empty-state-icon"></i><br>
                                    No hay registros de asistencia para hoy
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Reportes -->
<div id="tab-reportes" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-chart-bar"></i> Generar Reportes de Asistencia</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p class="card-info-header"><i class="fas fa-info-circle"></i> <strong>Generador de Reportes</strong></p>
                <p class="card-info-description">
                    Selecciona el rango de fechas y formato para generar reportes personalizados
                </p>
            </div>

            <form id="form-reporte" class="form-inline">
                <div class="form-grid">
                    <div class="form-group form-group-no-margin">
                        <label for="fecha_inicio"><i class="fas fa-calendar-alt"></i> Fecha Inicio:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                    </div>
                    <div class="form-group form-group-no-margin">
                        <label for="fecha_fin"><i class="fas fa-calendar-alt"></i> Fecha Fin:</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo date('Y-m-t'); ?>" required>
                    </div>
                    <div class="form-group form-group-no-margin">
                        <label for="formato"><i class="fas fa-file-alt"></i> Formato:</label>
                        <select id="formato" name="formato" class="form-control">
                            <option value="html"><i class="fas fa-eye"></i> Ver en pantalla</option>
                            <option value="csv"><i class="fas fa-file-csv"></i> Descargar CSV</option>
                            <option value="json"><i class="fas fa-code"></i> JSON (API)</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-chart-line"></i> Generar Reporte
                        </button>
                    </div>
                </div>
            </form>

            <div class="grid-3-cols">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-bolt"></i> Reportes Rápidos</h6>
                    </div>
                    <div class="card-body">
                        <div class="flex-column">
                            <button class="btn btn-primary btn-sm" onclick="generarReporteRapido('hoy')">
                                <i class="fas fa-calendar-day"></i> Asistencias Hoy
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="generarReporteRapido('semana')">
                                <i class="fas fa-calendar-week"></i> Esta Semana
                            </button>
                            <button class="btn btn-success btn-sm" onclick="generarReporteRapido('mes')">
                                <i class="fas fa-calendar-alt"></i> Este Mes
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="generarReporteRapido('tardanzas')">
                                <i class="fas fa-exclamation-triangle"></i> Solo Tardanzas
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie"></i> Estadísticas del Mes</h6>
                    </div>
                    <div class="card-body">
                        <div class="info-line">
                            <p><strong><i class="fas fa-exclamation-triangle"></i> Total Tardanzas:</strong> <?php echo ($estadisticas['tardanzas_mes'] ?? 0); ?></p>
                            <p><strong><i class="fas fa-chart-line"></i> Promedio Diario:</strong> <?php echo round(($estadisticas['tardanzas_mes'] ?? 0) / date('j'), 1); ?></p>
                            <p><strong><i class="fas fa-calendar-check"></i> Días Transcurridos:</strong> <?php echo date('j'); ?> de <?php echo date('t'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Empleados con Tardanzas -->
<div id="tab-tardanzas" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-exclamation-triangle"></i> Empleados con Más Tardanzas Este Mes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="tabla-tardanzas">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Empleado</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-exclamation-triangle"></i> Total Tardanzas</th>
                            <th><i class="fas fa-tools"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($empleados_tardanzas)): ?>
                            <?php foreach ($empleados_tardanzas as $empleado): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($empleado['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                                    <td>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle"></i> <?php echo $empleado['total_tardanzas']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="verDetalleEmpleado('<?php echo htmlspecialchars($empleado['email']); ?>')">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-check-circle fa-3x empty-state-icon success"></i><br>
                                    ¡Excelente! No hay empleados con tardanzas este mes
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Reporte Semanal -->
<div id="tab-semanal" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-chart-line"></i> Reporte de los Últimos 7 Días</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="tabla-semanal">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-day"></i> Fecha</th>
                            <th><i class="fas fa-users"></i> Total Presentes</th>
                            <th><i class="fas fa-check-circle"></i> Puntuales</th>
                            <th><i class="fas fa-exclamation-triangle"></i> Tardanzas</th>
                            <th><i class="fas fa-percentage"></i> % Puntualidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reporte_semanal)): ?>
                            <?php foreach ($reporte_semanal as $dia): ?>
                                <?php
                                $total = $dia['total_presentes'];
                                $puntuales = $dia['puntuales'];
                                $tardanzas = $dia['tardanzas'];
                                $porcentaje = $total > 0 ? round(($puntuales / $total) * 100, 1) : 0;

                                $porcentaje_clase = '';
                                if ($porcentaje >= 90) $porcentaje_clase = 'success';
                                elseif ($porcentaje >= 70) $porcentaje_clase = 'warning';
                                else $porcentaje_clase = 'danger';
                                ?>
                                <tr>
                                    <td><strong><?php echo date('d/m/Y (l)', strtotime($dia['dia'])); ?></strong></td>
                                    <td><?php echo $total; ?></td>
                                    <td><span class="badge badge-success"><?php echo $puntuales; ?></span></td>
                                    <td><span class="badge badge-warning"><?php echo $tardanzas; ?></span></td>
                                    <td><span class="badge badge-<?php echo $porcentaje_clase; ?>"><?php echo $porcentaje; ?>%</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-chart-line fa-3x empty-state-icon"></i><br>
                                    No hay datos para mostrar
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($reporte_semanal)): ?>
                <div class="summary-card">
                    <h6 class="summary-title"><i class="fas fa-chart-line"></i> Resumen Semanal</h6>
                    <p><strong><i class="fas fa-chart-bar"></i> Promedio de Asistencia:</strong> <?php echo round(array_sum(array_column($reporte_semanal, 'total_presentes')) / max(1, count($reporte_semanal)), 1); ?> empleados/día</p>
                    <p><strong><i class="fas fa-exclamation-triangle"></i> Total Tardanzas:</strong> <?php echo array_sum(array_column($reporte_semanal, 'tardanzas')); ?></p>
                    <p><strong><i class="fas fa-check-circle"></i> Total Puntuales:</strong> <?php echo array_sum(array_column($reporte_semanal, 'puntuales')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Generar reporte desde formulario
    document.getElementById("form-reporte").addEventListener("submit", function(e) {
        e.preventDefault();

        const fechaInicio = document.getElementById("fecha_inicio").value;
        const fechaFin = document.getElementById("fecha_fin").value;
        const formato = document.getElementById("formato").value;

        const url = `/rrhh/reporte?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&formato=${formato}`;

        if (formato === "csv") {
            // Descargar archivo
            window.location.href = url;
        } else {
            // Abrir en nueva pestaña
            window.open(url, "_blank");
        }
    });

    // Reportes rápidos
    function generarReporteRapido(tipo) {
        let fechaInicio, fechaFin;
        const hoy = new Date();

        switch (tipo) {
            case "hoy":
                fechaInicio = fechaFin = hoy.toISOString().split("T")[0];
                break;
            case "semana":
                const inicioSemana = new Date(hoy.setDate(hoy.getDate() - hoy.getDay()));
                fechaInicio = inicioSemana.toISOString().split("T")[0];
                fechaFin = new Date().toISOString().split("T")[0];
                break;
            case "mes":
                fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split("T")[0];
                fechaFin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split("T")[0];
                break;
            case "tardanzas":
                fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split("T")[0];
                fechaFin = new Date().toISOString().split("T")[0];
                break;
        }

        const url = `/rrhh/reporte?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&formato=html${tipo === "tardanzas" ? "&solo_tardanzas=1" : ""}`;
        window.open(url, "_blank");
    }

    // Exportar asistencias de hoy
    function exportarAsistenciasHoy() {
        const hoy = new Date().toISOString().split("T")[0];
        window.location.href = `/rrhh/reporte?fecha_inicio=${hoy}&fecha_fin=${hoy}&formato=csv`;
    }

    // Ver detalle de empleado
    function verDetalleEmpleado(email) {
        mostrarAlerta("Ver detalle del empleado: " + email + "\n\nEsta función abrirá el perfil completo del empleado con su historial de asistencias.", 'info');
    }

    // Sistema de actualizaciones en tiempo real
    let actualizacionEnCurso = false;
    let intervalId = null;

    function actualizarDashboard() {
        if (actualizacionEnCurso) return;

        actualizacionEnCurso = true;
        const btnActualizar = document.getElementById('btn-actualizar');
        const iconoOriginal = btnActualizar.innerHTML;

        btnActualizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        btnActualizar.disabled = true;

        fetch('/rrhh/api/estadisticas-tiempo-real', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarEstadisticas(data.estadisticas);
                    actualizarAlertas(data.alertas);
                    document.getElementById('last-update').textContent = new Date().toLocaleTimeString();

                    // Mostrar notificación si hay nuevas alertas críticas
                    if (data.alertas && data.alertas.length > 0) {
                        data.alertas.forEach(alerta => {
                            if (alerta.critica) {
                                mostrarNotificacionTiempoReal(alerta);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error actualizando dashboard:', error);
                mostrarAlerta('Error al actualizar los datos. Inténtalo de nuevo.', 'error');
            })
            .finally(() => {
                actualizacionEnCurso = false;
                btnActualizar.innerHTML = iconoOriginal;
                btnActualizar.disabled = false;
            });
    }

    function actualizarEstadisticas(estadisticas) {
        // Actualizar números con animación
        animarNumero('stat-empleados', estadisticas.total_empleados);
        animarNumero('stat-presentes', estadisticas.presentes_hoy);
        animarNumero('stat-tardanzas', estadisticas.tardanzas_hoy);
        animarNumero('stat-ausentes', estadisticas.ausentes_hoy);

        // Actualizar colores de estado
        actualizarColorEstado('stat-tardanzas', estadisticas.tardanzas_hoy, 'warning');
        actualizarColorEstado('stat-ausentes', estadisticas.ausentes_hoy, 'danger');
    }

    function animarNumero(elementId, nuevoValor) {
        const elemento = document.querySelector(`#${elementId} .stat-number`);
        const valorActual = parseInt(elemento.textContent) || 0;

        if (valorActual !== nuevoValor) {
            elemento.style.transform = 'scale(1.1)';
            elemento.style.transition = 'transform 0.3s ease';

            setTimeout(() => {
                elemento.textContent = nuevoValor;
                elemento.style.transform = 'scale(1)';
            }, 150);
        }
    }

    function actualizarColorEstado(elementId, valor, tipoClase) {
        const elemento = document.getElementById(elementId);
        if (valor > 0) {
            elemento.classList.add(tipoClase);
            elemento.classList.add('pulse');
        } else {
            elemento.classList.remove(tipoClase);
            elemento.classList.remove('pulse');
        }
    }

    function actualizarAlertas(alertas) {
        const contenedorAlertas = document.querySelector('.alertas-tiempo-real');
        if (!contenedorAlertas) return;

        // Limpiar alertas existentes
        contenedorAlertas.innerHTML = '';

        if (alertas && alertas.length > 0) {
            alertas.forEach(alerta => {
                const alertaDiv = document.createElement('div');
                alertaDiv.className = `alert alert-${alerta.tipo} alert-dismissible fade show`;
                alertaDiv.innerHTML = `
                    <i class="fas fa-${alerta.icono}"></i>
                    <strong>${alerta.titulo}</strong> ${alerta.mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                contenedorAlertas.appendChild(alertaDiv);
            });
        }
    }

    function mostrarNotificacionTiempoReal(alerta) {
        // Usar Notification API si está disponible
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(`${alerta.titulo}`, {
                body: alerta.mensaje,
                icon: '/public/css/img/logo.png',
                tag: 'asistencia-alert',
                requireInteraction: true
            });
        }
    }

    function iniciarActualizacionesAutomaticas() {
        // Actualizar cada 30 segundos
        intervalId = setInterval(actualizarDashboard, 30000);
    }

    function detenerActualizacionesAutomaticas() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    // Solicitar permisos de notificación
    function solicitarPermisosNotificacion() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // Inicializar cuando la página se carga
    document.addEventListener('DOMContentLoaded', function() {
        solicitarPermisosNotificacion();
        iniciarActualizacionesAutomaticas();

        // Detener actualizaciones cuando la página se oculta
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                detenerActualizacionesAutomaticas();
            } else {
                iniciarActualizacionesAutomaticas();
            }
        });
    });

    // CSS para animaciones
    const style = document.createElement('style');
    style.textContent = `
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .stat-trend {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        
        .update-indicator {
            text-align: center;
            padding: 0.5rem;
            background: rgba(0,0,0,0.05);
            border-radius: 0.25rem;
        }
        
        .alertas-tiempo-real {
            position: relative;
            z-index: 1000;
        }
    `;
    document.head.appendChild(style);

    // Inicializar DataTables cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            // Tabla de asistencias de hoy
            $('#tabla-asistencias-hoy').DataTable({
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

            // Tabla de empleados con tardanzas
            $('#tabla-tardanzas').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });

            // Tabla de reporte semanal
            $('#tabla-semanal').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "searching": false,
                "ordering": false,
                "info": false,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });
        }
    });
</script>