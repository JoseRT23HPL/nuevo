<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// ===== ESTADÍSTICAS DE INVENTARIO =====

// Total de productos activos
$total_productos = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
if ($result) {
    $total_productos = $result->fetch_assoc()['total'];
}

// Productos con stock bajo (stock_actual <= stock_minimo Y stock_actual > 0)
$stock_bajo = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock_actual <= stock_minimo AND stock_actual > 0 AND activo = 1");
if ($result) {
    $stock_bajo = $result->fetch_assoc()['total'];
}

// Productos con stock cero
$stock_cero = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock_actual = 0 AND activo = 1");
if ($result) {
    $stock_cero = $result->fetch_assoc()['total'];
}

// Valor total del inventario (suma de precio_compra * stock_actual)
$valor_inventario = 0;
$result = $conn->query("SELECT COALESCE(SUM(precio_compra * stock_actual), 0) as total FROM productos WHERE activo = 1");
if ($result) {
    $valor_inventario = $result->fetch_assoc()['total'];
}

// ===== PRODUCTOS CON STOCK BAJO (para mostrar en tabla) =====
$productos_bajo_stock = [];
$result = $conn->query("
    SELECT id, nombre, sku, stock_actual, stock_minimo
    FROM productos 
    WHERE stock_actual <= stock_minimo AND activo = 1 
    ORDER BY stock_actual ASC 
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos_bajo_stock[] = $row;
    }
}

// ===== ÚLTIMOS MOVIMIENTOS =====
$movimientos = [];
$result = $conn->query("
    SELECT m.*, p.nombre as producto, p.sku, u.username 
    FROM movimientos_inventario m
    JOIN productos p ON m.id_producto = p.id
    LEFT JOIN usuarios u ON m.id_usuario = u.id
    ORDER BY m.fecha_movimiento DESC
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $movimientos[] = $row;
    }
}

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
        <a href="<?php echo url('dashboard/productos/entrada_rapida.php'); ?>" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-barcode"></i>
            Entrada Rápida
        </a>
        <a href="<?php echo url('dashboard/inventario/ajuste_masivo.php'); ?>" class="btn-header" style="text-decoration: none; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white;">
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
            <span class="estadistica-valor"><?php echo number_format($total_productos); ?></span>
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
            <span class="estadistica-valor"><?php echo number_format($stock_bajo); ?></span>
            <a href="<?php echo url('dashboard/inventario/stock_bajo.php'); ?>" class="estadistica-link">
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
            <span class="estadistica-valor"><?php echo number_format($stock_cero); ?></span>
            <a href="<?php echo url('dashboard/inventario/stock_bajo.php?tipo=agotado'); ?>" class="estadistica-link" style="color: var(--danger);">
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
            <span class="estadistica-valor">$<?php echo number_format($valor_inventario, 2); ?></span>
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
            <a href="<?php echo url('dashboard/inventario/stock_bajo.php'); ?>" class="card-link">
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
                        <?php if (empty($productos_bajo_stock)): ?>
                            <tr>
                                <td colspan="5" class="empty-state-row">
                                    <div class="empty-state-small">
                                        <i class="fas fa-check-circle" style="color: var(--success); font-size: 2rem;"></i>
                                        <p>¡Todo bien! No hay productos con stock bajo</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos_bajo_stock as $p): ?>
                            <tr class="fila-producto">
                                <td>
                                    <div class="producto-info">
                                        <span class="producto-nombre"><?php echo h($p['nombre']); ?></span>
                                        <span class="producto-codigo"><?php echo h($p['sku']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="stock-cantidad <?php echo $p['stock_actual'] == 0 ? 'cero' : 'bajo'; ?>">
                                        <?php echo $p['stock_actual']; ?>
                                    </span>
                                </td>
                                <td class="text-center"><?php echo $p['stock_minimo']; ?></td>
                                <td>
                                    <?php if ($p['stock_actual'] == 0): ?>
                                        <span class="badge-estado danger">Agotado</span>
                                    <?php else: ?>
                                        <span class="badge-estado warning">Stock Bajo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo url('dashboard/productos/ajustar_stock.php?id=' . $p['id']); ?>" class="btn-accion-inventario" title="Ajustar stock">
                                        <i class="fas fa-cubes"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Últimos Movimientos -->
    <div class="card-inventario">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Últimos Movimientos
            </h3>
            <a href="<?php echo url('dashboard/inventario/movimientos.php'); ?>" class="card-link">
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
                        <?php if (empty($movimientos)): ?>
                            <tr>
                                <td colspan="5" class="empty-state-row">
                                    <div class="empty-state-small">
                                        <i class="fas fa-history" style="color: var(--gray-400); font-size: 2rem;"></i>
                                        <p>No hay movimientos registrados</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movimientos as $m): ?>
                            <tr class="fila-movimiento">
                                <td class="fecha-movimiento">
                                    <span class="fecha-dia"><?php echo date('d/m', strtotime($m['fecha_movimiento'])); ?></span>
                                    <span class="fecha-hora"><?php echo date('H:i', strtotime($m['fecha_movimiento'])); ?></span>
                                </td>
                                <td>
                                    <div class="producto-info">
                                        <span class="producto-nombre"><?php echo h($m['producto']); ?></span>
                                        <span class="producto-codigo"><?php echo h($m['username'] ?? 'Sistema'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-tipo <?php echo $m['tipo']; ?>">
                                        <?php 
                                        echo $m['tipo'] == 'entrada' ? 'Entrada' : 
                                            ($m['tipo'] == 'salida' ? 'Salida' : 'Ajuste'); 
                                        ?>
                                    </span>
                                </td>
                                <td class="text-center cantidad <?php echo $m['tipo']; ?>">
                                    <?php 
                                    echo $m['tipo'] == 'entrada' ? '+' : 
                                        ($m['tipo'] == 'salida' ? '-' : '±'); 
                                    echo $m['cantidad']; 
                                    ?>
                                </td>
                                <td class="text-center">
                                    <span class="stock-evolucion">
                                        <?php echo $m['stock_anterior']; ?> → <?php echo $m['stock_nuevo']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas de inventario -->
<div class="acciones-rapidas-grid">
    <a href="<?php echo url('dashboard/productos/nuevo.php'); ?>" class="accion-rapida-card">
        <div class="accion-icon blue">
            <i class="fas fa-box"></i>
        </div>
        <div class="accion-info">
            <h4>Nuevo Producto</h4>
            <p>Agregar al inventario</p>
        </div>
    </a>
    
    <a href="<?php echo url('dashboard/productos/entrada_rapida.php'); ?>" class="accion-rapida-card">
        <div class="accion-icon green">
            <i class="fas fa-barcode"></i>
        </div>
        <div class="accion-info">
            <h4>Entrada Rápida</h4>
            <p>Escanear y agregar stock</p>
        </div>
    </a>
    
    <a href="<?php echo url('dashboard/inventario/stock_bajo.php'); ?>" class="accion-rapida-card">
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
.fila-producto:nth-child(5),
.fila-movimiento:nth-child(5) { animation-delay: 0.25s; }

/* Estilos para empty state */
.empty-state-row {
    text-align: center;
    padding: 2rem !important;
}

.empty-state-small {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-500);
}

.empty-state-small i {
    margin-bottom: 0.5rem;
}

.empty-state-small p {
    margin: 0;
    font-size: 0.9rem;
}
</style>

<?php include '../footer.php'; ?>