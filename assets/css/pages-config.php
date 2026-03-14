<?php
// ===== CONFIGURACIÓN DE CSS POR PÁGINA =====
function getPageCss($uri, $version) {
    $cssFiles = [];
    
    $pageMappings = [
        'dashboard/index.php' => 'dashboard.css',
        'ventas/index.php' => 'ventas.css',
        'ventas/historial.php' => 'historial.css',
        'productos/index.php' => 'productos.css',
        'productos/ver.php' => 'producto-ver.css',
        'productos/nuevo.php' => 'producto-form.css',
        'productos/editar.php' => 'producto-form.css',
        'productos/ajustar_stock.php' => 'ajustar-stock.css',
        'inventario/index.php' => 'inventario.css',
        'inventario/entrada_rapida.php' => 'entrada_rapida.css',
        'inventario/movimientos.php' => 'movimientos.css',
        'inventario/stock_bajo.php' => 'stock-bajo.css',
        'categorias/index.php' => 'categorias.css',
        'categorias/nuevo.php' => 'categoria-form.css',
        'categorias/editar.php' => 'categoria-form.css',
        'marcas/index.php' => 'marcas.css',
        'marcas/nuevo.php' => 'marca-form.css',
        'marcas/editar.php' => 'marca-form.css',
        'clientes/index.php' => 'clientes.css',
        'clientes/nuevo.php' => 'cliente-form.css',
        'clientes/editar.php' => 'cliente-form.css',
        'reportes/corte_caja.php' => 'corte_caja.css',
        'usuarios/index.php' => 'usuarios.css',           // Lista de usuarios
        'usuarios/nuevo.php' => 'usuarios.css',           // Nuevo usuario (usa mismo CSS)
        'usuarios/editar.php' => 'usuarios.css',          // Editar usuario (usa mismo CSS)
        'usuarios/ver.php' => 'usuarios.css', 
        'usuarios/cambiar_password.php' => 'usuarios.css',     
        'reportes/index.php' => 'reportes.css',
        'reportes/historial_cortes.php' => 'historial_cortes.css',      
    ];
    
    foreach ($pageMappings as $pattern => $cssFile) {
        if (strpos($uri, $pattern) !== false) {
            $cssFiles[] = $cssFile;
        }
    }
    
    return $cssFiles;
}
?>