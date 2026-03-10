<?php
require_once dirname(__DIR__) . '/config.php';
requiereAuth();

// Obtener datos del usuario actual
$usuario_actual = getCurrentUser();

// Si no hay usuario (por alguna razón), usar valores por defecto
if (!$usuario_actual) {
    $usuario_actual = [
        'id' => 0,
        'nombre' => 'Usuario',
        'email' => '',
        'rol' => 'usuario'
    ];
}

// Obtener iniciales para el avatar
$nombre_completo = $usuario_actual['nombre'];
$iniciales = strtoupper(substr($nombre_completo, 0, 1));
if (strpos($nombre_completo, ' ') !== false) {
    $partes = explode(' ', $nombre_completo);
    $iniciales = strtoupper(substr($partes[0], 0, 1) . substr(end($partes), 0, 1));
}

// Contar notificaciones reales
$conn = getDB();
$notificaciones = [];
$notificaciones_no_leidas = 0;

if ($conn) {
    // Productos con stock bajo
    $result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock_actual <= stock_minimo AND stock_actual > 0 AND activo = 1");
    if ($result) {
        $stock_bajo_count = $result->fetch_assoc()['total'];
        $notificaciones_no_leidas += $stock_bajo_count;
        
        if ($stock_bajo_count > 0) {
            $notificaciones[] = [
                'tipo' => 'stock',
                'icono' => 'fa-box',
                'color' => 'bg-yellow-100',
                'titulo' => 'Stock bajo',
                'mensaje' => $stock_bajo_count . ' productos necesitan reabastecimiento',
                'tiempo' => 'Ahora',
                'enlace' => url('dashboard/inventario/stock_bajo.php')
            ];
        }
    }
    
    // Productos agotados
    $result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock_actual = 0 AND activo = 1");
    if ($result) {
        $agotados_count = $result->fetch_assoc()['total'];
        $notificaciones_no_leidas += $agotados_count;
        
        if ($agotados_count > 0) {
            $notificaciones[] = [
                'tipo' => 'agotado',
                'icono' => 'fa-times-circle',
                'color' => 'bg-red-100',
                'titulo' => 'Productos agotados',
                'mensaje' => $agotados_count . ' productos están sin stock',
                'tiempo' => 'Ahora',
                'enlace' => url('dashboard/inventario/stock_bajo.php?tipo=agotado')
            ];
        }
    }
    
    // Ventas del día
    $hoy = date('Y-m-d');
    $result = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as total_ventas FROM ventas WHERE DATE(fecha_venta) = '$hoy'");
    if ($result) {
        $ventas_hoy = $result->fetch_assoc();
        if ($ventas_hoy['total'] > 0) {
            $notificaciones[] = [
                'tipo' => 'venta',
                'icono' => 'fa-shopping-cart',
                'color' => 'bg-green-100',
                'titulo' => 'Ventas del día',
                'mensaje' => $ventas_hoy['total'] . ' ventas por $' . number_format($ventas_hoy['total_ventas'], 2),
                'tiempo' => 'Hoy',
                'enlace' => url('dashboard/ventas/historial.php?fecha_inicio=' . $hoy . '&fecha_fin=' . $hoy)
            ];
        }
    }
}

