<?php
/**
 * Dashboard del Empleado
 * Sistema de Control de Asistencia
 */
?>

<!-- Estado del día -->
<div class="welcome-header">
    <h2 class="welcome-title"><i class="fas fa-hand-wave"></i> ¡Hola, <?php echo htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']); ?>!</h2>
    <div class="welcome-date"><i class="fas fa-calendar-day"></i> <?php echo date('l, d \d\e F \d\e Y'); ?></div>
    
    <?php if ($asistencia_hoy): ?>
        <?php
        $estado_emoji = '';
        $estado_texto = '';
        $estado_color = '';
        
        switch ($asistencia_hoy['estado']) {
            case 'puntual':
                $estado_emoji = '<i class="fas fa-check-circle"></i>';
                $estado_texto = 'Llegaste puntual';
                $estado_color = '#27ae60';
                break;
            case 'tardanza':
                $estado_emoji = '<i class="fas fa-exclamation-triangle"></i>';
                $estado_texto = 'Llegaste tarde';
                $estado_color = '#f39c12';
                break;
        }
        ?>
        <div class="status-card">
            <div class="status-icon"><?php echo $estado_emoji; ?></div>
            <div class="status-text"><?php echo $estado_texto; ?></div>
            <div class="status-detail">Hora de entrada: <?php echo date('H:i', strtotime($asistencia_hoy['hora_entrada'])); ?></div>
        </div>
    <?php else: ?>
        <div class="status-card">
            <div class="status-icon"><i class="fas fa-question-circle"></i></div>
            <div class="status-text">Aún no has marcado asistencia hoy</div>
            <div class="status-detail">Recuerda pasar tu tarjeta por el lector</div>
        </div>
    <?php endif; ?>
</div>

