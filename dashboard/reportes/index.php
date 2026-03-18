<?php
// file: /dashboard/reportes/index.php - VERSIÓN CONECTADA A BD Y CORREGIDA
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener el usuario actual
$usuario_actual = getCurrentUser();

// Definir fechas
$hoy = date('Y-m-d');
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$inicio_mes = date('Y-m-01');

// ===== 1. VENTAS DE HOY =====
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_ventas,
        COALESCE(SUM(total), 0) as total_ingresos,
        COALESCE(SUM(CASE WHEN metodo_pago = 'efectivo' THEN total ELSE 0 END), 0) as efectivo,
        COALESCE(SUM(CASE WHEN metodo_pago = 'transferencia' THEN total ELSE 0 END), 0) as transferencia
    FROM ventas 
    WHERE DATE(fecha_venta) = ? AND estado = 'completada'
");
$stmt->bind_param("s", $hoy);
$stmt->execute();
$ventas_hoy = $stmt->get_result()->fetch_assoc();

// ===== 2. VENTAS DE LA SEMANA =====
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_ventas,
        COALESCE(SUM(total), 0) as total_ingresos
    FROM ventas 
    WHERE DATE(fecha_venta) >= ? AND estado = 'completada'
");
$stmt->bind_param("s", $inicio_semana);
$stmt->execute();
$ventas_semana = $stmt->get_result()->fetch_assoc();

// ===== 3. VENTAS DEL MES =====
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_ventas,
        COALESCE(SUM(total), 0) as total_ingresos
    FROM ventas 
    WHERE DATE(fecha_venta) >= ? AND estado = 'completada'
");
$stmt->bind_param("s", $inicio_mes);
$stmt->execute();
$ventas_mes = $stmt->get_result()->fetch_assoc();

// ===== 4. PRODUCTOS MÁS VENDIDOS (TOP 5) =====
$productos_top = $conn->query("
    SELECT 
        p.id,
        p.nombre,
        p.imagen_url,
        COALESCE(SUM(dv.cantidad), 0) as total_vendido,
        COALESCE(SUM(dv.subtotal), 0) as total_ingresos
    FROM productos p
    INNER JOIN detalle_ventas dv ON p.id = dv.id_producto
    INNER JOIN ventas v ON dv.id_venta = v.id
    WHERE v.estado = 'completada'
        AND v.fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY p.id, p.nombre, p.imagen_url
    ORDER BY total_vendido DESC
    LIMIT 5
");

// ===== 5. DATOS PARA EL GRÁFICO (ÚLTIMOS 7 DÍAS) =====
$fechas = [];
$totales = [];

for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $fecha_label = date('d/m', strtotime($fecha));
    $fechas[] = $fecha_label;
    
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total), 0) as total
        FROM ventas 
        WHERE DATE(fecha_venta) = ? AND estado = 'completada'
    ");
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    $totales[] = (float)$total;
}

