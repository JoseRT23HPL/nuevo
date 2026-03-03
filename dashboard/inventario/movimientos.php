<?php
include '../header.php';

// Datos de ejemplo para movimientos de inventario
$movimientos = [
    [
        'fecha' => '2024-03-15 14:30:00',
        'producto' => 'Martillo de Uña 16oz',
        'codigo_barras' => '750123456789',
        'tipo' => 'entrada',
        'cantidad' => 50,
        'stock_anterior' => 195,
        'stock_nuevo' => 245,
        'motivo' => 'Compra a proveedor',
        'usuario' => 'Admin User'
    ],
    [
        'fecha' => '2024-03-15 11:20:00',
        'producto' => 'Taladro Percutor 500W',
        'codigo_barras' => '750123456788',
        'tipo' => 'salida',
        'cantidad' => 2,
        'stock_anterior' => 10,
        'stock_nuevo' => 8,
        'motivo' => 'Venta #V-001234',
        'usuario' => 'María G.'
    ],
    [
        'fecha' => '2024-03-14 09:45:00',
        'producto' => 'Caja de Tornillos 1/2"',
        'codigo_barras' => '750123456787',
        'tipo' => 'ajuste',
        'cantidad' => 5,
        'stock_anterior' => 20,
        'stock_nuevo' => 25,
        'motivo' => 'Ajuste por inventario',
        'usuario' => 'Carlos R.'
    ],
    [
        'fecha' => '2024-03-14 16:20:00',
        'producto' => 'Cemento Portland Gris 50kg',
        'codigo_barras' => '750123456786',
        'tipo' => 'entrada',
        'cantidad' => 100,
        'stock_anterior' => 50,
        'stock_nuevo' => 150,
        'motivo' => 'Compra a proveedor',
        'usuario' => 'Admin User'
    ],
    [
        'fecha' => '2024-03-13 10:15:00',
        'producto' => 'Pintura Blanca 20L',
        'codigo_barras' => '750123456785',
        'tipo' => 'salida',
        'cantidad' => 3,
        'stock_anterior' => 15,
        'stock_nuevo' => 12,
        'motivo' => 'Venta #V-001230',
        'usuario' => 'Laura M.'
    ],
    [
        'fecha' => '2024-03-13 08:30:00',
        'producto' => 'Cable Eléctrico Calibre 12',
        'codigo_barras' => '750123456784',
        'tipo' => 'ajuste',
        'cantidad' => 2,
        'stock_anterior' => 18,
        'stock_nuevo' => 20,
        'motivo' => 'Ajuste por conteo',
        'usuario' => 'Sistema'
    ],
    [
        'fecha' => '2024-03-12 15:40:00',
        'producto' => 'Disco de Corte 7"',
        'codigo_barras' => '750123456783',
        'tipo' => 'entrada',
        'cantidad' => 30,
        'stock_anterior' => 45,
        'stock_nuevo' => 75,
        'motivo' => 'Compra a proveedor',
        'usuario' => 'Admin User'
    ],
    [
        'fecha' => '2024-03-12 12:10:00',
        'producto' => 'Guantes de Seguridad',
        'codigo_barras' => '750123456782',
        'tipo' => 'salida',
        'cantidad' => 5,
        'stock_anterior' => 25,
        'stock_nuevo' => 20,
        'motivo' => 'Venta #V-001228',
        'usuario' => 'María G.'
    ]
];

// Obtener filtros actuales
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipo_filtro = $_GET['tipo'] ?? '';
$producto_filtro = $_GET['producto'] ?? '';

// Filtrar movimientos (simulación)
$movimientos_filtrados = array_filter($movimientos, function($m) use ($fecha_inicio, $fecha_fin, $tipo_filtro, $producto_filtro) {
    // Filtro por fecha
    $fecha_mov = date('Y-m-d', strtotime($m['fecha']));
    if ($fecha_mov < $fecha_inicio || $fecha_mov > $fecha_fin) return false;
    
    // Filtro por tipo
    if (!empty($tipo_filtro) && $m['tipo'] != $tipo_filtro) return false;
    
    // Filtro por producto
    if (!empty($producto_filtro) && stripos($m['producto'], $producto_filtro) === false) return false;
    
    return true;
});

// Calcular estadísticas
$total_entradas = count(array_filter($movimientos_filtrados, function($m) { return $m['tipo'] == 'entrada'; }));
$total_salidas = count(array_filter($movimientos_filtrados, function($m) { return $m['tipo'] == 'salida'; }));
$total_ajustes = count(array_filter($movimientos_filtrados, function($m) { return $m['tipo'] == 'ajuste'; }));
?>

<!-- Header de la página -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-history" style="color: var(--primary);"></i>
            <h1>Historial de Movimientos</h1>
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
        <div class="filtros-grid" style="grid-template-columns: repeat(6, 1fr);">
            <!-- Fecha inicio -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar"></i>
                    Fecha inicio
                </label>
                <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" class="filtro-input">
            </div>
            
            <!-- Fecha fin -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar-check"></i>
                    Fecha fin
                </label>
                <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>" class="filtro-input">
            </div>
            
            <!-- Tipo -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-filter"></i>
                    Tipo
                </label>
                <select name="tipo" class="filtro-select">
                    <option value="">Todos los tipos</option>
                    <option value="entrada" <?php echo $tipo_filtro == 'entrada' ? 'selected' : ''; ?>>✅ Entradas</option>
                    <option value="salida" <?php echo $tipo_filtro == 'salida' ? 'selected' : ''; ?>>❌ Salidas</option>
                    <option value="ajuste" <?php echo $tipo_filtro == 'ajuste' ? 'selected' : ''; ?>>⚖️ Ajustes</option>
                </select>
            </div>
            
            <!-- Producto -->
            <div class="filtro-group" style="grid-column: span 2;">
                <label class="filtro-label">
                    <i class="fas fa-box"></i>
                    Producto
                </label>
                <input type="text" name="producto" placeholder="Buscar producto..." 
                       value="<?php echo htmlspecialchars($producto_filtro); ?>" class="filtro-input">
            </div>
            
            <!-- Botones -->
            <div class="filtro-botones" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                <button type="submit" class="btn-filtro btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
                
                <a href="/dashboard/inventario/movimientos.php" class="btn-filtro btn-secondary" style="text-decoration: none; text-align: center;">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tabla de movimientos -->
