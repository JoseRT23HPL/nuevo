<?php
include '../header.php';

// Datos de ejemplo para productos con stock bajo
$productos = [
    [
        'id' => 1,
        'nombre' => 'Martillo de Uña 16oz',
        'codigo_barras' => '750123456789',
        'categoria' => 'Herramientas Manuales',
        'marca' => 'Truper',
        'stock_actual' => 2,
        'stock_minimo' => 5
    ],
    [
        'id' => 2,
        'nombre' => 'Taladro Percutor 500W',
        'codigo_barras' => '750123456788',
        'categoria' => 'Herramientas Eléctricas',
        'marca' => 'Bosch',
        'stock_actual' => 1,
        'stock_minimo' => 3
    ],
    [
        'id' => 3,
        'nombre' => 'Caja de Tornillos 1/2" x 100pz',
        'codigo_barras' => '750123456787',
        'categoria' => 'Ferretería General',
        'marca' => 'Pretul',
        'stock_actual' => 0,
        'stock_minimo' => 10
    ],
    [
        'id' => 4,
        'nombre' => 'Cemento Portland Gris 50kg',
        'codigo_barras' => '750123456786',
        'categoria' => 'Materiales Construcción',
        'marca' => 'Cruz Azul',
        'stock_actual' => 3,
        'stock_minimo' => 8
    ],
    [
        'id' => 5,
        'nombre' => 'Pintura Blanca 20L',
        'codigo_barras' => '750123456785',
        'categoria' => 'Pinturas',
        'marca' => 'Comex',
        'stock_actual' => 0,
        'stock_minimo' => 5
    ],
    [
        'id' => 6,
        'nombre' => 'Cable Eléctrico Calibre 12',
        'codigo_barras' => '750123456784',
        'categoria' => 'Electricidad',
        'marca' => 'Volteck',
        'stock_actual' => 4,
        'stock_minimo' => 6
    ],
    [
        'id' => 7,
        'nombre' => 'Disco de Corte 7"',
        'codigo_barras' => '750123456783',
        'categoria' => 'Herramientas Eléctricas',
        'marca' => 'Stanley',
        'stock_actual' => 2,
        'stock_minimo' => 10
    ],
    [
        'id' => 8,
        'nombre' => 'Guantes de Seguridad Talla M',
        'codigo_barras' => '750123456782',
        'categoria' => 'Seguridad Industrial',
        'marca' => '3M',
        'stock_actual' => 0,
        'stock_minimo' => 15
    ]
];

// Datos para filtros
$categorias = [
    ['id' => 1, 'nombre' => 'Herramientas Manuales'],
    ['id' => 2, 'nombre' => 'Herramientas Eléctricas'],
    ['id' => 3, 'nombre' => 'Materiales Construcción'],
    ['id' => 4, 'nombre' => 'Ferretería General'],
    ['id' => 5, 'nombre' => 'Pinturas'],
    ['id' => 6, 'nombre' => 'Electricidad'],
    ['id' => 7, 'nombre' => 'Seguridad Industrial']
];

$marcas = [
    ['id' => 1, 'nombre' => 'Truper'],
    ['id' => 2, 'nombre' => 'Pretul'],
    ['id' => 3, 'nombre' => 'Bosch'],
    ['id' => 4, 'nombre' => 'Stanley'],
    ['id' => 5, 'nombre' => 'Comex'],
    ['id' => 6, 'nombre' => 'Cruz Azul'],
    ['id' => 7, 'nombre' => 'Volteck'],
    ['id' => 8, 'nombre' => '3M']
];

// Obtener filtros actuales (simulados)
$tipo_actual = $_GET['tipo'] ?? 'todos';
$categoria_actual = $_GET['categoria'] ?? '';
$marca_actual = $_GET['marca'] ?? '';

// Filtrar productos según los filtros (simulación)
$productos_filtrados = array_filter($productos, function($p) use ($tipo_actual, $categoria_actual, $marca_actual) {
    // Filtro por tipo
    if ($tipo_actual == 'bajo' && $p['stock_actual'] == 0) return false;
    if ($tipo_actual == 'agotado' && $p['stock_actual'] > 0) return false;
    
    // Filtro por categoría
    if (!empty($categoria_actual) && strpos($p['categoria'], $categoria_actual) === false) return false;
    
    // Filtro por marca
    if (!empty($marca_actual) && strpos($p['marca'], $marca_actual) === false) return false;
    
    return true;
});

// Contar estadísticas
$total_agotados = count(array_filter($productos, function($p) { return $p['stock_actual'] == 0; }));
$total_bajos = count(array_filter($productos, function($p) { return $p['stock_actual'] > 0 && $p['stock_actual'] <= $p['stock_minimo']; }));
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
        <a href="/dashboard/inventario/index.php" class="btn-header" style="text-decoration: none;">
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
                <option value="todos" <?php echo $tipo_actual == 'todos' ? 'selected' : ''; ?>>📋 Todos (Bajo y Agotado)</option>
                <option value="bajo" <?php echo $tipo_actual == 'bajo' ? 'selected' : ''; ?>>⚠️ Stock Bajo</option>
                <option value="agotado" <?php echo $tipo_actual == 'agotado' ? 'selected' : ''; ?>>❌ Agotados</option>
            </select>
            
            <!-- Categorías -->
            <select name="categoria" class="filtro-select">
                <option value="">📂 Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['nombre']; ?>" <?php echo $categoria_actual == $cat['nombre'] ? 'selected' : ''; ?>>
                        <?php echo $cat['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Marcas -->
            <select name="marca" class="filtro-select">
                <option value="">🏷️ Todas las marcas</option>
                <?php foreach ($marcas as $m): ?>
                    <option value="<?php echo $m['nombre']; ?>" <?php echo $marca_actual == $m['nombre'] ? 'selected' : ''; ?>>
                        <?php echo $m['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Botón filtrar -->
            <button type="submit" class="btn-filtro btn-primary">
                <i class="fas fa-filter"></i>
                Filtrar
            </button>
            
            <!-- Botón limpiar -->
            <a href="/dashboard/inventario/stock_bajo.php" class="btn-filtro btn-secondary" style="text-decoration: none; text-align: center;">
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
                <?php if (count($productos_filtrados) > 0): ?>
                    <?php foreach ($productos_filtrados as $p): ?>
                    <tr class="fila-producto-stock">
                        <td class="col-producto">
                            <div class="producto-info">
                                <span class="producto-nombre"><?php echo $p['nombre']; ?></span>
                                <span class="producto-codigo"><?php echo $p['codigo_barras']; ?></span>
                            </div>
                        </td>
                        <td class="col-categoria"><?php echo $p['categoria']; ?></td>
                        <td class="col-marca"><?php echo $p['marca']; ?></td>
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
                                <a href="/dashboard/productos/ver.php?id=<?php echo $p['id']; ?>" 
                                   class="accion-icon" title="Ver producto">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/dashboard/productos/ajustar_stock.php?id=<?php echo $p['id']; ?>" 
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
                            <a href="/dashboard/inventario/index.php" class="btn-primary" style="margin-top: 1rem; display: inline-block;">
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
    <?php if (count($productos_filtrados) > 0): ?>
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-boxes"></i>
                Mostrando: <strong><?php echo count($productos_filtrados); ?></strong> productos
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