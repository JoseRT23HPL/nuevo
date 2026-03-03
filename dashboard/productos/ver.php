<?php
include '../header.php';

// Datos de ejemplo para el producto (CON IMAGEN)
$producto = [
    'id' => 1,
    'sku' => 'TRP-001-24',
    'codigo_barras' => '750123456789',
    'nombre' => 'Martillo de Uña 16oz con Mango de Madera',
    'descripcion' => 'Martillo de uña profesional con cabeza forjada en acero al carbono. Mango de madera de fresno con acabado ergonómico. Ideal para carpintería y construcción.',
    'categoria_nombre' => 'Herramientas',
    'categoria_descripcion' => 'Herramientas manuales para construcción y carpintería',
    'marca_nombre' => 'Truper',
    'precio_compra' => 98.50,
    'precio_venta' => 185.00,
    'stock_actual' => 45,
    'stock_minimo' => 10,
    'activo' => true,
    'fecha_creacion' => '2024-01-15 10:30:00',
    'imagen' => 'https://via.placeholder.com/300x300?text=Martillo'
];

// Datos de movimientos de ejemplo
$movimientos = [
    [
        'fecha' => '2024-03-15 14:30:00',
        'tipo' => 'entrada',
        'cantidad' => 50,
        'stock_anterior' => 195,
        'stock_nuevo' => 245,
        'motivo' => 'Compra a proveedor',
        'usuario' => 'Admin User'
    ],
    [
        'fecha' => '2024-03-14 11:20:00',
        'tipo' => 'salida',
        'cantidad' => 2,
        'stock_anterior' => 197,
        'stock_nuevo' => 195,
        'motivo' => 'Venta #V-001234',
        'usuario' => 'María G.'
    ],
    [
        'fecha' => '2024-03-13 09:45:00',
        'tipo' => 'ajuste',
        'cantidad' => 5,
        'stock_anterior' => 192,
        'stock_nuevo' => 197,
        'motivo' => 'Ajuste por inventario',
        'usuario' => 'Carlos R.'
    ]
];

// Datos de ventas de ejemplo
$ventas = [
    [
        'fecha' => '2024-03-15',
        'folio' => 'V-001234',
        'id_venta' => 1234,
        'cantidad' => 2,
        'precio_unitario' => 185.00,
        'subtotal' => 370.00,
        'vendedor' => 'Admin User'
    ],
    [
        'fecha' => '2024-03-14',
        'folio' => 'V-001230',
        'id_venta' => 1230,
        'cantidad' => 1,
        'precio_unitario' => 185.00,
        'subtotal' => 185.00,
        'vendedor' => 'Laura M.'
    ],
    [
        'fecha' => '2024-03-12',
        'folio' => 'V-001225',
        'id_venta' => 1225,
        'cantidad' => 3,
        'precio_unitario' => 185.00,
        'subtotal' => 555.00,
        'vendedor' => 'Admin User'
    ]
];

// Estadísticas de ejemplo
$total_vendido = 45;
$ingresos = 8325.00;
$meses = ['Ene 24', 'Feb 24', 'Mar 24', 'Abr 24', 'May 24', 'Jun 24'];
$ventas_mensuales = [12, 15, 8, 10, 14, 18];
?>

<!-- Header de la página -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-box" style="color: var(--primary);"></i>
            <h1>Detalle del Producto</h1>
        </div>
        <span class="pv-badge">CONSULTA</span>
    </div>
    
    <div class="pv-header-right" style="gap: 0.75rem;">
        <a href="/dashboard/productos/editar.php?id=<?php echo $producto['id']; ?>" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="/dashboard/productos/index.php" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- ===== NUEVA SECCIÓN DE PERFIL ESTILO FACEBOOK ===== -->
<div class="producto-perfil-container">
    <div class="producto-perfil-header">
        <div class="perfil-imagen-wrapper">
            <div class="perfil-imagen">
                <img src="<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>">
            </div>
        </div>
        <div class="perfil-info">
            <h1 class="perfil-nombre"><?php echo $producto['nombre']; ?></h1>
            <div class="perfil-codigos">
                <span class="perfil-sku">SKU: <?php echo $producto['sku']; ?></span>
                <span class="perfil-barras">Código: <?php echo $producto['codigo_barras']; ?></span>
            </div>
            <div class="perfil-estado">
                <span class="badge-estado <?php echo $producto['activo'] ? 'activo' : 'inactivo'; ?>">
                    <?php echo $producto['activo'] ? 'Producto Activo' : 'Producto Inactivo'; ?>
                </span>
                <span class="badge-stock <?php 
                    echo $producto['stock_actual'] <= 0 ? 'agotado' : 
                        ($producto['stock_actual'] <= $producto['stock_minimo'] ? 'bajo' : 'normal'); 
                ?>">
                    <i class="fas fa-cubes"></i>
                    <?php echo $producto['stock_actual']; ?> unidades en stock
                </span>
            </div>
            <div class="perfil-acciones-rapidas">
                <a href="#" class="perfil-accion" onclick="ajustarStock(<?php echo $producto['id']; ?>)">
                    <i class="fas fa-cubes"></i>
                    <span>Ajustar Stock</span>
                </a>
                <a href="#" class="perfil-accion" onclick="venderProducto(<?php echo $producto['id']; ?>)">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Vender</span>
                </a>
                <a href="#" class="perfil-accion" onclick="imprimirEtiqueta(<?php echo $producto['id']; ?>)">
                    <i class="fas fa-tag"></i>
                    <span>Etiqueta</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Grid de información del producto -->
