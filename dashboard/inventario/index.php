<?php
include '../header.php';
?>

<!-- Header del Inventario -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-warehouse" style="color: var(--primary);"></i>
            <h1>Panel de Inventario</h1>
        </div>
        <span class="pv-badge">GESTIÓN</span>
    </div>
    
    <div class="pv-header-right" style="gap: 0.75rem;">
        <a href="/dashboard/productos/entrada_rapida.php" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-barcode"></i>
            Entrada Rápida
        </a>
        <a href="/dashboard/inventario/ajuste_masivo.php" class="btn-header" style="text-decoration: none; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white;">
            <i class="fas fa-cubes"></i>
            Ajuste Masivo
        </a>
    </div>
</div>

<!-- Tarjetas de resumen -->
<div class="estadisticas-grid" style="grid-template-columns: repeat(4, 1fr);">
    <!-- Total Productos -->
    <div class="estadistica-card">
        <div class="estadistica-icon blue">
            <i class="fas fa-box"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Total Productos</span>
            <span class="estadistica-valor">1,234</span>
            <span class="estadistica-sub">Activos en inventario</span>
        </div>
    </div>
    
    <!-- Stock Bajo -->
    <div class="estadistica-card">
        <div class="estadistica-icon yellow">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Stock Bajo</span>
            <span class="estadistica-valor">12</span>
            <a href="/dashboard/inventario/stock_bajo.php" class="estadistica-link">
                Ver lista <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Stock Cero -->
    <div class="estadistica-card">
        <div class="estadistica-icon red">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Stock Cero</span>
            <span class="estadistica-valor">3</span>
            <a href="/dashboard/inventario/stock_bajo.php?tipo=agotado" class="estadistica-link" style="color: var(--danger);">
                Revisar urgentes <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Valor del Inventario -->
    <div class="estadistica-card">
        <div class="estadistica-icon green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Valor del Inventario</span>
            <span class="estadistica-valor">$145,678.90</span>
            <span class="estadistica-sub">Costo de compra</span>
        </div>
    </div>
</div>