// ===== 6. ÚLTIMOS CORTES DE CAJA =====
$ultimos_cortes = $conn->query("
    SELECT 
        c.*,
        u.username
    FROM cortes_caja c
    JOIN usuarios u ON c.id_usuario = u.id
    WHERE c.fecha_cierre IS NOT NULL
    ORDER BY c.fecha_cierre DESC
    LIMIT 5
");

// ===== 7. VERIFICAR SI HAY CORTE ABIERTO =====
$stmt = $conn->prepare("
    SELECT id FROM cortes_caja 
    WHERE id_usuario = ? AND fecha_cierre IS NULL
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$corte_abierto = $stmt->get_result()->num_rows > 0;

include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-chart-line"></i>
            <h1>Reportes y Estadísticas</h1>
        </div>
        <span class="pv-badge">INFORMES</span>
    </div>
    
    <div class="pv-header-right">
        <?php if ($corte_abierto): ?>
            <a href="corte_caja.php" class="btn-header warning">
                <i class="fas fa-cash-register"></i>
                Cerrar Corte
            </a>
        <?php else: ?>
            <a href="corte_caja.php?action=abrir" class="btn-header success">
                <i class="fas fa-cash-register"></i>
                Abrir Corte
            </a>
        <?php endif; ?>
        <a href="historial_cortes.php" class="btn-header purple">
            <i class="fas fa-history"></i>
            Historial
        </a>
    </div>
</div>

<!-- Subtítulo con nombre personalizado -->
<p class="page-subtitle">
    Hola <?php echo h($usuario_actual['nombre'] ?? $usuario_actual['username']); ?>, 
    aquí tienes el rendimiento de tu negocio
</p>

<!-- Resumen de ventas -->
<div class="reportes-grid">
    <!-- Ventas de Hoy -->
    <div class="reporte-card">
        <div class="reporte-header">
            <div class="reporte-icono blue">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="reporte-info">
                <span class="reporte-label">Ventas de Hoy</span>
                <span class="reporte-valor">$<?php echo number_format($ventas_hoy['total_ingresos'] ?? 0, 2); ?></span>
                <span class="reporte-sub"><?php echo $ventas_hoy['total_ventas'] ?? 0; ?> ventas</span>
            </div>
        </div>
        <div class="reporte-detalles">
            <div class="detalle-item">
                <span class="detalle-label">Efectivo</span>
                <span class="detalle-valor">$<?php echo number_format($ventas_hoy['efectivo'] ?? 0, 0); ?></span>
            </div>
            <div class="detalle-item">
                <span class="detalle-label">Transferencia</span>
                <span class="detalle-valor">$<?php echo number_format($ventas_hoy['transferencia'] ?? 0, 0); ?></span>
            </div>
            <?php if (($ventas_hoy['total_ingresos'] ?? 0) > 0): ?>
            <div class="detalle-item">
                <span class="detalle-label">Ticket prom.</span>
                <span class="detalle-valor">$<?php echo number_format(($ventas_hoy['total_ingresos'] / $ventas_hoy['total_ventas']), 2); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Ventas de la Semana -->
    <div class="reporte-card">
        <div class="reporte-header">
            <div class="reporte-icono green">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="reporte-info">
                <span class="reporte-label">Esta Semana</span>
                <span class="reporte-valor">$<?php echo number_format($ventas_semana['total_ingresos'] ?? 0, 2); ?></span>
                <span class="reporte-sub"><?php echo $ventas_semana['total_ventas'] ?? 0; ?> ventas</span>
            </div>
        </div>
        <div class="reporte-periodo">
            <span class="periodo-texto">
                Desde <?php echo date('d/m', strtotime($inicio_semana)); ?> al <?php echo date('d/m', strtotime($hoy)); ?>
            </span>
        </div>
    </div>
    
    <!-- Ventas del Mes -->
    <div class="reporte-card">
        <div class="reporte-header">
            <div class="reporte-icono yellow">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="reporte-info">
                <span class="reporte-label">Este Mes</span>
                <span class="reporte-valor">$<?php echo number_format($ventas_mes['total_ingresos'] ?? 0, 2); ?></span>
                <span class="reporte-sub"><?php echo $ventas_mes['total_ventas'] ?? 0; ?> ventas</span>
            </div>
        </div>
        <div class="reporte-periodo">
            <span class="periodo-texto"><?php echo strftime('%B %Y'); ?></span>
        </div>
    </div>
</div>

<!-- Gráfico de ventas -->
<div class="grafico-container">
    <div class="grafico-header">
        <h3 class="grafico-titulo">
            <i class="fas fa-chart-line"></i>
            Ventas de los Últimos 7 Días
        </h3>
        <a href="ventas.php" class="grafico-link">
            Ver detalle <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="grafico-wrapper">
        <canvas id="ventasChart" style="height: 300px;"></canvas>
    </div>
</div>

<!-- Dos columnas -->
<div class="reportes-columnas">
    <!-- Productos más vendidos -->
    <div class="columna-card">
        <div class="card-header">
            <h3 class="card-titulo">
                <i class="fas fa-crown" style="color: #f59e0b;"></i>
                Productos Más Vendidos (30 días)
            </h3>
            <a href="productos_mas_vendidos.php" class="card-link">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-content">
            <?php if ($productos_top->num_rows > 0): ?>
                <div class="productos-lista">
                    <?php while($prod = $productos_top->fetch_assoc()): ?>
                    <div class="producto-item">
                        <div class="producto-imagen">
                            <?php if (!empty($prod['imagen_url'])): ?>
                                <!-- CORREGIDO: Usamos BASE_URL directamente para evitar el doble 'assets' -->
                                <img src="<?php echo BASE_URL . '/' . $prod['imagen_url']; ?>" 
                                     alt="<?php echo h($prod['nombre']); ?>"
                                     onerror="this.onerror=null; this.src='<?php echo BASE_URL . '/assets/images/no-image.png'; ?>'">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL . '/assets/images/no-image.png'; ?>" 
                                     alt="Sin imagen">
                            <?php endif; ?>
                        </div>
                        <div class="producto-info">
                            <p class="producto-nombre"><?php echo h($prod['nombre']); ?></p>
                            <div class="producto-stats">
                                <span><i class="fas fa-shopping-cart"></i> <?php echo $prod['total_vendido']; ?> vendidos</span>
                                <span><i class="fas fa-dollar-sign"></i> $<?php echo number_format($prod['total_ingresos'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <p>No hay ventas en los últimos 30 días</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Últimos cortes de caja -->
    <div class="columna-card">
        <div class="card-header">
            <h3 class="card-titulo">
                <i class="fas fa-cash-register" style="color: #10b981;"></i>
                Últimos Cortes de Caja
            </h3>
            <a href="historial_cortes.php" class="card-link">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-content">
            <?php if ($ultimos_cortes->num_rows > 0): ?>
                <div class="cortes-lista">
                    <?php while($corte = $ultimos_cortes->fetch_assoc()): ?>
                    <div class="corte-item">
                        <div class="corte-info">
                            <p class="corte-fecha"><?php echo date('d/m/Y H:i', strtotime($corte['fecha_cierre'])); ?></p>
                            <p class="corte-usuario"><?php echo h($corte['username']); ?></p>
                        </div>
                        <div class="corte-montos">
                            <p class="corte-total">$<?php echo number_format($corte['monto_final'] ?? 0, 2); ?></p>
                            <p class="corte-ventas"><?php echo $corte['ventas_totales'] ?? 0; ?> ventas</p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <p>No hay cortes registrados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<div class="accesos-rapidos">
    <h3 class="accesos-titulo">Reportes Detallados</h3>
    <div class="accesos-grid">
        <a href="ventas.php?periodo=hoy" class="acceso-card">
            <div class="acceso-icono blue">
                <i class="fas fa-calendar-day"></i>
            </div>
            <span class="acceso-texto">Hoy</span>
        </a>
        
        <a href="ventas.php?periodo=semana" class="acceso-card">
            <div class="acceso-icono green">
                <i class="fas fa-calendar-week"></i>
            </div>
            <span class="acceso-texto">Semana</span>
        </a>
        
        <a href="ventas.php?periodo=mes" class="acceso-card">
            <div class="acceso-icono yellow">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <span class="acceso-texto">Mes</span>
        </a>
        
        <a href="productos_mas_vendidos.php" class="acceso-card">
            <div class="acceso-icono purple">
                <i class="fas fa-chart-bar"></i>
            </div>
            <span class="acceso-texto">Top</span>
        </a>
        
        <a href="../inventario/stock_bajo.php" class="acceso-card">
            <div class="acceso-icono red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <span class="acceso-texto">Stock</span>
        </a>
        
        <a href="exportar.php" class="acceso-card">
            <div class="acceso-icono gray">
                <i class="fas fa-file-export"></i>
            </div>
            <span class="acceso-texto">Exportar</span>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechas = <?php echo json_encode($fechas); ?>;
    const ventas = <?php echo json_encode($totales); ?>;
    
    const ctx = document.getElementById('ventasChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: fechas,
            datasets: [{
                label: 'Ventas ($)',
                data: ventas,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: 'white',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#f3f4f6',
                    bodyColor: '#e5e7eb',
                    callbacks: {
                        label: function(context) {
                            return ' $' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#e5e7eb', drawBorder: false },
                    ticks: { callback: function(value) { return '$' + value; } }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php include '../footer.php'; ?>