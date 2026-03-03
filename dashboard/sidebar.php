<?php
// Al inicio del sidebar, asegurarnos que config está incluido
if (!function_exists('url')) {
    require_once dirname(__DIR__) . '/config.php';
}

// Obtener el usuario actual desde la sesión
$usuario_actual = getCurrentUser();

// Si no hay usuario, mostrar valores por defecto (no debería pasar porque ya pasó por requiereAuth)
if (!$usuario_actual) {
    $usuario_actual = [
        'nombre' => 'Usuario',
        'rol' => 'usuario'
    ];
}
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    
    <!-- Header del Sidebar (Perfil) - AHORA CON DATOS REALES -->
    <div class="sidebar-profile">
        <div class="profile-content">
            <div class="profile-avatar">
                <i class="fas fa-store"></i>
            </div>
            <div class="profile-info">
                <p class="profile-name"><?php echo h($usuario_actual['nombre']); ?></p>
                <p class="profile-role"><?php echo ucfirst($usuario_actual['rol']); ?></p>
            </div>
        </div>
    </div>

    <!-- Contenido del menú -->
    <div class="sidebar-menu">
        <nav class="menu-nav">
            <!-- Dashboard -->
            <a href="<?php echo url('dashboard/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/dashboard/index.php'); ?>" 
               data-tooltip="Dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- VENTAS -->
            <div class="menu-section">
                <h4 class="menu-section-title">Ventas</h4>
            </div>
            
            <!-- Punto de Venta -->
            <a href="<?php echo url('dashboard/ventas/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/ventas/index.php'); ?>" 
               data-tooltip="Punto de Venta">
                <i class="fas fa-cash-register"></i>
                <span>Punto de Venta</span>
            </a>
            
            <!-- Historial -->
            <a href="<?php echo url('dashboard/ventas/historial.php'); ?>" 
               class="menu-item <?php echo activeClass('/ventas/historial.php'); ?>" 
               data-tooltip="Historial">
                <i class="fas fa-history"></i>
                <span>Historial de Ventas</span>
            </a>
            
            <!-- Tickets -->
            <a href="<?php echo url('dashboard/ventas/ticket.php'); ?>" 
               class="menu-item <?php echo activeClass('/ventas/ticket.php'); ?>" 
               data-tooltip="Tickets">
                <i class="fas fa-receipt"></i>
                <span>Tickets</span>
            </a>
            
            <!-- INVENTARIO -->
            <div class="menu-section">
                <h4 class="menu-section-title">Inventario</h4>
            </div>
            
            <!-- Panel Inventario -->
            <a href="<?php echo url('dashboard/inventario/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/inventario/index.php'); ?>" 
               data-tooltip="Panel Inventario">
                <i class="fas fa-warehouse"></i>
                <span>Panel de Inventario</span>
            </a>
            
            <!-- Productos -->
            <a href="<?php echo url('dashboard/productos/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/productos/index.php'); ?>" 
               data-tooltip="Productos">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
            
            <!-- Categorías -->
            <a href="<?php echo url('dashboard/categorias/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/categorias/index.php'); ?>" 
               data-tooltip="Categorías">
                <i class="fas fa-tags"></i>
                <span>Categorías</span>
            </a>
            
            <!-- Marcas -->
            <a href="<?php echo url('dashboard/marcas/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/marcas/index.php'); ?>" 
               data-tooltip="Marcas">
                <i class="fas fa-trademark"></i>
                <span>Marcas</span>
            </a>
            
            <!-- Stock Bajo (con contador real) -->
            <a href="<?php echo url('dashboard/inventario/stock_bajo.php'); ?>" 
               class="menu-item <?php echo activeClass('/inventario/stock_bajo.php'); ?>" 
               data-tooltip="Stock Bajo">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Stock Bajo</span>
                <?php
                // Consultar productos con stock bajo
                $conn = getDB();
                if ($conn) {
                    $result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock_actual <= stock_minimo AND activo = 1");
                    $stock_bajo = $result->fetch_assoc()['total'];
                    if ($stock_bajo > 0):
                ?>
                    <span class="badge"><?php echo $stock_bajo; ?></span>
                <?php 
                    endif;
                }
                ?>
            </a>
            
            <!-- Movimientos -->
            <a href="<?php echo url('dashboard/inventario/movimientos.php'); ?>" 
               class="menu-item <?php echo activeClass('/inventario/movimientos.php'); ?>" 
               data-tooltip="Movimientos">
                <i class="fas fa-history"></i>
                <span>Movimientos</span>
            </a>
            
            <!-- Entrada Rápida -->
            <a href="<?php echo url('dashboard/inventario/entrada_rapida.php'); ?>" 
               class="menu-item <?php echo activeClass('/inventario/entrada_rapida.php'); ?>" 
               data-tooltip="Entrada Rápida">
                <i class="fas fa-barcode"></i>
                <span>Entrada por Escáner</span>
            </a>
            
            <!-- CLIENTES -->
            <div class="menu-section">
                <h4 class="menu-section-title">Clientes</h4>
            </div>
            
            <!-- Clientes -->
            <a href="<?php echo url('dashboard/clientes/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/clientes/index.php'); ?>" 
               data-tooltip="Clientes">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            
            <!-- Nuevo Cliente -->
            <a href="<?php echo url('dashboard/clientes/nuevo.php'); ?>" 
               class="menu-item <?php echo activeClass('/clientes/nuevo.php'); ?>" 
               data-tooltip="Nuevo Cliente">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Cliente</span>
            </a>
            
            <!-- FACTURACIÓN (solo para admin) -->
            <?php if (hasRole(['admin', 'super_admin'])): ?>
            <div class="menu-section">
                <h4 class="menu-section-title">Facturación</h4>
            </div>
            
            <a href="<?php echo url('dashboard/facturacion/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/facturacion/index.php'); ?>" 
               data-tooltip="Facturación">
                <i class="fas fa-file-invoice"></i>
                <span>Facturación</span>
            </a>
            
            <a href="<?php echo url('dashboard/facturacion/nueva.php'); ?>" 
               class="menu-item <?php echo activeClass('/facturacion/nueva.php'); ?>" 
               data-tooltip="Nueva Factura">
                <i class="fas fa-plus-circle"></i>
                <span>Nueva Factura</span>
            </a>
            <?php endif; ?>
            
            <!-- REPORTES (solo para admin) -->
            <?php if (hasRole(['admin', 'super_admin'])): ?>
            <div class="menu-section">
                <h4 class="menu-section-title">Reportes</h4>
            </div>
            
            <a href="<?php echo url('dashboard/reportes/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/reportes/index.php'); ?>" 
               data-tooltip="Panel Reportes">
                <i class="fas fa-chart-line"></i>
                <span>Panel de Reportes</span>
            </a>
            
            <a href="<?php echo url('dashboard/reportes/ventas.php'); ?>" 
               class="menu-item <?php echo activeClass('/reportes/ventas.php'); ?>" 
               data-tooltip="Reporte Ventas">
                <i class="fas fa-shopping-cart"></i>
                <span>Reporte de Ventas</span>
            </a>
            
            <a href="<?php echo url('dashboard/reportes/corte_caja.php'); ?>" 
               class="menu-item <?php echo activeClass('/reportes/corte_caja.php'); ?>" 
               data-tooltip="Corte de Caja">
                <i class="fas fa-cash-register"></i>
                <span>Corte de Caja</span>
            </a>
            <?php endif; ?>
            
            <!-- ADMINISTRACIÓN (solo para super_admin) -->
            <?php if (hasRole('super_admin')): ?>
            <div class="menu-section">
                <h4 class="menu-section-title">Administración</h4>
            </div>
            
            <a href="<?php echo url('dashboard/usuarios/index.php'); ?>" 
               class="menu-item <?php echo activeClass('/usuarios/index.php'); ?>" 
               data-tooltip="Usuarios">
                <i class="fas fa-users-cog"></i>
                <span>Usuarios</span>
            </a>
            
            <a href="<?php echo url('dashboard/configuracion/empresa.php'); ?>" 
               class="menu-item <?php echo activeClass('/configuracion/empresa.php'); ?>" 
               data-tooltip="Configuración">
                <i class="fas fa-building"></i>
                <span>Configuración Empresa</span>
            </a>
            <?php endif; ?>
            
            <!-- MI CUENTA (todos los usuarios) -->
            <div class="menu-section">
                <h4 class="menu-section-title">Mi Cuenta</h4>
            </div>
            
            <a href="<?php echo url('dashboard/perfil.php'); ?>" 
               class="menu-item <?php echo activeClass('/perfil.php'); ?>" 
               data-tooltip="Mi Perfil">
                <i class="fas fa-user"></i>
                <span>Mi Perfil</span>
            </a>
            
            <a href="<?php echo url('dashboard/cambiar_password.php'); ?>" 
               class="menu-item <?php echo activeClass('/cambiar_password.php'); ?>" 
               data-tooltip="Cambiar Contraseña">
                <i class="fas fa-key"></i>
                <span>Cambiar Contraseña</span>
            </a>
        </nav>
    </div>

    <!-- Footer del sidebar (CERRAR SESIÓN) -->
    <div class="sidebar-footer">
        <a href="<?php echo url('dashboard/logout.php'); ?>" class="logout-button" onclick="return confirm('¿Estás seguro de cerrar sesión?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>