<div class="producto-info-grid">
    <!-- Columna izquierda - Información básica -->
    <div class="info-col-left">
        <!-- Clasificación -->
        <div class="info-card">
            <h3 class="card-title">
                <i class="fas fa-tags"></i>
                Clasificación
            </h3>
            <div class="card-content">
                <div class="info-row">
                    <span class="info-label">Categoría:</span>
                    <span class="info-value"><?php echo $producto['categoria_nombre']; ?></span>
                </div>
                <?php if ($producto['categoria_descripcion']): ?>
                <div class="info-row">
                    <span class="info-label"></span>
                    <span class="info-desc"><?php echo $producto['categoria_descripcion']; ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Marca:</span>
                    <span class="info-value"><?php echo $producto['marca_nombre']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Descripción -->
        <div class="info-card">
            <h3 class="card-title">
                <i class="fas fa-align-left"></i>
                Descripción
            </h3>
            <div class="card-content">
                <p class="descripcion-texto"><?php echo nl2br($producto['descripcion']); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Columna derecha - Precios y stock -->
    <div class="info-col-right">
        <!-- Precios -->
        <div class="info-card">
            <h3 class="card-title">
                <i class="fas fa-dollar-sign"></i>
                Precios
            </h3>
            <div class="card-content">
                <div class="precios-grid">
                    <div class="precio-item">
                        <span class="precio-label">Precio de Compra</span>
                        <span class="precio-valor compra">$<?php echo number_format($producto['precio_compra'], 2); ?></span>
                    </div>
                    <div class="precio-item">
                        <span class="precio-label">Precio de Venta</span>
                        <span class="precio-valor venta">$<?php echo number_format($producto['precio_venta'], 2); ?></span>
                    </div>
                    <?php 
                    $ganancia = $producto['precio_venta'] - $producto['precio_compra'];
                    $porcentaje = $producto['precio_compra'] > 0 ? ($ganancia / $producto['precio_compra']) * 100 : 0;
                    ?>
                    <div class="precio-item ganancia">
                        <span class="precio-label">Ganancia Estimada</span>
                        <span class="precio-valor <?php echo $ganancia >= 0 ? 'positiva' : 'negativa'; ?>">
                            $<?php echo number_format($ganancia, 2); ?>
                        </span>
                        <span class="porcentaje">(<?php echo number_format($porcentaje, 1); ?>%)</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stock -->
        <div class="info-card">
            <h3 class="card-title">
                <i class="fas fa-cubes"></i>
                Inventario
            </h3>
            <div class="card-content">
                <div class="stock-grid">
                    <div class="stock-item">
                        <span class="stock-label">Stock Actual</span>
                        <span class="stock-valor <?php 
                            echo $producto['stock_actual'] <= 0 ? 'cero' : 
                                ($producto['stock_actual'] <= $producto['stock_minimo'] ? 'bajo' : 'normal'); 
                        ?>">
                            <?php echo $producto['stock_actual']; ?>
                        </span>
                        <span class="stock-unidad">unidades</span>
                    </div>
                    
                    <div class="stock-item">
                        <span class="stock-label">Stock Mínimo</span>
                        <span class="stock-valor minimo"><?php echo $producto['stock_minimo']; ?></span>
                        <span class="stock-unidad">unidades</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pestañas de información adicional -->