// Incluir configuración de CSS
include_once dirname(__DIR__) . '/assets/css/pages-config.php';
$version = time();
$current_uri = $_SERVER['REQUEST_URI'];
$page_css = getPageCss($current_uri, $version);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    
    <!-- Layout -->
    <link rel="stylesheet" href="<?php echo asset('css/layout/header.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/layout/sidebar.css'); ?>">
    
    <!-- Componentes -->
    <link rel="stylesheet" href="<?php echo asset('css/components/buttons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/cards.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/tables.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/forms.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/modals.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/alerts.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/badges.css'); ?>">
    
    <!-- Estilos adicionales del header -->
    <link rel="stylesheet" href="<?php echo asset('css/layout/header-estilos.css'); ?>?v=<?php echo $version; ?>">
    
    <!-- CSS específico de la página -->
    <?php foreach ($page_css as $css_file): ?>
        <link rel="stylesheet" href="<?php echo asset('css/pages/' . $css_file); ?>?v=<?php echo $version; ?>">
    <?php endforeach; ?>
    
    
     <!-- Chart.js - Cargar ANTES de otros scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header fijo -->
    <header class="header">
        <div class="header-container">
            <div class="header-content">
                
                <!-- Lado izquierdo: Menú y Logo -->
                <div class="header-left">
                    <!-- Botón menú -->
                    <button id="menuToggle" class="menu-button" data-tooltip="Menú">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <!-- Logo -->
                    <div class="logo">
                        <h1><?php echo APP_NAME; ?></h1>
                    </div>
                </div>
                
                <!-- Centro: Buscador -->
                <div class="header-search">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               placeholder="Buscar productos, ventas, clientes..." 
                               id="globalSearch"
                               class="search-input">
                    </div>
                </div>
                
                <!-- Lado derecho: Acciones -->
                <div class="header-right">
                    <!-- Pantalla completa -->
                    <button id="fullscreenBtn" class="icon-button" data-tooltip="Pantalla completa">
                        <i class="fas fa-expand" id="fullscreenIcon"></i>
                    </button>
                    
                    <!-- Notificaciones -->
                    <div class="relative" id="notificationContainer">
                        <button id="notificationBtn" class="icon-button">
                            <i class="fas fa-bell"></i>
                            <?php if ($notificaciones_no_leidas > 0): ?>
                                <span class="notification-badge"></span>
                            <?php endif; ?>
                        </button>
                        
                        <!-- Dropdown notificaciones -->
                        <div id="notificationDropdown" class="dropdown">
                            <div class="dropdown-header">
                                <div class="dropdown-header-content">
                                    <h4 class="dropdown-title">Notificaciones</h4>
                                    <span class="dropdown-link" onclick="marcarTodasLeidas()">Marcar todas</span>
                                </div>
                            </div>
                            
                            <div class="dropdown-content">
                                <?php if (count($notificaciones) > 0): ?>
                                    <?php foreach ($notificaciones as $notif): ?>
                                    <a href="<?php echo $notif['enlace']; ?>" class="dropdown-item <?php echo $notif['color']; ?>" style="text-decoration: none;">
                                        <div class="item-icon <?php echo $notif['color']; ?>">
                                            <i class="fas <?php echo $notif['icono']; ?>"></i>
                                        </div>
                                        <div class="item-content">
                                            <p class="item-title">
                                                <strong><?php echo $notif['titulo']; ?>:</strong> <?php echo $notif['mensaje']; ?>
                                            </p>
                                            <p class="item-time">
                                                <i class="far fa-clock"></i> <?php echo $notif['tiempo']; ?>
                                            </p>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="dropdown-item" style="text-align: center; color: var(--gray-500);">
                                        <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--success);"></i>
                                        <p>No hay notificaciones</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="dropdown-footer">
                                <a href="<?php echo url('dashboard/notificaciones.php'); ?>">Ver todas las notificaciones</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Perfil de usuario -->
                    <div class="relative" id="userContainer">
                        <button id="userMenuBtn" class="user-menu-button">
                            <div class="user-avatar">
                                <?php echo $iniciales; ?>
                            </div>
                            <div class="user-info">
                                <p class="user-name"><?php echo h($usuario_actual['nombre']); ?></p>
                                <p class="user-role"><?php echo ucfirst($usuario_actual['rol']); ?></p>
                            </div>
                            <i class="fas fa-chevron-down chevron-icon"></i>
                        </button>
                        
                        <!-- Dropdown usuario -->
                        <div id="userDropdown" class="dropdown user-dropdown">
                            <div class="user-header">
                                <p><?php echo h($usuario_actual['nombre']); ?></p>
                                <small><?php echo h($usuario_actual['email']); ?></small>
                            </div>
                            
                            <a href="<?php echo url('dashboard/perfil.php'); ?>">
                                <i class="fas fa-user"></i>
                                Mi Perfil
                            </a>
                            
                            <?php if (hasRole(['admin', 'super_admin'])): ?>
                            <a href="<?php echo url('dashboard/configuracion/index.php'); ?>">
                                <i class="fas fa-cog"></i>
                                Configuración
                            </a>
                            
                            <a href="<?php echo url('dashboard/configuracion/empresa.php'); ?>">
                                <i class="fas fa-building"></i>
                                Datos de la Empresa
                            </a>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <a href="<?php echo url('dashboard/ayuda.php'); ?>">
                                <i class="fas fa-question-circle"></i>
                                Ayuda
                            </a>
                            
                            <a href="<?php echo url('dashboard/logout.php'); ?>" class="logout-link" onclick="return confirm('¿Cerrar sesión?')">
                                <i class="fas fa-sign-out-alt"></i>
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenedor principal con sidebar -->
    <div class="app-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content" id="mainContent">
        
<!-- Scripts del header -->
<script src="<?php echo asset('js/header.js'); ?>?v=<?php echo $version; ?>"></script>