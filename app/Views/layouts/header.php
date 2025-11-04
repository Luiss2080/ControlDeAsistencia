<?php
/**
 * Componente Header del Sistema
 * Sistema de Control de Asistencia
 */
?>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-building"></i>
            <span>Sistema de Asistencia</span>
        </div>
        <div class="user-info">
            <span>Bienvenido, <?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></span>
            <span class="badge"><?php echo strtoupper($usuario['rol'] ?? 'USER'); ?></span>
            <a href="/logout" class="logout-btn" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?')">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</header>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebar && mainContent) {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
        
        // En móviles, mostrar/ocultar el sidebar
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('show');
        }
    }
}
</script>