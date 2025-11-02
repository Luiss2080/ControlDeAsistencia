<?php
$contenido = '
<div class="stats-grid">
    <div class="stat-card success">
        <div class="stat-number">' . ($stats['total_empleados'] ?? 0) . '</div>
        <div class="stat-label">Total Empleados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">' . ($stats['presentes_hoy'] ?? 0) . '</div>
        <div class="stat-label">Presentes Hoy</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-number">' . ($stats['tardanzas_hoy'] ?? 0) . '</div>
        <div class="stat-label">Tardanzas Hoy</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-number">' . ($stats['ausentes_hoy'] ?? 0) . '</div>
        <div class="stat-label">Ausentes Hoy</div>
    </div>
</div>

<div class="nav-tabs">
    <button class="nav-tab active" onclick="cambiarTab(event, \'tab-hoy\')">📅 Asistencias de Hoy</button>
    <button class="nav-tab" onclick="cambiarTab(event, \'tab-reportes\')">📊 Reportes</button>
    <button class="nav-tab" onclick="cambiarTab(event, \'tab-tardanzas\')">⚠️ Empleados con Tardanzas</button>
    <button class="nav-tab" onclick="cambiarTab(event, \'tab-semanal\')">📈 Reporte Semanal</button>
</div>

<!-- TAB: Asistencias de Hoy -->
<div id="tab-hoy" class="tab-content active">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>📅 Asistencias de Hoy (' . date('d/m/Y') . ')</h3>
        <div>
            <button class="btn btn-success" onclick="location.reload()">🔄 Actualizar</button>
            <button class="btn btn-primary" onclick="exportarAsistenciasHoy()">📥 Exportar</button>
        </div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>👤 Empleado</th>
                <th>🕐 Hora Entrada</th>
                <th>📋 Estado</th>
                <th>📱 Dispositivo</th>
                <th>📝 Observaciones</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($asistencias_hoy)) {
    foreach ($asistencias_hoy as $asistencia) {
        $estado_clase = '';
        $estado_texto = $asistencia['estado'];
        
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
                <td>
                    <strong>' . htmlspecialchars($asistencia['usuario_nombre']) . '</strong><br>
                    <small style="color: #666;">' . htmlspecialchars($asistencia['usuario_email']) . '</small>
                </td>
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
                    📋 No hay registros de asistencia para hoy
                </td>
            </tr>';
}

$contenido .= '
        </tbody>
    </table>
</div>

<!-- TAB: Reportes -->
<div id="tab-reportes" class="tab-content">
    <h3>📊 Generar Reportes de Asistencia</h3>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <form id="form-reporte" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="fecha_inicio">📅 Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="' . date('Y-m-01') . '" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="fecha_fin">📅 Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="' . date('Y-m-t') . '" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="formato">📄 Formato:</label>
                <select id="formato" name="formato">
                    <option value="html">📄 Ver en pantalla</option>
                    <option value="csv">📊 Descargar CSV</option>
                    <option value="json">🔗 JSON (API)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">📈 Generar Reporte</button>
            </div>
        </form>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
            <h4>📈 Reportes Rápidos</h4>
            <div style="margin-top: 15px;">
                <button class="btn btn-primary" onclick="generarReporteRapido(\'hoy\')">📅 Asistencias Hoy</button><br>
                <button class="btn btn-warning" onclick="generarReporteRapido(\'semana\')">📊 Esta Semana</button><br>
                <button class="btn btn-success" onclick="generarReporteRapido(\'mes\')">📈 Este Mes</button><br>
                <button class="btn btn-danger" onclick="generarReporteRapido(\'tardanzas\')">⚠️ Solo Tardanzas</button>
            </div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
            <h4>📋 Estadísticas del Mes</h4>
            <div style="margin-top: 15px;">
                <p><strong>📊 Total Tardanzas:</strong> ' . ($stats['tardanzas_mes'] ?? 0) . '</p>
                <p><strong>📈 Promedio Diario:</strong> ' . round(($stats['tardanzas_mes'] ?? 0) / date('j'), 1) . '</p>
                <p><strong>📅 Días Transcurridos:</strong> ' . date('j') . ' de ' . date('t') . '</p>
            </div>
        </div>
    </div>
</div>

<!-- TAB: Empleados con Tardanzas -->
<div id="tab-tardanzas" class="tab-content">
    <h3>⚠️ Empleados con Más Tardanzas Este Mes</h3>
    
    <table class="table">
        <thead>
            <tr>
                <th>👤 Empleado</th>
                <th>📧 Email</th>
                <th>⚠️ Total Tardanzas</th>
                <th>🔍 Acciones</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($empleados_tardanzas)) {
    foreach ($empleados_tardanzas as $empleado) {
        $contenido .= '
            <tr>
                <td><strong>' . htmlspecialchars($empleado['nombre']) . '</strong></td>
                <td>' . htmlspecialchars($empleado['email']) . '</td>
                <td>
                    <span class="btn btn-warning">⚠️ ' . $empleado['total_tardanzas'] . '</span>
                </td>
                <td>
                    <button class="btn btn-primary" onclick="verDetalleEmpleado(\'' . htmlspecialchars($empleado['email']) . '\')">
                        👁️ Ver Detalle
                    </button>
                </td>
            </tr>';
    }
} else {
    $contenido .= '
            <tr>
                <td colspan="4" style="text-align: center; padding: 30px; color: #666;">
                    ✅ ¡Excelente! No hay empleados con tardanzas este mes
                </td>
            </tr>';
}

