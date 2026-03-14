<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener filtros actuales
$tipo_filtro = $_GET['tipo'] ?? 'todos';
$categoria_filtro = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$marca_filtro = isset($_GET['marca']) ? (int)$_GET['marca'] : 0;

// Construir condiciones WHERE
$condiciones = ["p.activo = 1"];

// Filtro por tipo de stock
if ($tipo_filtro === 'bajo') {
    $condiciones[] = "p.stock_actual <= p.stock_minimo AND p.stock_actual > 0";
} elseif ($tipo_filtro === 'agotado') {
    $condiciones[] = "p.stock_actual = 0";
} else {
    $condiciones[] = "p.stock_actual <= p.stock_minimo";
}

// Filtro por categoría
if ($categoria_filtro > 0) {
    $condiciones[] = "p.id_categoria = $categoria_filtro";
}

// Filtro por marca
if ($marca_filtro > 0) {
    $condiciones[] = "p.id_marca = $marca_filtro";
}

$where = implode(" AND ", $condiciones);

// Obtener productos con stock bajo
$sql = "
    SELECT p.*, c.nombre as categoria_nombre, m.nombre as marca_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id
    LEFT JOIN marcas m ON p.id_marca = m.id
    WHERE $where
    ORDER BY 
        CASE 
            WHEN p.stock_actual = 0 THEN 0 
            ELSE 1 
        END,
        p.stock_actual ASC
";

$result = $conn->query($sql);
$productos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}

// Obtener categorías para filtros
$categorias = [];
$result = $conn->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Obtener marcas para filtros
$marcas = [];
$result = $conn->query("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $marcas[] = $row;
    }
}

// Contar estadísticas totales (sin filtros)
$stats_sql = "
    SELECT 
        SUM(CASE WHEN stock_actual <= stock_minimo AND stock_actual > 0 THEN 1 ELSE 0 END) as total_bajos,
        SUM(CASE WHEN stock_actual = 0 THEN 1 ELSE 0 END) as total_agotados
    FROM productos 
    WHERE activo = 1
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
$total_bajos = $stats['total_bajos'] ?? 0;
$total_agotados = $stats['total_agotados'] ?? 0;

include '../header.php';
?>

<!-- Header de la página -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
            <h1>Productos con Stock Bajo</h1>
        </div>
        <span class="pv-badge">INVENTARIO</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/inventario/index.php'); ?>" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver a Inventario
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <div class="filtros-grid" style="grid-template-columns: repeat(5, 1fr);">
            <!-- Tipo de filtro -->
            <select name="tipo" class="filtro-select">
                <option value="todos" <?php echo $tipo_filtro == 'todos' ? 'selected' : ''; ?>>📋 Todos (Bajo y Agotado)</option>
                <option value="bajo" <?php echo $tipo_filtro == 'bajo' ? 'selected' : ''; ?>>⚠️ Stock Bajo</option>
                <option value="agotado" <?php echo $tipo_filtro == 'agotado' ? 'selected' : ''; ?>>❌ Agotados</option>
            </select>
            
            <!-- Categorías -->
            <select name="categoria" class="filtro-select">
                <option value="">📂 Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoria_filtro == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo h($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Marcas -->
            <select name="marca" class="filtro-select">
                <option value="">🏷️ Todas las marcas</option>
                <?php foreach ($marcas as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo $marca_filtro == $m['id'] ? 'selected' : ''; ?>>
                        <?php echo h($m['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Botón filtrar -->
            <button type="submit" class="btn-filtro btn-primary">
                <i class="fas fa-filter"></i>
                Filtrar
            </button>
            
            <!-- Botón limpiar -->
            <a href="<?php echo url('dashboard/inventario/stock_bajo.php'); ?>" class="btn-filtro btn-secondary" style="text-decoration: none; text-align: center;">
                <i class="fas fa-times"></i>
                Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de productos -->
<div class="tabla-container">
    <div class="tabla-responsive">
        <table class="tabla-stock-bajo">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th class="text-center">Stock Actual</th>
                    <th class="text-center">Stock Mínimo</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($productos) > 0): ?>
                    <?php foreach ($productos as $p): ?>
                    <tr class="fila-producto-stock">
                        <td class="col-producto">
                            <div class="producto-info">
                                <span class="producto-nombre"><?php echo h($p['nombre']); ?></span>
                                <span class="producto-codigo"><?php echo $p['codigo_barras'] ?: '—'; ?></span>
                                <small class="producto-sku">SKU: <?php echo h($p['sku']); ?></small>
                            </div>
                        </td>
                        <td class="col-categoria"><?php echo $p['categoria_nombre'] ?: '—'; ?></td>
                        <td class="col-marca"><?php echo $p['marca_nombre'] ?: '—'; ?></td>
                        <td class="text-center">
                            <span class="stock-actual <?php echo $p['stock_actual'] == 0 ? 'agotado' : 'bajo'; ?>">
                                <?php echo $p['stock_actual']; ?>
                            </span>
                        </td>
                        <td class="text-center stock-minimo"><?php echo $p['stock_minimo']; ?></td>
                        <td class="text-center">
                            <?php if ($p['stock_actual'] == 0): ?>
                                <span class="estado-badge agotado">
                                    <i class="fas fa-times-circle"></i>
                                    Agotado
                                </span>
                            <?php else: ?>
                                <span class="estado-badge bajo">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Stock Bajo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="col-acciones">
                            <div class="acciones-wrapper">
                                <a href="<?php echo url('dashboard/productos/ver.php?id=' . $p['id']); ?>" 
                                   class="accion-icon" title="Ver producto">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo url('dashboard/productos/ajustar_stock.php?id=' . $p['id']); ?>" 
                                   class="accion-icon" title="Ajustar stock">
                                    <i class="fas fa-cubes"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state-row">
                            <div class="empty-state-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3>¡Excelente!</h3>
                            <p>No hay productos con problemas de stock</p>
                            <a href="<?php echo url('dashboard/inventario/index.php'); ?>" class="btn-primary" style="margin-top: 1rem; display: inline-block;">
                                <i class="fas fa-arrow-left"></i>
                                Volver al inventario
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <?php if (count($productos) > 0): ?>
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-boxes"></i>
                Mostrando: <strong><?php echo count($productos); ?></strong> productos
            </p>
            <div class="resumen-stats">
                <span class="stat-badge bajo">
                    <span class="stat-dot" style="background: #f59e0b;"></span>
                    Stock bajo: <strong><?php echo $total_bajos; ?></strong>
                </span>
                <span class="stat-badge agotado">
                    <span class="stat-dot" style="background: var(--danger);"></span>
                    Agotados: <strong><?php echo $total_agotados; ?></strong>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
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

.fila-producto-stock {
    animation: fadeInRow 0.3s ease-out forwards;
}

/* Estilos adicionales */
.producto-info small {
    display: block;
    font-size: 0.7rem;
    color: var(--gray-500);
    margin-top: 0.2rem;
}

.fila-producto-stock:nth-child(1) { animation-delay: 0.02s; }
.fila-producto-stock:nth-child(2) { animation-delay: 0.04s; }
.fila-producto-stock:nth-child(3) { animation-delay: 0.06s; }
.fila-producto-stock:nth-child(4) { animation-delay: 0.08s; }
.fila-producto-stock:nth-child(5) { animation-delay: 0.10s; }
.fila-producto-stock:nth-child(6) { animation-delay: 0.12s; }
.fila-producto-stock:nth-child(7) { animation-delay: 0.14s; }
.fila-producto-stock:nth-child(8) { animation-delay: 0.16s; }
</style>

<?php include '../footer.php'; ?>