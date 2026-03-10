<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener filtros actuales
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipo_filtro = $_GET['tipo'] ?? '';
$producto_filtro = $_GET['producto'] ?? '';

// Construir condiciones WHERE
$condiciones = [];
$params = [];
$types = "";

// Filtro por fecha
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $condiciones[] = "DATE(m.fecha_movimiento) BETWEEN ? AND ?";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
    $types .= "ss";
}

// Filtro por tipo
if (!empty($tipo_filtro)) {
    $condiciones[] = "m.tipo = ?";
    $params[] = $tipo_filtro;
    $types .= "s";
}

// Filtro por producto (búsqueda en nombre)
if (!empty($producto_filtro)) {
    $condiciones[] = "p.nombre LIKE ?";
    $params[] = "%$producto_filtro%";
    $types .= "s";
}

$where = empty($condiciones) ? "1=1" : implode(" AND ", $condiciones);

// Obtener movimientos con filtros
$sql = "
    SELECT m.*, p.nombre as producto, p.codigo_barras, p.sku, u.username 
    FROM movimientos_inventario m
    JOIN productos p ON m.id_producto = p.id
    LEFT JOIN usuarios u ON m.id_usuario = u.id
    WHERE $where
    ORDER BY m.fecha_movimiento DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$movimientos = [];
while ($row = $result->fetch_assoc()) {
    $movimientos[] = $row;
}

// Calcular estadísticas
$total_entradas = count(array_filter($movimientos, function($m) { return $m['tipo'] == 'entrada'; }));
$total_salidas = count(array_filter($movimientos, function($m) { return $m['tipo'] == 'salida'; }));
$total_ajustes = count(array_filter($movimientos, function($m) { return $m['tipo'] == 'ajuste'; }));

include '../header.php';
?>

<!-- Header de la página -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-history"></i>
            <h1>Historial de Movimientos</h1>
        </div>
        <span class="pv-badge">INVENTARIO</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/inventario/index.php'); ?>" class="btn-header">
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
                
                <a href="<?php echo url('dashboard/inventario/movimientos.php'); ?>" class="btn-filtro btn-secondary" style="text-decoration: none; text-align: center;">
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
                    <th>Código/SKU</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Stock Ant.</th>
                    <th class="text-right">Stock Nuevo</th>
                    <th>Motivo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimientos) > 0): ?>
                    <?php foreach ($movimientos as $m): ?>
                    <tr class="fila-movimiento">
                        <td class="col-fecha">
                            <span class="fecha-dia"><?php echo date('d/m/Y', strtotime($m['fecha_movimiento'])); ?></span>
                            <span class="fecha-hora"><?php echo date('H:i', strtotime($m['fecha_movimiento'])); ?></span>
                        </td>
                        <td class="col-producto">
                            <span class="producto-nombre"><?php echo h($m['producto']); ?></span>
                            <small class="producto-sku">SKU: <?php echo h($m['sku']); ?></small>
                        </td>
                        <td class="col-codigo">
                            <span class="codigo-valor"><?php echo $m['codigo_barras'] ?: '—'; ?></span>
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
                        <td class="col-motivo" title="<?php echo h($m['motivo']); ?>">
                            <?php echo h($m['motivo']); ?>
                        </td>
                        <td class="col-usuario"><?php echo $m['username'] ?: 'Sistema'; ?></td>
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
                                <a href="<?php echo url('dashboard/inventario/index.php'); ?>" class="btn-primary">
                                    <i class="fas fa-arrow-left"></i>
                                    Volver al inventario
                                </a>
                                <a href="<?php echo url('dashboard/productos/entrada_rapida.php'); ?>" class="btn-success">
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
    <?php if (count($movimientos) > 0): ?>
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-chart-bar"></i>
                Total de movimientos: <strong><?php echo count($movimientos); ?></strong>
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

/* Mejoras en la tabla */
.col-producto {
    min-width: 180px;
}

.producto-sku {
    display: block;
    font-size: 0.7rem;
    color: var(--gray-500);
    margin-top: 0.2rem;
}

.codigo-valor {
    font-size: 0.8rem;
    color: var(--gray-600);
    background-color: var(--gray-100);
    padding: 0.2rem 0.5rem;
    border-radius: var(--radius-md);
    font-family: monospace;
}

/* Responsive */
@media (max-width: 1024px) {
    .filtros-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
    
    .filtro-group[style*="span 2"] {
        grid-column: span 3 !important;
    }
    
    .filtro-botones {
        grid-column: span 3 !important;
    }
}

@media (max-width: 768px) {
    .filtros-grid {
        grid-template-columns: 1fr !important;
    }
    
    .filtro-group[style*="span 2"],
    .filtro-botones {
        grid-column: span 1 !important;
    }
}
</style>

<?php include '../footer.php'; ?>