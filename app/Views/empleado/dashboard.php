<?php
$contenido = '
<!-- Estado del día -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; text-align: center;">
    <h2 style="margin-bottom: 15px;">👋 ¡Hola, ' . htmlspecialchars($usuario['nombre']) . '!</h2>
    <div style="font-size: 18px; margin-bottom: 10px;">📅 ' . date('l, d \d\e F \d\e Y') . '</div>';

if ($asistencia_hoy) {
    $estado_emoji = '';
    $estado_texto = '';
    $estado_color = '';
    
    switch ($asistencia_hoy['estado']) {
        case 'puntual':
            $estado_emoji = '✅';
            $estado_texto = 'Llegaste puntual';
            $estado_color = '#27ae60';
            break;
        case 'tardanza':
            $estado_emoji = '⚠️';
            $estado_texto = 'Llegaste tarde';
            $estado_color = '#f39c12';
            break;
    }
    
    $contenido .= '
    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-top: 15px;">
        <div style="font-size: 24px; margin-bottom: 5px;">' . $estado_emoji . '</div>
        <div style="font-size: 16px; font-weight: bold;">' . $estado_texto . '</div>
        <div style="font-size: 14px; opacity: 0.9;">Hora de entrada: ' . date('H:i', strtotime($asistencia_hoy['hora_entrada'])) . '</div>
    </div>';
} else {
    $contenido .= '
    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-top: 15px;">
        <div style="font-size: 24px; margin-bottom: 5px;">❓</div>
        <div style="font-size: 16px; font-weight: bold;">Aún no has marcado asistencia hoy</div>
        <div style="font-size: 14px; opacity: 0.9;">Recuerda pasar tu tarjeta por el lector</div>
    </div>';
}

$contenido .= '
</div>

<!-- Estadísticas del mes -->
<div class="stats-grid">
    <div class="stat-card success">
        <div class="stat-number">' . ($stats['mes_actual']['total_asistencias'] ?? 0) . '</div>
        <div class="stat-label">📅 Días Trabajados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">' . ($stats['mes_actual']['puntuales'] ?? 0) . '</div>
        <div class="stat-label">✅ Días Puntuales</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-number">' . ($stats['mes_actual']['tardanzas'] ?? 0) . '</div>
        <div class="stat-label">⚠️ Tardanzas</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-number">' . ($stats['racha_puntual'] ?? 0) . '</div>
        <div class="stat-label">🔥 Racha Puntual</div>
    </div>
</div>

<!-- Información personal y tarjeta -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="color: #2c3e50; margin-bottom: 15px;">👤 Mi Información</h3>
        <div style="line-height: 1.8;">
            <p><strong>📧 Email:</strong> ' . htmlspecialchars($usuario['email']) . '</p>
            <p><strong>💼 Puesto:</strong> ' . htmlspecialchars($usuario['puesto'] ?? 'No especificado') . '</p>
            <p><strong>📞 Teléfono:</strong> ' . htmlspecialchars($usuario['telefono'] ?? 'No registrado') . '</p>
            <p><strong>🕐 Promedio de Llegada:</strong> ' . ($stats['promedio_llegada'] ?? '00:00') . '</p>
        </div>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="color: #2c3e50; margin-bottom: 15px;">🏷️ Mi Tarjeta RFID</h3>';

if ($tarjeta) {
    $estado_tarjeta = $tarjeta['activa'] ? '🟢 Activa' : '🔴 Inactiva';
    $contenido .= '
        <div style="line-height: 1.8;">
            <p><strong>🆔 UID:</strong> <code>' . htmlspecialchars($tarjeta['uid']) . '</code></p>
            <p><strong>📊 Estado:</strong> ' . $estado_tarjeta . '</p>
            <p><strong>📅 Registrada:</strong> ' . date('d/m/Y', strtotime($tarjeta['fecha_registro'])) . '</p>
        </div>
        <div style="background: #e8f5e8; padding: 10px; border-radius: 6px; margin-top: 15px; border-left: 4px solid #27ae60;">
            <small style="color: #27ae60;">✅ Tu tarjeta está configurada correctamente</small>
        </div>';
} else {
    $contenido .= '
        <div style="background: #ffeaa7; padding: 15px; border-radius: 8px; border-left: 4px solid #fdcb6e;">
            <p style="color: #e17055; margin: 0;"><strong>⚠️ Sin tarjeta asignada</strong></p>
            <p style="color: #636e72; margin: 5px 0 0 0; font-size: 14px;">
                Contacta al administrador para que te asigne una tarjeta RFID
            </p>
        </div>';
}

