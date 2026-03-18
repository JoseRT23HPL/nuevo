<?php
// file: /dashboard/reportes/productos_mas_vendidos.php - VERSIÓN SIN FILTROS DE FECHA
include '../header.php';

// Datos de ejemplo para mostrar
$categoria = $_GET['categoria'] ?? '';
$marca = $_GET['marca'] ?? '';
$limite = (int)($_GET['limite'] ?? 20);

// Ejemplo de categorías para filtros
$categorias = [
    ['id' => 1, 'nombre' => 'Herramientas Manuales'],
    ['id' => 2, 'nombre' => 'Herramientas Eléctricas'],
    ['id' => 3, 'nombre' => 'Materiales Construcción'],
    ['id' => 4, 'nombre' => 'Ferretería General'],
    ['id' => 5, 'nombre' => 'Pinturas'],
];

// Ejemplo de marcas para filtros
$marcas = [
    ['id' => 1, 'nombre' => 'Truper'],
    ['id' => 2, 'nombre' => 'Pretul'],
    ['id' => 3, 'nombre' => 'Bosch'],
    ['id' => 4, 'nombre' => 'Stanley'],
    ['id' => 5, 'nombre' => 'Comex'],
];

// Productos más vendidos por cantidad (ejemplo)
$top_cantidad = [
    [
        'id' => 1,
        'nombre' => 'Martillo de Uña 16oz',
        'codigo_barras' => '750123456789',
        'imagen_url' => '',
        'categoria' => 'Herramientas Manuales',
        'marca' => 'Truper',
        'veces_vendido' => 45,
        'total_unidades' => 78,
        'total_ingresos' => 1950.00,
        'precio_promedio' => 25.00
    ],
    [
        'id' => 2,
        'nombre' => 'Taladro Percutor 500W',
        'codigo_barras' => '750123456788',
        'imagen_url' => '',
        'categoria' => 'Herramientas Eléctricas',
        'marca' => 'Bosch',
        'veces_vendido' => 23,
        'total_unidades' => 28,
        'total_ingresos' => 10920.00,
        'precio_promedio' => 390.00
    ],
    [
        'id' => 3,
        'nombre' => 'Caja de Tornillos 1/2" x 100pz',
        'codigo_barras' => '750123456787',
        'imagen_url' => '',
        'categoria' => 'Ferretería General',
        'marca' => 'Pretul',
        'veces_vendido' => 120,
        'total_unidades' => 450,
        'total_ingresos' => 6750.00,
        'precio_promedio' => 15.00
    ],
    [
        'id' => 4,
        'nombre' => 'Cemento Portland Gris 50kg',
        'codigo_barras' => '750123456786',
        'imagen_url' => '',
        'categoria' => 'Materiales Construcción',
        'marca' => 'Cruz Azul',
        'veces_vendido' => 85,
        'total_unidades' => 340,
        'total_ingresos' => 68000.00,
        'precio_promedio' => 200.00
    ],
    [
        'id' => 5,
        'nombre' => 'Pintura Blanca 20L',
        'codigo_barras' => '750123456785',
        'imagen_url' => '',
        'categoria' => 'Pinturas',
        'marca' => 'Comex',
        'veces_vendido' => 32,
        'total_unidades' => 64,
        'total_ingresos' => 8960.00,
        'precio_promedio' => 140.00
    ],
    [
        'id' => 6,
        'nombre' => 'Cable Eléctrico Calibre 12',
        'codigo_barras' => '750123456784',
        'imagen_url' => '',
        'categoria' => 'Electricidad',
        'marca' => 'Volteck',
        'veces_vendido' => 56,
        'total_unidades' => 280,
        'total_ingresos' => 4200.00,
        'precio_promedio' => 15.00
    ],
    [
        'id' => 7,
        'nombre' => 'Disco de Corte 7"',
        'codigo_barras' => '750123456783',
        'imagen_url' => '',
        'categoria' => 'Herramientas Eléctricas',
        'marca' => 'Stanley',
        'veces_vendido' => 67,
        'total_unidades' => 201,
        'total_ingresos' => 3015.00,
        'precio_promedio' => 15.00
    ],
    [
        'id' => 8,
        'nombre' => 'Guantes de Seguridad Talla M',
        'codigo_barras' => '750123456782',
        'imagen_url' => '',
        'categoria' => 'Seguridad Industrial',
        'marca' => '3M',
        'veces_vendido' => 43,
        'total_unidades' => 215,
        'total_ingresos' => 3225.00,
        'precio_promedio' => 15.00
    ]
];

