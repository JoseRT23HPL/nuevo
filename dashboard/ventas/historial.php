<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener filtros actuales
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$buscar = $_GET['buscar'] ?? '';

// Construir condiciones WHERE
$condiciones = [];
$params = [];
$types = "";

// Filtro por fecha
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $condiciones[] = "DATE(v.fecha_venta) BETWEEN ? AND ?";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
    $types .= "ss";
}

// Filtro por búsqueda (folio, nombre de usuario)
if (!empty($buscar)) {
    $condiciones[] = "(v.folio LIKE ? OR u.nombre LIKE ? OR u.username LIKE ?)";
    $buscar_param = "%$buscar%";
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $types .= "sss";
}

$where = empty($condiciones) ? "1=1" : implode(" AND ", $condiciones);

// ===== ESTADÍSTICAS =====
$stats_sql = "
    SELECT 
        COUNT(*) as total_ventas,
        COALESCE(SUM(total), 0) as total_ingresos,
        COALESCE(AVG(total), 0) as ticket_promedio
    FROM ventas v
    WHERE $where
";

$stats_stmt = $conn->prepare($stats_sql);
if (!empty($params)) {
    $stats_stmt->bind_param($types, ...$params);
}
$stats_stmt->execute();
$estadisticas = $stats_stmt->get_result()->fetch_assoc();

// ===== VENTAS =====
$sql = "
    SELECT v.*, u.nombre as cajero_nombre, u.username,
           (SELECT COUNT(*) FROM detalle_ventas WHERE id_venta = v.id) as total_productos
    FROM ventas v
    JOIN usuarios u ON v.id_usuario = u.id
    WHERE $where
    ORDER BY v.fecha_venta DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$ventas = [];
while ($row = $result->fetch_assoc()) {
    $ventas[] = $row;
}

include '../header.php';
?>

<!-- Header del Historial - CORREGIDO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-history"></i>
            <h1>Historial de Ventas</h1>
        </div>
        <span class="pv-badge">CONSULTA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/ventas/index.php'); ?>" class="btn-nueva-venta">
            <i class="fas fa-plus-circle"></i>
            Nueva Venta
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <div class="filtros-grid">
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
            
            <!-- Buscar -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-search"></i>
                    Buscar
                </label>
                <input type="text" name="buscar" placeholder="Folio o cajero..." value="<?php echo h($buscar); ?>" class="filtro-input">
            </div>
            
            <!-- Botones -->
            <div class="filtro-botones">
                <button type="submit" class="btn-filtro btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
                
                <a href="<?php echo url('dashboard/ventas/historial.php'); ?>" class="btn-filtro btn-secondary">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Estadísticas -->
<div class="estadisticas-grid">
    <!-- Total ventas -->
    <div class="estadistica-card">
        <div class="estadistica-icon blue">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Total de ventas</span>
            <span class="estadistica-valor"><?php echo $estadisticas['total_ventas']; ?></span>
        </div>
    </div>
    
    <!-- Ingresos totales -->
    <div class="estadistica-card">
        <div class="estadistica-icon green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Ingresos totales</span>
            <span class="estadistica-valor">$<?php echo number_format($estadisticas['total_ingresos'], 2); ?></span>
        </div>
    </div>
    
    <!-- Ticket promedio -->
    <div class="estadistica-card">
        <div class="estadistica-icon yellow">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Ticket promedio</span>
            <span class="estadistica-valor">$<?php echo number_format($estadisticas['ticket_promedio'], 2); ?></span>
        </div>
    </div>
</div>

<!-- Tabla de ventas -->
<div class="tabla-container">
    <table class="tabla-historial">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Cajero</th>
                <th class="text-center">Productos</th>
                <th class="text-center">Método</th>
                <th class="text-right">Total</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($ventas) > 0): ?>
                <?php foreach ($ventas as $v): ?>
                <tr class="fila-venta">
                    <td class="col-folio">
                        <span class="folio-numero"><?php echo $v['folio']; ?></span>
                    </td>
                    <td class="col-fecha">
                        <span class="fecha-dia"><?php echo date('d/m/Y', strtotime($v['fecha_venta'])); ?></span>
                        <span class="fecha-hora"><?php echo date('H:i', strtotime($v['fecha_venta'])); ?></span>
                    </td>
                    <td class="col-cajero">
                        <span class="cajero-nombre"><?php echo h($v['cajero_nombre'] ?: $v['username']); ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge-productos"><?php echo $v['total_productos']; ?></span>
                    </td>
                    <td class="text-center">
                        <?php
                        $metodo_class = '';
                        $metodo_text = '';
                        switch($v['metodo_pago']) {
                            case 'efectivo':
                                $metodo_class = 'efectivo';
                                $metodo_text = 'Efectivo';
                                break;
                            case 'transferencia':
                                $metodo_class = 'transferencia';
                                $metodo_text = 'Transferencia';
                                break;
                            default:
                                $metodo_class = 'efectivo';
                                $metodo_text = ucfirst($v['metodo_pago']);
                        }
                        ?>
                        <span class="badge-metodo <?php echo $metodo_class; ?>"><?php echo $metodo_text; ?></span>
                    </td>
                    <td class="col-total">$<?php echo number_format($v['total'], 2); ?></td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="<?php echo url('dashboard/ventas/ticket.php?id=' . $v['id']); ?>" class="accion-icon" title="Ver ticket" target="_blank">
                                <i class="fas fa-receipt"></i>
                            </a>
                            <a href="<?php echo url('dashboard/ventas/detalle.php?id=' . $v['id']); ?>" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-state-row">
                        <div class="empty-state-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3>No hay ventas en este período</h3>
                        <p>Prueba con otros filtros o realiza una nueva venta</p>
                        <a href="<?php echo url('dashboard/ventas/index.php'); ?>" class="btn-header primary" style="display: inline-block; margin-top: 1rem;">
                            <i class="fas fa-plus"></i>
                            Nueva Venta
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Resumen de resultados -->
    <?php if (count($ventas) > 0): ?>
    <div class="tabla-footer">
        <p class="resultados-info">
            <i class="fas fa-info-circle"></i>
            Mostrando: <strong><?php echo count($ventas); ?> ventas</strong> 
            <?php if ($fecha_inicio && $fecha_fin): ?>
                del período <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> al <?php echo date('d/m/Y', strtotime($fecha_fin)); ?>
            <?php endif; ?>
            <?php if (!empty($buscar)): ?>
                con búsqueda "<?php echo h($buscar); ?>"
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>