$contenido .= '
    </div>
</div>

<!-- Navegación de pestañas -->
<div class="nav-tabs">
    <button class="nav-tab active" onclick="cambiarTab(event, \'tab-recientes\')">📊 Asistencias Recientes</button>
    <button class="nav-tab" onclick="cambiarTab(event, \'tab-estadisticas\')">📈 Mis Estadísticas</button>
    <button class="nav-tab" onclick="cambiarTab(event, \'tab-historial\')">📋 Ver Historial Completo</button>
</div>

<!-- TAB: Asistencias Recientes -->
<div id="tab-recientes" class="tab-content active">
    <h3>📊 Mis Últimas Asistencias</h3>
    
    <table class="table">
        <thead>
            <tr>
                <th>📅 Fecha</th>
                <th>🕐 Hora Entrada</th>
                <th>📋 Estado</th>
                <th>📱 Dispositivo</th>
                <th>📝 Observaciones</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($asistencias_recientes)) {
    foreach ($asistencias_recientes as $asistencia) {
        $estado_clase = '';
        $estado_texto = '';
        
        switch ($asistencia['estado']) {
            case 'puntual':
                $estado_clase = 'success';
                $estado_texto = '✅ Puntual';
                break;
            case 'tardanza':
                $estado_clase = 'warning';
                $estado_texto = '⚠️ Tardanza';
                break;
            case 'ausente':
                $estado_clase = 'danger';
                $estado_texto = '❌ Ausente';
                break;
        }
        
        $contenido .= '
            <tr>
                <td><strong>' . date('d/m/Y', strtotime($asistencia['fecha'])) . '</strong><br>
                    <small style="color: #666;">' . date('l', strtotime($asistencia['fecha'])) . '</small></td>
                <td>' . ($asistencia['hora_entrada'] ? '<strong>' . date('H:i', strtotime($asistencia['hora_entrada'])) . '</strong>' : '-') . '</td>
                <td><span class="btn btn-' . $estado_clase . '">' . $estado_texto . '</span></td>
                <td>' . htmlspecialchars($asistencia['dispositivo_nombre'] ?? 'No registrado') . '</td>
                <td>' . htmlspecialchars($asistencia['observaciones'] ?? '') . '</td>
            </tr>';
    }
} else {
    $contenido .= '
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                    📋 No tienes registros de asistencia aún
                </td>
            </tr>';
}

$contenido .= '
        </tbody>
    </table>
</div>

<!-- TAB: Estadísticas -->
<div id="tab-estadisticas" class="tab-content">
    <h3>📈 Mis Estadísticas del Mes</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- Gráfico de puntualidad -->
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">🎯 Puntualidad</h4>
            <div style="text-align: center;">';

$total_asistencias = $stats['mes_actual']['total_asistencias'] ?? 0;
$puntuales = $stats['mes_actual']['puntuales'] ?? 0;
$porcentaje_puntualidad = $total_asistencias > 0 ? round(($puntuales / $total_asistencias) * 100, 1) : 0;

$color_porcentaje = '';
if ($porcentaje_puntualidad >= 95) $color_porcentaje = '#27ae60';
elseif ($porcentaje_puntualidad >= 85) $color_porcentaje = '#f39c12';
else $color_porcentaje = '#e74c3c';