// Productos más vendidos por ingresos (ejemplo - ordenado diferente)
$top_ingresos = $top_cantidad;
usort($top_ingresos, function($a, $b) {
    return $b['total_ingresos'] <=> $a['total_ingresos'];
});

// Resumen general
$total_productos = count($top_cantidad);
$total_unidades = array_sum(array_column($top_cantidad, 'total_unidades'));
$total_ingresos = array_sum(array_column($top_cantidad, 'total_ingresos'));
$promedio_unidades = $total_unidades / max($total_productos, 1);

$resumen = [
    'total_productos_vendidos' => $total_productos,
    'total_unidades_vendidas' => $total_unidades,
    'total_ingresos' => $total_ingresos,
    'promedio_unidades_por_venta' => $promedio_unidades
];

// Limitar según el parámetro
$top_cantidad = array_slice($top_cantidad, 0, $limite);
$top_ingresos = array_slice($top_ingresos, 0, $limite);
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-chart-bar"></i>
            <h1>Productos Más Vendidos</h1>
        </div>
        <span class="pv-badge">REPORTES</span>
    </div>
    
    <div class="pv-header-right">
        <a href="exportar.php?tipo=productos" class="btn-header success">
            <i class="fas fa-file-excel"></i>
            Exportar
        </a>
        <a href="index.php" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Analiza los productos con mejor rendimiento en el sistema</p>

