<?php

/**
 * Componente Sidebar del Sistema
 * Sistema de Control de Asistencia
 */

// Obtener el rol del usuario
$rol = $usuario['rol'] ?? 'empleado';
$paginaActual = $_SERVER['REQUEST_URI'] ?? '';
?>

<aside class="sidebar" id="sidebar">
    <!-- Logo del Sistema -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-building"></i>
            <span class="sidebar-title">Sistema de Asistencia</span>
        </div>
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <ul class="sidebar-menu">
        <!-- Menú común para todos los roles -->
        <li>
            <a href="/dashboard" class="<?php echo (strpos($paginaActual, '/dashboard') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if ($rol === 'admin'): ?>
            <!-- Menú específico para Administradores -->
            <li>
                <a href="/admin/usuarios" class="<?php echo (strpos($paginaActual, '/admin/usuarios') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Gestión de Usuarios</span>
                </a>
            </li>
            <li>
                <a href="/admin/dispositivos" class="<?php echo (strpos($paginaActual, '/admin/dispositivos') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-microchip"></i>
                    <span>Dispositivos</span>
                </a>
            </li>
            <li>
                <a href="/admin/tarjetas" class="<?php echo (strpos($paginaActual, '/admin/tarjetas') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-id-card"></i>
                    <span>Tarjetas RFID</span>
                </a>
            </li>
            <li>
                <a href="/admin/reportes" class="<?php echo (strpos($paginaActual, '/admin/reportes') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes</span>
                </a>
            </li>
            <li>
                <a href="/admin/configuracion" class="<?php echo (strpos($paginaActual, '/admin/configuracion') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </li>
            <li>
                <a href="/admin/logs" class="<?php echo (strpos($paginaActual, '/admin/logs') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-list-alt"></i>
                    <span>Logs del Sistema</span>
                </a>
            </li>

        <?php elseif ($rol === 'rrhh'): ?>
            <!-- Menú específico para RRHH -->
            <li>
                <a href="/rrhh/empleados" class="<?php echo (strpos($paginaActual, '/rrhh/empleados') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Empleados</span>
                </a>
            </li>
            <li>
                <a href="/rrhh/asistencias" class="<?php echo (strpos($paginaActual, '/rrhh/asistencias') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Control de Asistencias</span>
                </a>
            </li>
            <li>
                <a href="/rrhh/reportes" class="<?php echo (strpos($paginaActual, '/rrhh/reportes') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Reportes</span>
                </a>
            </li>
            <li>
                <a href="/rrhh/horarios" class="<?php echo (strpos($paginaActual, '/rrhh/horarios') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i>
                    <span>Gestión de Horarios</span>
                </a>
            </li>
            <li>
                <a href="/rrhh/permisos" class="<?php echo (strpos($paginaActual, '/rrhh/permisos') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-times"></i>
                    <span>Permisos y Ausencias</span>
                </a>
            </li>

        <?php else: ?>
            <!-- Menú específico para Empleados -->
            <li>
                <a href="/empleado/asistencias" class="<?php echo (strpos($paginaActual, '/empleado/asistencias') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Mis Asistencias</span>
                </a>
            </li>
            <li>
                <a href="/empleado/horarios" class="<?php echo (strpos($paginaActual, '/empleado/horarios') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i>
                    <span>Mi Horario</span>
                </a>
            </li>
            <li>
                <a href="/empleado/permisos" class="<?php echo (strpos($paginaActual, '/empleado/permisos') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Solicitar Permiso</span>
                </a>
            </li>
            <li>
                <a href="/empleado/perfil" class="<?php echo (strpos($paginaActual, '/empleado/perfil') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>Mi Perfil</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Opciones comunes para todos los roles -->
        <li>
            <a href="/ayuda" class="<?php echo (strpos($paginaActual, '/ayuda') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i>
                <span>Ayuda</span>
            </a>
        </li>

        <?php if ($rol === 'admin' || $rol === 'rrhh'): ?>
            <li>
                <a href="/soporte" class="<?php echo (strpos($paginaActual, '/soporte') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-life-ring"></i>
                    <span>Soporte Técnico</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>