<div class="tabs-container">
    <div class="tabs-header">
        <button class="tab-btn active" onclick="showTab('movimientos')">
            <i class="fas fa-history"></i>
            Movimientos
        </button>
        <button class="tab-btn" onclick="showTab('ventas')">
            <i class="fas fa-shopping-cart"></i>
            Ventas
        </button>
        <button class="tab-btn" onclick="showTab('estadisticas')">
            <i class="fas fa-chart-line"></i>
            Estadísticas
        </button>
    </div>
    
    <div class="tabs-content">
        <!-- Pestaña Movimientos -->
        <div id="tab-movimientos" class="tab-pane active">
            <div class="tab-header">
                <h3>Últimos movimientos de inventario</h3>
                <a href="/dashboard/inventario/movimientos.php?producto=<?php echo $producto['id']; ?>" class="tab-link">
                    Ver todos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (count($movimientos) > 0): ?>
                <div class="tabla-responsive">
                    <table class="tabla-movimientos">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-center">Stock</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td class="fecha-mov">
                                    <?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?>
                                </td>
                                <td>
                                    <span class="tipo-badge <?php echo $mov['tipo']; ?>">
                                        <?php 
                                        echo $mov['tipo'] == 'entrada' ? '➕ Entrada' : 
                                            ($mov['tipo'] == 'salida' ? '➖ Salida' : '✏️ Ajuste'); 
                                        ?>
                                    </span>
                                </td>
                                <td class="text-right cantidad <?php echo $mov['tipo']; ?>">
                                    <?php echo $mov['cantidad']; ?>
                                </td>
                                <td class="text-center stock-evolucion">
                                    <?php echo $mov['stock_anterior']; ?> → <?php echo $mov['stock_nuevo']; ?>
                                </td>
                                <td><?php echo $mov['motivo']; ?></td>
                                <td><?php echo $mov['usuario']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>No hay movimientos registrados</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pestaña Ventas -->
        <div id="tab-ventas" class="tab-pane hidden">
            <div class="tab-header">
                <h3>Ventas recientes de este producto</h3>
                <a href="/dashboard/reportes/productos.php?producto=<?php echo $producto['id']; ?>" class="tab-link">
                    Ver todas <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (count($ventas) > 0): ?>
                <div class="tabla-responsive">
                    <table class="tabla-ventas">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Folio</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Subtotal</th>
                                <th>Vendedor</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></td>
                                <td>
                                    <a href="/dashboard/ventas/ver.php?id=<?php echo $venta['id_venta']; ?>" class="folio-link">
                                        #<?php echo $venta['folio']; ?>
                                    </a>
                                </td>
                                <td class="text-right cantidad-venta"><?php echo $venta['cantidad']; ?></td>
                                <td class="text-right">$<?php echo number_format($venta['precio_unitario'], 2); ?></td>
                                <td class="text-right subtotal">$<?php echo number_format($venta['subtotal'], 2); ?></td>
                                <td><?php echo $venta['vendedor']; ?></td>
                                <td class="text-center">
                                    <a href="/dashboard/ventas/ver.php?id=<?php echo $venta['id_venta']; ?>" class="accion-icon" title="Ver venta">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <p>No hay ventas registradas de este producto</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pestaña Estadísticas -->
        <div id="tab-estadisticas" class="tab-pane hidden">
            <div class="estadisticas-grid">
                <div class="estadistica-card">
                    <div class="estadistica-icon">
                        <i class="fas fa-chart-line" style="color: var(--primary);"></i>
                    </div>
                    <div class="estadistica-info">
                        <span class="estadistica-label">Ventas totales</span>
                        <span class="estadistica-valor"><?php echo $total_vendido; ?></span>
                        <span class="estadistica-unidad">unidades vendidas</span>
                    </div>
                </div>
                
                <div class="estadistica-card">
                    <div class="estadistica-icon" style="background: var(--success-light);">
                        <i class="fas fa-dollar-sign" style="color: var(--success);"></i>
                    </div>
                    <div class="estadistica-info">
                        <span class="estadistica-label">Ingresos generados</span>
                        <span class="estadistica-valor">$<?php echo number_format($ingresos, 2); ?></span>
                    </div>
                </div>
                
                <div class="estadistica-card">
                    <div class="estadistica-icon" style="background: var(--secondary-alpha);">
                        <i class="fas fa-calendar" style="color: var(--secondary);"></i>
                    </div>
                    <div class="estadistica-info">
                        <span class="estadistica-label">Fecha de creación</span>
                        <span class="estadistica-valor"><?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de ventas -->
            <div class="grafico-container">
                <h4 class="grafico-titulo">Ventas por mes</h4>
                <div class="grafico-wrapper">
                    <canvas id="ventasChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Función para cambiar de pestaña
function showTab(tabName) {
    // Ocultar todos los contenidos
    document.querySelectorAll('.tab-pane').forEach(tab => {
        tab.classList.remove('active');
        tab.classList.add('hidden');
    });
    
    // Desactivar todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar el tab seleccionado
    const tabContent = document.getElementById('tab-' + tabName);
    tabContent.classList.remove('hidden');
    tabContent.classList.add('active');
    
    // Activar el botón clickeado
    event.target.classList.add('active');
}

// Funciones de acción
function ajustarStock(id) {
    window.location.href = 'ajustar_stock.php?id=' + id;
}

function venderProducto(id) {
    window.location.href = '/dashboard/ventas/index.php?agregar=' + id;
}

function imprimirEtiqueta(id) {
    window.open('etiqueta.php?id=' + id, '_blank', 'width=400,height=300');
}

function verHistorial(id) {
    window.location.href = 'movimientos.php?id=' + id;
}

// Gráfico de ventas
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('ventasChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($meses); ?>,
                datasets: [{
                    label: 'Unidades vendidas',
                    data: <?php echo json_encode($ventas_mensuales); ?>,
                    borderColor: 'var(--primary)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'white',
                    pointBorderColor: 'var(--primary)',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'var(--gray-200)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>

<?php include '../footer.php'; ?>