<!-- FILTROS REDISEÑADOS - SIN FECHAS -->
<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <div class="filtros-grid-productos">
            <!-- Fila única: Categoría, Marca, Límite y Botones -->
            <div class="filtros-row sin-fechas">
                <div class="filtro-group">
                    <label class="filtro-label">
                        <i class="fas fa-tags"></i>
                        Categoría
                    </label>
                    <select name="categoria" class="filtro-select">
                        <option value="">📂 Todas las categorías</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label class="filtro-label">
                        <i class="fas fa-trademark"></i>
                        Marca
                    </label>
                    <select name="marca" class="filtro-select">
                        <option value="">🏷️ Todas las marcas</option>
                        <?php foreach($marcas as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo $marca == $m['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label class="filtro-label">
                        <i class="fas fa-list"></i>
                        Mostrar
                    </label>
                    <select name="limite" class="filtro-select">
                        <option value="10" <?php echo $limite == 10 ? 'selected' : ''; ?>>10 productos</option>
                        <option value="20" <?php echo $limite == 20 ? 'selected' : ''; ?>>20 productos</option>
                        <option value="50" <?php echo $limite == 50 ? 'selected' : ''; ?>>50 productos</option>
                        <option value="100" <?php echo $limite == 100 ? 'selected' : ''; ?>>100 productos</option>
                    </select>
                </div>
                
                <div class="filtro-botones">
                    <button type="submit" class="btn-filtro btn-primary" title="Aplicar filtros">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    <a href="productos_mas_vendidos.php" class="btn-filtro btn-secondary" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Resumen general en cards -->
<div class="stats-grid-productos">
    <!-- Productos vendidos -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Productos Vendidos</span>
            <span class="stat-valor"><?php echo number_format($resumen['total_productos_vendidos']); ?></span>
        </div>
    </div>
    
    <!-- Unidades vendidas -->
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-cubes"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Unidades Vendidas</span>
            <span class="stat-valor"><?php echo number_format($resumen['total_unidades_vendidas']); ?></span>
        </div>
    </div>
    
    <!-- Ingresos totales -->
    <div class="stat-card">
        <div class="stat-icon yellow">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Ingresos Totales</span>
            <span class="stat-valor">$<?php echo number_format($resumen['total_ingresos'], 2); ?></span>
        </div>
    </div>
    
    <!-- Unidades por venta -->
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Unidades por Venta</span>
            <span class="stat-valor"><?php echo number_format($resumen['promedio_unidades_por_venta'], 1); ?></span>
        </div>
    </div>
</div>

<!-- Dos tablas comparativas -->
<div class="tablas-grid-productos">
    <!-- Top por cantidad vendida -->
    <div class="tabla-card">
        <div class="tabla-card-header yellow-header">
            <i class="fas fa-crown" style="color: #f59e0b;"></i>
            <h3>Top <?php echo $limite; ?> - Más Vendidos (por Unidades)</h3>
        </div>
        <div class="tabla-responsive">
            <table class="tabla-productos-masvendidos">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-center">Vendido</th>
                        <th class="text-right">Unidades</th>
                        <th class="text-right">Ingresos</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($top_cantidad) > 0): 
                        $posicion = 1;
                        foreach($top_cantidad as $p): 
                    ?>
                    <tr>
                        <td class="text-center">
                            <?php if ($posicion == 1): ?>
                                <span class="medalla oro">🥇</span>
                            <?php elseif ($posicion == 2): ?>
                                <span class="medalla plata">🥈</span>
                            <?php elseif ($posicion == 3): ?>
                                <span class="medalla bronce">🥉</span>
                            <?php else: ?>
                                <span class="medalla numero"><?php echo $posicion; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="producto-miniatura">
                                <div class="producto-imagen-small">
                                    <img src="<?php echo $p['imagen_url'] ?: '/nuevo/assets/images/no-image.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                                </div>
                                <div class="producto-info-small">
                                    <p class="producto-nombre-small"><?php echo htmlspecialchars($p['nombre']); ?></p>
                                    <p class="producto-codigo-small"><?php echo $p['codigo_barras'] ?: 'Sin código'; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="col-categoria"><?php echo $p['categoria'] ?: '-'; ?></td>
                        <td class="text-center col-veces"><?php echo $p['veces_vendido']; ?>x</td>
                        <td class="text-right col-unidades"><?php echo number_format($p['total_unidades']); ?></td>
                        <td class="text-right col-ingresos">$<?php echo number_format($p['total_ingresos'], 2); ?></td>
                        <td class="text-center">
                            <a href="../productos/ver.php?id=<?php echo $p['id']; ?>" class="accion-icon-small" title="Ver producto">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        $posicion++;
                        endforeach; 
                    else: ?>
                    <tr>
                        <td colspan="7" class="empty-message">
                            <p>No hay datos en este período</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top por ingresos generados -->
    <div class="tabla-card">
        <div class="tabla-card-header green-header">
            <i class="fas fa-dollar-sign" style="color: #10b981;"></i>
            <h3>Top <?php echo $limite; ?> - Más Vendidos (por Ingresos)</h3>
        </div>
        <div class="tabla-responsive">
            <table class="tabla-productos-masvendidos">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-center">Unidades</th>
                        <th class="text-right">Precio Prom.</th>
                        <th class="text-right">Ingresos</th>
                        <th class="text-center">%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($top_ingresos) > 0): 
                        $posicion = 1;
                        foreach($top_ingresos as $p): 
                        $porcentaje = ($p['total_ingresos'] / $resumen['total_ingresos']) * 100;
                    ?>
                    <tr>
                        <td class="text-center">
                            <?php if ($posicion == 1): ?>
                                <span class="medalla oro">🥇</span>
                            <?php elseif ($posicion == 2): ?>
                                <span class="medalla plata">🥈</span>
                            <?php elseif ($posicion == 3): ?>
                                <span class="medalla bronce">🥉</span>
                            <?php else: ?>
                                <span class="medalla numero"><?php echo $posicion; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="producto-miniatura">
                                <div class="producto-imagen-small">
                                    <img src="<?php echo $p['imagen_url'] ?: '/nuevo/assets/images/no-image.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                                </div>
                                <div class="producto-info-small">
                                    <p class="producto-nombre-small"><?php echo htmlspecialchars($p['nombre']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="col-categoria"><?php echo $p['categoria'] ?: '-'; ?></td>
                        <td class="text-center col-unidades"><?php echo number_format($p['total_unidades']); ?></td>
                        <td class="text-right col-precio">$<?php echo number_format($p['precio_promedio'] ?? 0, 2); ?></td>
                        <td class="text-right col-ingresos">$<?php echo number_format($p['total_ingresos'], 2); ?></td>
                        <td class="text-center">
                            <div class="porcentaje-barra">
                                <div class="barra-container">
                                    <div class="barra" style="width: <?php echo $porcentaje; ?>%;"></div>
                                </div>
                                <span class="porcentaje-texto"><?php echo number_format($porcentaje, 1); ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        $posicion++;
                        endforeach; 
                    else: ?>
                    <tr>
                        <td colspan="7" class="empty-message">
                            <p>No hay datos en este período</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>