<!-- Grid principal (2 columnas) -->
<div class="inventario-grid">
    <!-- Productos con Stock Bajo -->
    <div class="card-inventario">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-circle" style="color: var(--warning);"></i>
                Productos con Stock Bajo
            </h3>
            <a href="/dashboard/inventario/stock_bajo.php" class="card-link">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="tabla-responsive">
                <table class="tabla-inventario">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Mínimo</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Producto 1 -->
                        <tr class="fila-producto">
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Martillo de Uña 16oz</span>
                                    <span class="producto-codigo">MT-001</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="stock-cantidad cero">0</span>
                            </td>
                            <td class="text-center text-gray-600">5</td>
                            <td>
                                <span class="badge-estado danger">Agotado</span>
                            </td>
                            <td>
                                <a href="#" class="btn-accion-inventario" title="Ajustar stock">
                                    <i class="fas fa-cubes"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Producto 2 -->
                        <tr class="fila-producto">
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Taladro Percutor 500W</span>
                                    <span class="producto-codigo">TL-023</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="stock-cantidad bajo">2</span>
                            </td>
                            <td class="text-center text-gray-600">3</td>
                            <td>
                                <span class="badge-estado warning">Stock Bajo</span>
                            </td>
                            <td>
                                <a href="#" class="btn-accion-inventario" title="Ajustar stock">
                                    <i class="fas fa-cubes"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Producto 3 -->
                        <tr class="fila-producto">
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Caja de Tornillos 1/2"</span>
                                    <span class="producto-codigo">TR-456</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="stock-cantidad bajo">3</span>
                            </td>
                            <td class="text-center text-gray-600">10</td>
                            <td>
                                <span class="badge-estado warning">Stock Bajo</span>
                            </td>
                            <td>
                                <a href="#" class="btn-accion-inventario" title="Ajustar stock">
                                    <i class="fas fa-cubes"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Producto 4 -->
                        <tr class="fila-producto">
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Sierra Circular 7-1/4"</span>
                                    <span class="producto-codigo">SR-789</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="stock-cantidad bajo">1</span>
                            </td>
                            <td class="text-center text-gray-600">2</td>
                            <td>
                                <span class="badge-estado warning">Stock Bajo</span>
                            </td>
                            <td>
                                <a href="#" class="btn-accion-inventario" title="Ajustar stock">
                                    <i class="fas fa-cubes"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Empty state (comentado) 
            <div class="empty-state-small">
                <i class="fas fa-check-circle" style="color: var(--success); font-size: 2rem;"></i>
                <p>¡Todo bien! No hay productos con stock bajo</p>
            </div>
            -->
        </div>
    </div>
    
    <!-- Últimos Movimientos -->
    <div class="card-inventario">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Últimos Movimientos
            </h3>
            <a href="/dashboard/inventario/movimientos.php" class="card-link">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="tabla-responsive">
                <table class="tabla-inventario">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-center">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Movimiento 1 -->
                        <tr class="fila-movimiento">
                            <td class="fecha-movimiento">
                                <span class="fecha-dia">15/03</span>
                                <span class="fecha-hora">14:30</span>
                            </td>
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Cemento Portland</span>
                                    <span class="producto-codigo">Admin</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-tipo entrada">Entrada</span>
                            </td>
                            <td class="text-center cantidad entrada">+50</td>
                            <td class="text-center">
                                <span class="stock-evolucion">245 → 295</span>
                            </td>
                        </tr>
                        
                        <!-- Movimiento 2 -->
                        <tr class="fila-movimiento">
                            <td class="fecha-movimiento">
                                <span class="fecha-dia">15/03</span>
                                <span class="fecha-hora">12:15</span>
                            </td>
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Varilla 3/8"</span>
                                    <span class="producto-codigo">Sistema</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-tipo salida">Salida</span>
                            </td>
                            <td class="text-center cantidad salida">-20</td>
                            <td class="text-center">
                                <span class="stock-evolucion">856 → 836</span>
                            </td>
                        </tr>
                        
                        <!-- Movimiento 3 -->
                        <tr class="fila-movimiento">
                            <td class="fecha-movimiento">
                                <span class="fecha-dia">14/03</span>
                                <span class="fecha-hora">18:20</span>
                            </td>
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Taladro Percutor</span>
                                    <span class="producto-codigo">Admin</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-tipo ajuste">Ajuste</span>
                            </td>
                            <td class="text-center cantidad ajuste">-2</td>
                            <td class="text-center">
                                <span class="stock-evolucion">4 → 2</span>
                            </td>
                        </tr>
                        
                        <!-- Movimiento 4 -->
                        <tr class="fila-movimiento">
                            <td class="fecha-movimiento">
                                <span class="fecha-dia">14/03</span>
                                <span class="fecha-hora">16:00</span>
                            </td>
                            <td>
                                <div class="producto-info">
                                    <span class="producto-nombre">Pintura Blanca 20L</span>
                                    <span class="producto-codigo">María G.</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-tipo entrada">Entrada</span>
                            </td>
                            <td class="text-center cantidad entrada">+15</td>
                            <td class="text-center">
                                <span class="stock-evolucion">23 → 38</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas de inventario -->
<div class="acciones-rapidas-grid">
    <a href="/dashboard/productos/nuevo.php" class="accion-rapida-card">
        <div class="accion-icon blue">
            <i class="fas fa-box"></i>
        </div>
        <div class="accion-info">
            <h4>Nuevo Producto</h4>
            <p>Agregar al inventario</p>
        </div>
    </a>
    
    <a href="/dashboard/productos/entrada_rapida.php" class="accion-rapida-card">
        <div class="accion-icon green">
            <i class="fas fa-barcode"></i>
        </div>
        <div class="accion-info">
            <h4>Entrada Rápida</h4>
            <p>Escanear y agregar stock</p>
        </div>
    </a>
    
    <a href="/dashboard/inventario/stock_bajo.php" class="accion-rapida-card">
        <div class="accion-icon yellow">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="accion-info">
            <h4>Stock Bajo</h4>
            <p>Revisar productos críticos</p>
        </div>
    </a>
</div>

<style>
/* Animaciones para las filas */
@keyframes fadeInRow {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fila-producto, .fila-movimiento {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-producto:nth-child(1),
.fila-movimiento:nth-child(1) { animation-delay: 0.05s; }
.fila-producto:nth-child(2),
.fila-movimiento:nth-child(2) { animation-delay: 0.1s; }
.fila-producto:nth-child(3),
.fila-movimiento:nth-child(3) { animation-delay: 0.15s; }
.fila-producto:nth-child(4),
.fila-movimiento:nth-child(4) { animation-delay: 0.2s; }
</style>

<?php include '../footer.php'; ?>