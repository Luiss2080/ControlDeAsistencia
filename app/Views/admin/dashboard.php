<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'Panel de Administración' ?> - Control de Asistencia</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            border-radius: 0.5rem;
            margin: 0.2rem 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 0;
            border-radius: 1rem;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-card-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        }
        
        .stat-card-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-check text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2">Control Asistencia</h5>
                        <small class="text-white-50">Panel de Administración</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/usuarios">
                                <i class="bi bi-people me-2"></i>
                                Gestión de Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dispositivos">
                                <i class="bi bi-hdd-network me-2"></i>
                                Dispositivos ESP32
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/tarjetas">
                                <i class="bi bi-credit-card-2-front me-2"></i>
                                Tarjetas RFID
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/registros">
                                <i class="bi bi-clock-history me-2"></i>
                                Registros de Asistencia
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/reportes">
                                <i class="bi bi-graph-up me-2"></i>
                                Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/configuracion">
                                <i class="bi bi-gear me-2"></i>
                                Configuración
                            </a>
                        </li>
                        <hr class="text-white-50">
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard de Administración
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="bi bi-download me-1"></i>
                                Exportar
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($data['user']->nombres ?? 'Usuario') ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/admin/configuracion">
                                    <i class="bi bi-gear me-1"></i> Configuración
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout">
                                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas principales -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="bi bi-people-fill" style="font-size: 2.5rem;"></i>
                                <h3 class="mt-2"><?= $data['estadisticas']['total_empleados'] ?? 0 ?></h3>
                                <p class="mb-0">Total Empleados</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card-success">
                            <div class="card-body text-center">
                                <i class="bi bi-person-check-fill" style="font-size: 2.5rem;"></i>
                                <h3 class="mt-2"><?= $data['estadisticas']['empleados_presentes_hoy'] ?? 0 ?></h3>
                                <p class="mb-0">Presentes Hoy</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card-info">
                            <div class="card-body text-center">
                                <i class="bi bi-clock-fill" style="font-size: 2.5rem;"></i>
                                <h3 class="mt-2"><?= $data['estadisticas']['marcaciones_hoy'] ?? 0 ?></h3>
                                <p class="mb-0">Marcaciones Hoy</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 2.5rem;"></i>
                                <h3 class="mt-2"><?= $data['estadisticas']['llegadas_tardias_hoy'] ?? 0 ?></h3>
                                <p class="mb-0">Llegadas Tardías</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos y estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-8 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-graph-up me-2"></i>
                                    Asistencias de la Semana
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="asistenciasChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-hdd-network me-2"></i>
                                    Estado de Dispositivos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Online</span>
                                        <span class="badge bg-success"><?= $data['estadisticas']['dispositivos_online'] ?? 0 ?></span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Offline</span>
                                        <span class="badge bg-danger">0</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Mantenimiento</span>
                                        <span class="badge bg-warning">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimas marcaciones -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Últimas Marcaciones
                                </h5>
                                <a href="/admin/registros" class="btn btn-outline-primary btn-sm">
                                    Ver todas <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Empleado</th>
                                                <th>Tipo</th>
                                                <th>Fecha y Hora</th>
                                                <th>Dispositivo</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($data['estadisticas']['ultimas_marcaciones'])): ?>
                                                <?php foreach ($data['estadisticas']['ultimas_marcaciones'] as $marcacion): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-circle bg-primary text-white me-2">
                                                                    <?= substr($marcacion['nombre_usuario'] ?? 'U', 0, 1) ?>
                                                                </div>
                                                                <?= htmlspecialchars($marcacion['nombre_usuario'] ?? 'Usuario') ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?= ($marcacion['tipo'] ?? 'entrada') === 'entrada' ? 'bg-success' : 'bg-info' ?>">
                                                                <i class="bi bi-<?= ($marcacion['tipo'] ?? 'entrada') === 'entrada' ? 'box-arrow-in-right' : 'box-arrow-left' ?> me-1"></i>
                                                                <?= ucfirst($marcacion['tipo'] ?? 'entrada') ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('d/m/Y H:i', strtotime($marcacion['fecha_hora'] ?? 'now')) ?></td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <i class="bi bi-geo-alt me-1"></i>
                                                                <?= htmlspecialchars($marcacion['dispositivo_nombre'] ?? 'N/A') ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?= ($marcacion['valido'] ?? true) ? 'success' : 'danger' ?>">
                                                                <?= ($marcacion['valido'] ?? true) ? 'Válida' : 'Inválida' ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                        <p class="mt-2">No hay marcaciones recientes</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    Acciones Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <button class="btn btn-outline-primary w-100" onclick="window.location.href='/admin/usuarios'">
                                            <i class="bi bi-person-plus me-2"></i>
                                            Agregar Usuario
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button class="btn btn-outline-success w-100" onclick="window.location.href='/admin/dispositivos'">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Registrar Dispositivo
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button class="btn btn-outline-info w-100" onclick="window.location.href='/admin/tarjetas'">
                                            <i class="bi bi-credit-card me-2"></i>
                                            Asignar Tarjeta
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button class="btn btn-outline-warning w-100" onclick="window.location.href='/admin/reportes'">
                                            <i class="bi bi-file-earmark-text me-2"></i>
                                            Generar Reporte
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configurar gráfico de asistencias
        const ctx = document.getElementById('asistenciasChart').getContext('2d');
        const asistenciasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Asistencias',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Actualizar estadísticas cada 30 segundos
        setInterval(function() {
            // Aquí podrías hacer una llamada AJAX para actualizar las estadísticas
            console.log('Actualizando estadísticas...');
        }, 30000);
    </script>
</body>
</html>