<!-- Estadísticas del mes -->
<div class="stats-grid">
    <div class="stat-card success">
        <div class="stat-number"><?php echo ($stats['mes_actual']['total_asistencias'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-calendar-check"></i> Días Trabajados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo ($stats['mes_actual']['puntuales'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-check-circle"></i> Días Puntuales</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-number"><?php echo ($stats['mes_actual']['tardanzas'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-exclamation-triangle"></i> Tardanzas</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-number"><?php echo ($stats['racha_puntual'] ?? 0); ?></div>
        <div class="stat-label"><i class="fas fa-fire"></i> Racha Puntual</div>
    </div>
</div>

<!-- Información personal y tarjeta -->
<div class="info-grid">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-user"></i> Mi Información</h6>
        </div>
        <div class="card-body">
            <div class="info-line">
                <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                <p><strong><i class="fas fa-briefcase"></i> Puesto:</strong> <?php echo htmlspecialchars($usuario['puesto'] ?? 'No especificado'); ?></p>
                <p><strong><i class="fas fa-phone"></i> Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono'] ?? 'No registrado'); ?></p>
                <p><strong><i class="fas fa-clock"></i> Promedio de Llegada:</strong> <?php echo ($stats['promedio_llegada'] ?? '00:00'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-id-card"></i> Mi Tarjeta RFID</h6>
        </div>
        <div class="card-body">
            <?php if ($tarjeta): ?>
                <?php $estado_tarjeta = $tarjeta['activa'] ? '<i class="fas fa-circle text-success"></i> Activa' : '<i class="fas fa-circle text-danger"></i> Inactiva'; ?>
                <div class="info-line">
                    <p><strong><i class="fas fa-hashtag"></i> UID:</strong> <code><?php echo htmlspecialchars($tarjeta['uid']); ?></code></p>
                    <p><strong><i class="fas fa-signal"></i> Estado:</strong> <?php echo $estado_tarjeta; ?></p>
                    <p><strong><i class="fas fa-calendar-plus"></i> Registrada:</strong> <?php echo date('d/m/Y', strtotime($tarjeta['fecha_registro'])); ?></p>
                </div>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Tu tarjeta está configurada correctamente
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p class="text-no-margin"><strong><i class="fas fa-exclamation-triangle"></i> Sin tarjeta asignada</strong></p>
                    <p class="text-small-margin">
                        Contacta al administrador para que te asigne una tarjeta RFID
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Navegación de pestañas -->
<div class="nav-tabs">
    <button class="nav-tab active" onclick="cambiarTab(event, 'tab-recientes')">
        <i class="fas fa-chart-bar"></i> Asistencias Recientes
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-estadisticas')">
        <i class="fas fa-chart-line"></i> Mis Estadísticas
    </button>
    <button class="nav-tab" onclick="cambiarTab(event, 'tab-historial')">
        <i class="fas fa-history"></i> Ver Historial Completo
    </button>
</div>

<!-- TAB: Asistencias Recientes -->
<div id="tab-recientes" class="tab-content active">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-chart-bar"></i> Mis Últimas Asistencias</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-day"></i> Fecha</th>
                            <th><i class="fas fa-clock"></i> Hora Entrada</th>
                            <th><i class="fas fa-info-circle"></i> Estado</th>
                            <th><i class="fas fa-mobile-alt"></i> Dispositivo</th>
                            <th><i class="fas fa-sticky-note"></i> Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($asistencias_recientes)): ?>
                            <?php foreach ($asistencias_recientes as $asistencia): ?>
                                <?php
                                $estado_clase = '';
                                $estado_texto = '';
                                
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
                                        <strong><?php echo date('d/m/Y', strtotime($asistencia['fecha'])); ?></strong><br>
                                        <small class="text-muted"><?php echo date('l', strtotime($asistencia['fecha'])); ?></small>
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
                                    No tienes registros de asistencia aún
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Estadísticas -->
<div id="tab-estadisticas" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-chart-line"></i> Mis Estadísticas del Mes</h6>
        </div>
        <div class="card-body">
            <div class="grid-3-cols">
                <!-- Gráfico de puntualidad -->
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-target"></i> Puntualidad</h6>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $total_asistencias = $stats['mes_actual']['total_asistencias'] ?? 0;
                        $puntuales = $stats['mes_actual']['puntuales'] ?? 0;
                        $porcentaje_puntualidad = $total_asistencias > 0 ? round(($puntuales / $total_asistencias) * 100, 1) : 0;

                        $color_class = '';
                        if ($porcentaje_puntualidad >= 95) $color_class = 'percentage-green';
                        elseif ($porcentaje_puntualidad >= 85) $color_class = 'percentage-yellow';
                        else $color_class = 'percentage-red';
                        ?>
                        <div class="percentage-display <?php echo $color_class; ?>">
                            <?php echo $porcentaje_puntualidad; ?>%
                        </div>
                        <p class="text-muted">de puntualidad este mes</p>
                        <div class="stats-summary">
                            <small><i class="fas fa-check-circle text-success"></i> <?php echo $puntuales; ?> puntuales de <?php echo $total_asistencias; ?> asistencias</small>
                        </div>
                    </div>
                </div>
                
                <!-- Comparación con horario -->
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-clock"></i> Horario vs Realidad</h6>
                    </div>
                    <div class="card-body">
                        <div class="info-line">
                            <p><strong><i class="fas fa-target"></i> Hora de entrada:</strong> 08:00</p>
                            <p><strong><i class="fas fa-chart-line"></i> Mi promedio:</strong> <?php echo ($stats['promedio_llegada'] ?? '00:00'); ?></p>
                            <?php if (isset($stats['mes_actual']['primera_llegada'])): ?>
                                <p><strong><i class="fas fa-sunrise"></i> Más temprano:</strong> <?php echo date('H:i', strtotime($stats['mes_actual']['primera_llegada'])); ?></p>
                            <?php endif; ?>
                            <?php if (isset($stats['mes_actual']['ultima_llegada'])): ?>
                                <p><strong><i class="fas fa-sunset"></i> Más tarde:</strong> <?php echo date('H:i', strtotime($stats['mes_actual']['ultima_llegada'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Logros y racha -->
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-trophy"></i> Logros</h6>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $racha = $stats['racha_puntual'] ?? 0;
                        
                        if ($racha >= 10) {
                            echo '<div class="status-icon"><i class="fas fa-fire text-danger"></i></div>';
                            echo '<p class="text-danger font-weight-bold">¡Racha increíble!</p>';
                        } elseif ($racha >= 5) {
                            echo '<div class="status-icon"><i class="fas fa-star text-warning"></i></div>';
                            echo '<p class="text-warning font-weight-bold">¡Excelente racha!</p>';
                        } elseif ($racha >= 3) {
                            echo '<div class="status-icon"><i class="fas fa-check-circle text-success"></i></div>';
                            echo '<p class="text-success font-weight-bold">¡Buena racha!</p>';
                        } else {
                            echo '<div class="status-icon"><i class="fas fa-target text-primary"></i></div>';
                            echo '<p class="text-primary font-weight-bold">¡Sigue así!</p>';
                        }
                        ?>
                        <p class="text-muted"><?php echo $racha; ?> días puntuales consecutivos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Ver Historial Completo -->
<div id="tab-historial" class="tab-content">
    <div class="card">
        <div class="card-body feature-center">
            <div class="feature-icon-large">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3 class="feature-title">Historial Completo de Asistencias</h3>
            <p class="feature-description">
                Consulta tu historial completo con filtros por fecha y opciones de exportación
            </p>
            <button class="btn btn-primary btn-large" onclick="window.open('/empleado/historial', '_blank')">
                <i class="fas fa-chart-bar"></i> Ver Historial Completo
            </button>
        </div>
    </div>
</div>

<script>
// Actualizar página cada 5 minutos para mostrar cambios en tiempo real
setTimeout(function() {
    location.reload();
}, 300000); // 5 minutos

// Mostrar notificación si aún no ha marcado asistencia hoy
document.addEventListener("DOMContentLoaded", function() {
    <?php if (!$asistencia_hoy && date('H') >= 8): // Si son las 8 AM o más y no ha marcado ?>
    setTimeout(function() {
        if (confirm("⚠️ Recordatorio: Aún no has marcado asistencia hoy.\n\n¿Te gustaría ver información sobre cómo marcar asistencia?")) {
            mostrarAlerta("📱 Para marcar asistencia:\n\n1. Busca el lector RFID en tu oficina\n2. Acerca tu tarjeta al lector\n3. Espera el pitido de confirmación\n\n¡Tu asistencia se registrará automáticamente!", 'info');
        }
    }, 3000);
    <?php endif; ?>
});
</script>