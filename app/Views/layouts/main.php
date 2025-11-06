<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Panel Administrativo'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS (opcional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Estilos principales -->
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="layout-container">
        <!-- Sidebar Component -->
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Content Wrapper -->
        <div class="content-wrapper">
            <!-- Header Component -->
            <?php include __DIR__ . '/header.php'; ?>
            
            <!-- Main Content Area -->
            <div class="main-content" id="main-content">
                <div class="container">
                    <div class="breadcrumb">
                        <strong>Panel <?php echo ucfirst($usuario['rol'] ?? 'Usuario'); ?></strong> / <?php echo $seccion ?? 'Dashboard'; ?>
                    </div>
                    
                    <div class="content">
                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($mensaje); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php echo $contenido; ?>
                    </div>
                </div>
            </div>
            
            <!-- Footer Component -->
            <?php include __DIR__ . '/footer.php'; ?>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- JavaScript principal -->
    <script src="js/main.js"></script>
</body>
</html>