<div class="tabla-container">
    <div class="tabla-responsive">
        <table class="tabla-movimientos">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Código</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Stock Ant.</th>
                    <th class="text-right">Stock Nuevo</th>
                    <th>Motivo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimientos_filtrados) > 0): ?>
                    <?php foreach ($movimientos_filtrados as $m): ?>
                    <tr class="fila-movimiento">
                        <td class="col-fecha">
                            <span class="fecha-dia"><?php echo date('d/m/Y', strtotime($m['fecha'])); ?></span>
                            <span class="fecha-hora"><?php echo date('H:i', strtotime($m['fecha'])); ?></span>
                        </td>
                        <td class="col-producto">
                            <span class="producto-nombre"><?php echo $m['producto']; ?></span>
                        </td>
                        <td class="col-codigo">
                            <span class="codigo-valor"><?php echo $m['codigo_barras']; ?></span>
                        </td>
                        <td class="text-center">
                            <?php
                            $tipo_class = '';
                            $tipo_text = '';
                            $tipo_icon = '';
                            switch($m['tipo']) {
                                case 'entrada':
                                    $tipo_class = 'entrada';
                                    $tipo_text = 'Entrada';
                                    $tipo_icon = 'fa-arrow-down';
                                    break;
                                case 'salida':
                                    $tipo_class = 'salida';
                                    $tipo_text = 'Salida';
                                    $tipo_icon = 'fa-arrow-up';
                                    break;
                                default:
                                    $tipo_class = 'ajuste';
                                    $tipo_text = 'Ajuste';
                                    $tipo_icon = 'fa-balance-scale';
                            }
                            ?>
                            <span class="tipo-badge <?php echo $tipo_class; ?>">
                                <i class="fas <?php echo $tipo_icon; ?>"></i>
                                <?php echo $tipo_text; ?>
                            </span>
                        </td>
                        <td class="text-right cantidad <?php echo $m['tipo']; ?>">
                            <?php echo $m['cantidad']; ?>
                        </td>
                        <td class="text-right stock-anterior"><?php echo $m['stock_anterior']; ?></td>
                        <td class="text-right stock-nuevo <?php 
                            echo $m['stock_nuevo'] > $m['stock_anterior'] ? 'positivo' : 
                                ($m['stock_nuevo'] < $m['stock_anterior'] ? 'negativo' : ''); 
                        ?>">
                            <?php echo $m['stock_nuevo']; ?>
                        </td>
                        <td class="col-motivo" title="<?php echo $m['motivo']; ?>">
                            <?php echo $m['motivo']; ?>
                        </td>
                        <td class="col-usuario"><?php echo $m['usuario']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-state-row">
                            <div class="empty-state-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <h3>No hay movimientos registrados</h3>
                            <p>Prueba con otros filtros o realiza algún movimiento</p>
                            <div class="empty-state-actions">
                                <a href="/dashboard/inventario/index.php" class="btn-primary">
                                    <i class="fas fa-arrow-left"></i>
                                    Volver al inventario
                                </a>
                                <a href="/dashboard/productos/entrada_rapida.php" class="btn-success">
                                    <i class="fas fa-barcode"></i>
                                    Registrar entrada
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <?php if (count($movimientos_filtrados) > 0): ?>
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-chart-bar"></i>
                Total de movimientos: <strong><?php echo count($movimientos_filtrados); ?></strong>
            </p>
            <div class="resumen-stats">
                <span class="stat-badge entrada">
                    <span class="stat-dot" style="background: var(--success);"></span>
                    Entradas: <strong><?php echo $total_entradas; ?></strong>
                </span>
                <span class="stat-badge salida">
                    <span class="stat-dot" style="background: var(--danger);"></span>
                    Salidas: <strong><?php echo $total_salidas; ?></strong>
                </span>
                <span class="stat-badge ajuste">
                    <span class="stat-dot" style="background: #f59e0b;"></span>
                    Ajustes: <strong><?php echo $total_ajustes; ?></strong>
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

.fila-movimiento {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-movimiento:nth-child(1) { animation-delay: 0.02s; }
.fila-movimiento:nth-child(2) { animation-delay: 0.04s; }
.fila-movimiento:nth-child(3) { animation-delay: 0.06s; }
.fila-movimiento:nth-child(4) { animation-delay: 0.08s; }
.fila-movimiento:nth-child(5) { animation-delay: 0.10s; }
.fila-movimiento:nth-child(6) { animation-delay: 0.12s; }
.fila-movimiento:nth-child(7) { animation-delay: 0.14s; }
.fila-movimiento:nth-child(8) { animation-delay: 0.16s; }
</style>

<?php include '../footer.php'; ?>