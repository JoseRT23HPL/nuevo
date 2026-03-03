<?php
// Configuración ya está incluida desde el principio
require_once dirname(__DIR__) . '/config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    
    <!-- CSS con rutas dinámicas -->
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/header.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/sidebar.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/ventas.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/historial.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/inventario.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/servicios.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/productos.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/producto-form.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/producto-ver.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/ajustar-stock.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/categorias.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/categoria-form.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/marcas.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/stock-bajo.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/entrada_rapida.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/movimientos.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/clientes.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/cliente-form.css'); ?>">
    


    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <span class="notification-badge"></span>
                        </button>
                        
                        <!-- Dropdown notificaciones -->
                        <div id="notificationDropdown" class="dropdown">
                            <div class="dropdown-header">
                                <div class="dropdown-header-content">
                                    <h4 class="dropdown-title">Notificaciones</h4>
                                    <span class="dropdown-link">Marcar todas</span>
                                </div>
                            </div>
                            
                            <div class="dropdown-content">
                                <!-- Notificación stock bajo -->
                                <div class="dropdown-item bg-blue-50">
                                    <div class="item-icon bg-yellow-100">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="item-content">
                                        <p class="item-title">
                                            <strong>Stock bajo:</strong> Martillo (5 unidades)
                                        </p>
                                        <p class="item-time">
                                            <i class="far fa-clock"></i> Hace 5 minutos
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Notificación venta -->
                                <div class="dropdown-item">
                                    <div class="item-icon bg-green-100">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="item-content">
                                        <p class="item-title">
                                            <strong>Nueva venta:</strong> #V001234 - $1,250.00
                                        </p>
                                        <p class="item-time">
                                            <i class="far fa-clock"></i> Hace 30 minutos
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Notificación usuario -->
                                <div class="dropdown-item">
                                    <div class="item-icon bg-purple-100">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="item-content">
                                        <p class="item-title">
                                            <strong>Nuevo usuario:</strong> María García
                                        </p>
                                        <p class="item-time">
                                            <i class="far fa-clock"></i> Hace 2 horas
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Notificación sistema -->
                                <div class="dropdown-item bg-blue-50">
                                    <div class="item-icon bg-red-100">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="item-content">
                                        <p class="item-title">
                                            <strong>Copia de seguridad:</strong> Completada
                                        </p>
                                        <p class="item-time">
                                            <i class="far fa-clock"></i> Hace 3 horas
                                        </p>
                                    </div>
                                </div>
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
                                A
                            </div>
                            <div class="user-info">
                                <p class="user-name">Admin User</p>
                                <p class="user-role">Administrador</p>
                            </div>
                            <i class="fas fa-chevron-down chevron-icon"></i>
                        </button>
                        
                        <!-- Dropdown usuario -->
                        <div id="userDropdown" class="dropdown user-dropdown">
                            <div class="user-header">
                                <p>Admin User</p>
                                <small>Administrador</small>
                            </div>
                            
                            <a href="<?php echo url('dashboard/perfil.php'); ?>">
                                <i class="fas fa-user"></i>
                                Mi Perfil
                            </a>
                            
                            <a href="<?php echo url('dashboard/configuracion/index.php'); ?>">
                                <i class="fas fa-cog"></i>
                                Configuración
                            </a>
                            
                            <a href="<?php echo url('dashboard/configuracion/empresa.php'); ?>">
                                <i class="fas fa-building"></i>
                                Datos de la Empresa
                            </a>
                            
                            <hr>
                            
                            <a href="<?php echo url('dashboard/ayuda.php'); ?>">
                                <i class="fas fa-question-circle"></i>
                                Ayuda
                            </a>
                            
                            <a href="<?php echo url('dashboard/logout.php'); ?>" class="logout-link">
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