$contenido .= '
                <div style="font-size: 48px; font-weight: bold; color: ' . $color_porcentaje . '; margin-bottom: 10px;">
                    ' . $porcentaje_puntualidad . '%
                </div>
                <p style="color: #666; margin: 0;">de puntualidad este mes</p>
                <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; margin-top: 15px;">
                    <small>✅ ' . $puntuales . ' puntuales de ' . $total_asistencias . ' asistencias</small>
                </div>
            </div>
        </div>
        
        <!-- Comparación con horario -->
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">⏰ Horario vs Realidad</h4>
            <div style="line-height: 2;">
                <p><strong>🎯 Hora de entrada:</strong> 08:00</p>
                <p><strong>📊 Mi promedio:</strong> ' . ($stats['promedio_llegada'] ?? '00:00') . '</p>';

if (isset($stats['mes_actual']['primera_llegada'])) {
    $contenido .= '<p><strong>🌅 Más temprano:</strong> ' . date('H:i', strtotime($stats['mes_actual']['primera_llegada'])) . '</p>';
}

if (isset($stats['mes_actual']['ultima_llegada'])) {
    $contenido .= '<p><strong>🌙 Más tarde:</strong> ' . date('H:i', strtotime($stats['mes_actual']['ultima_llegada'])) . '</p>';
}

$contenido .= '
            </div>
        </div>
        
        <!-- Logros y racha -->
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">🏆 Logros</h4>
            <div style="text-align: center;">';

$racha = $stats['racha_puntual'] ?? 0;

if ($racha >= 10) {
    $contenido .= '
                <div style="font-size: 48px; margin-bottom: 10px;">🔥</div>
                <p style="font-weight: bold; color: #e74c3c;">¡Racha increíble!</p>';
} elseif ($racha >= 5) {
    $contenido .= '
                <div style="font-size: 48px; margin-bottom: 10px;">⭐</div>
                <p style="font-weight: bold; color: #f39c12;">¡Excelente racha!</p>';
} elseif ($racha >= 3) {
    $contenido .= '
                <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
                <p style="font-weight: bold; color: #27ae60;">¡Buena racha!</p>';
} else {
    $contenido .= '
                <div style="font-size: 48px; margin-bottom: 10px;">🎯</div>
                <p style="font-weight: bold; color: #3498db;">¡Sigue así!</p>';
}

$contenido .= '
                <p style="color: #666; margin: 10px 0 0 0;">' . $racha . ' días puntuales consecutivos</p>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Ver Historial Completo -->
<div id="tab-historial" class="tab-content">
    <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 12px;">
        <div style="font-size: 64px; margin-bottom: 20px;">📋</div>
        <h3 style="color: #2c3e50; margin-bottom: 15px;">Historial Completo de Asistencias</h3>
        <p style="color: #666; margin-bottom: 25px;">
            Consulta tu historial completo con filtros por fecha y opciones de exportación
        </p>
        <button class="btn btn-primary" onclick="window.open(\'/empleado/historial\', \'_blank\')" style="font-size: 16px; padding: 12px 24px;">
            📊 Ver Historial Completo
        </button>
    </div>
</div>

<script>
// Actualizar página cada 5 minutos para mostrar cambios en tiempo real
setTimeout(function() {
    location.reload();
}, 300000); // 5 minutos

// Mostrar notificación si aún no ha marcado asistencia hoy
document.addEventListener("DOMContentLoaded", function() {';

if (!$asistencia_hoy && date('H') >= 8) { // Si son las 8 AM o más y no ha marcado
    $contenido .= '
    setTimeout(function() {
        if (confirm("⚠️ Recordatorio: Aún no has marcado asistencia hoy.\\n\\n¿Te gustaría ver información sobre cómo marcar asistencia?")) {
            alert("📱 Para marcar asistencia:\\n\\n1. Busca el lector RFID en tu oficina\\n2. Acerca tu tarjeta al lector\\n3. Espera el pitido de confirmación\\n\\n¡Tu asistencia se registrará automáticamente!");
        }
    }, 3000);';
}

$contenido .= '
});
</script>
';

$titulo = 'Mi Panel de Empleado';
$seccion = 'Dashboard Personal';
include_once __DIR__ . '/../layouts/main.php';
?>