$contenido .= '
        </tbody>
    </table>
</div>

<!-- TAB: Reporte Semanal -->
<div id="tab-semanal" class="tab-content">
    <h3>📈 Reporte de los Últimos 7 Días</h3>
    
    <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <table class="table">
            <thead>
                <tr>
                    <th>📅 Fecha</th>
                    <th>👥 Total Presentes</th>
                    <th>✅ Puntuales</th>
                    <th>⚠️ Tardanzas</th>
                    <th>📊 % Puntualidad</th>
                </tr>
            </thead>
            <tbody>';

if (!empty($reporte_semanal)) {
    foreach ($reporte_semanal as $dia) {
        $total = $dia['total_presentes'];
        $puntuales = $dia['puntuales'];
        $tardanzas = $dia['tardanzas'];
        $porcentaje = $total > 0 ? round(($puntuales / $total) * 100, 1) : 0;
        
        $porcentaje_clase = '';
        if ($porcentaje >= 90) $porcentaje_clase = 'success';
        elseif ($porcentaje >= 70) $porcentaje_clase = 'warning';
        else $porcentaje_clase = 'danger';
        
        $contenido .= '
            <tr>
                <td><strong>' . date('d/m/Y (l)', strtotime($dia['dia'])) . '</strong></td>
                <td>' . $total . '</td>
                <td><span class="btn btn-success">' . $puntuales . '</span></td>
                <td><span class="btn btn-warning">' . $tardanzas . '</span></td>
                <td><span class="btn btn-' . $porcentaje_clase . '">' . $porcentaje . '%</span></td>
            </tr>';
    }
} else {
    $contenido .= '
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                    📊 No hay datos para mostrar
                </td>
            </tr>';
}

$contenido .= '
            </tbody>
        </table>
    </div>
    
    <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #27ae60;">
        <h4 style="color: #27ae60; margin-bottom: 10px;">📈 Resumen Semanal</h4>
        <p><strong>📊 Promedio de Asistencia:</strong> ' . round(array_sum(array_column($reporte_semanal, 'total_presentes')) / max(1, count($reporte_semanal)), 1) . ' empleados/día</p>
        <p><strong>⚠️ Total Tardanzas:</strong> ' . array_sum(array_column($reporte_semanal, 'tardanzas')) . '</p>
        <p><strong>✅ Total Puntuales:</strong> ' . array_sum(array_column($reporte_semanal, 'puntuales')) . '</p>
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
    
    switch(tipo) {
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
    // Buscar ID del empleado por email (esto normalmente vendría del backend)
    alert("Ver detalle del empleado: " + email + "\\n\\nEsta función abrirá el perfil completo del empleado con su historial de asistencias.");
}
</script>
';

$titulo = 'Panel de Recursos Humanos';
$seccion = 'Dashboard RRHH';
include_once __DIR__ . '/../layouts/main.php';
?>