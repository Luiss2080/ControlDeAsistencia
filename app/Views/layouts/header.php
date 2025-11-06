<?php
/**
 * Componente Header del Sistema
 * Sistema de Control de Asistencia
 */
?>
<header class="header">
    <div class="header-content">
        <div class="header-breadcrumb">
            <span><?php echo $titulo ?? 'Panel'; ?></span>
        </div>
        <div class="user-info">
            <span>Bienvenido, <?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></span>
            <span class="badge"><?php echo strtoupper($usuario['rol'] ?? 'USER'); ?></span>
            <a href="logout" class="logout-btn" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?')">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</header>