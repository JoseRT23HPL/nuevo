<?php
// file: /dashboard/reportes/index.php - VERSIÓN CSS PURO (SIN BACKEND)
include '../header.php';

// Datos de ejemplo para mostrar
$hoy = date('Y-m-d');
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$inicio_mes = date('Y-m-01');

// Ventas de hoy (ejemplo)
$ventas_hoy = [
    'total_ventas' => 12,
    'total_ingresos' => 3560.50,
    'efectivo' => 2140.00,
    'tarjeta' => 950.50,
    'transferencia' => 470.00
];

// Ventas de la semana (ejemplo)
$ventas_semana = [
    'total_ventas' => 58,
    'total_ingresos' => 15780.25
];

// Ventas del mes (ejemplo)
$ventas_mes = [
    'total_ventas' => 187,
    'total_ingresos' => 52340.80
];

// Productos más vendidos (ejemplo)
$productos_top = [
    ['nombre' => 'Martillo de Uña 16oz', 'imagen' => '', 'total_vendido' => 45, 'total_ingresos' => 1125.00],
    ['nombre' => 'Taladro Percutor 500W', 'imagen' => '', 'total_vendido' => 23, 'total_ingresos' => 8970.00],
    ['nombre' => 'Caja de Tornillos 1/2" x 100pz', 'imagen' => '', 'total_vendido' => 120, 'total_ingresos' => 1800.00],
    ['nombre' => 'Cemento Portland Gris 50kg', 'imagen' => '', 'total_vendido' => 85, 'total_ingresos' => 17000.00],
    ['nombre' => 'Pintura Blanca 20L', 'imagen' => '', 'total_vendido' => 32, 'total_ingresos' => 4480.00]
];

// Datos para el gráfico (últimos 7 días)
$fechas = [];
$totales = [];

for ($i = 6; $i >= 0; $i--) {
    $fecha = date('d/m', strtotime("-$i days"));
    $fechas[] = $fecha;
    $totales[] = rand(1500, 5000); // Valores aleatorios para ejemplo
}

// Últimos cortes de caja (ejemplo)
$ultimos_cortes = [
    ['fecha_cierre' => '2025-03-14 19:30:00', 'username' => 'admin', 'monto_final' => 8750.50, 'ventas_totales' => 24],
    ['fecha_cierre' => '2025-03-13 19:15:00', 'username' => 'jperez', 'monto_final' => 6230.00, 'ventas_totales' => 18],
    ['fecha_cierre' => '2025-03-12 19:45:00', 'username' => 'mgarcia', 'monto_final' => 11250.75, 'ventas_totales' => 31],
    ['fecha_cierre' => '2025-03-11 19:20:00', 'username' => 'admin', 'monto_final' => 4980.25, 'ventas_totales' => 14],
    ['fecha_cierre' => '2025-03-10 19:00:00', 'username' => 'jperez', 'monto_final' => 7340.00, 'ventas_totales' => 22]
];

// Verificar si hay corte abierto (ejemplo)
$corte_abierto = true; // Cambiar a false para probar el otro estado
?>

<!-- HEADER UNIFICADO - CORREGIDO -->
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
            <a href="corte_caja.php?action=cerrar" class="btn-header warning">
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

<!-- Subtítulo -->
<p class="page-subtitle">Visualiza el rendimiento de tu negocio</p>

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
                <span class="reporte-valor">$<?php echo number_format($ventas_hoy['total_ingresos'], 2); ?></span>
                <span class="reporte-sub"><?php echo $ventas_hoy['total_ventas']; ?> ventas</span>
            </div>
        </div>
        <div class="reporte-detalles">
            <div class="detalle-item">
                <span class="detalle-label">Efectivo</span>
                <span class="detalle-valor">$<?php echo number_format($ventas_hoy['efectivo'], 0); ?></span>
            </div>
            <div class="detalle-item">
                <span class="detalle-label">Tarjeta</span>
                <span class="detalle-valor">$<?php echo number_format($ventas_hoy['tarjeta'], 0); ?></span>
            </div>
            <div class="detalle-item">
                <span class="detalle-label">Transfer</span>
                <span class="detalle-valor">$<?php echo number_format($ventas_hoy['transferencia'], 0); ?></span>
            </div>
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
                <span class="reporte-valor">$<?php echo number_format($ventas_semana['total_ingresos'], 2); ?></span>
                <span class="reporte-sub"><?php echo $ventas_semana['total_ventas']; ?> ventas</span>
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
                <span class="reporte-valor">$<?php echo number_format($ventas_mes['total_ingresos'], 2); ?></span>
                <span class="reporte-sub"><?php echo $ventas_mes['total_ventas']; ?> ventas</span>
            </div>
        </div>
        <div class="reporte-periodo">
            <span class="periodo-texto"><?php echo date('F Y'); ?></span>
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
                Productos Más Vendidos
            </h3>
            <a href="productos_mas_vendidos.php" class="card-link">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="card-content">
            <?php if (count($productos_top) > 0): ?>
                <div class="productos-lista">
                    <?php foreach ($productos_top as $prod): ?>
                    <div class="producto-item">
                        <div class="producto-imagen">
                            <img src="<?php echo $prod['imagen'] ?: '/nuevo/assets/images/no-image.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                        </div>
                        <div class="producto-info">
                            <p class="producto-nombre"><?php echo htmlspecialchars($prod['nombre']); ?></p>
                            <div class="producto-stats">
                                <span><i class="fas fa-shopping-cart"></i> <?php echo $prod['total_vendido']; ?> vendidos</span>
                                <span><i class="fas fa-dollar-sign"></i> $<?php echo number_format($prod['total_ingresos'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <p>No hay ventas en este período</p>
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
            <?php if (count($ultimos_cortes) > 0): ?>
                <div class="cortes-lista">
                    <?php foreach ($ultimos_cortes as $corte): ?>
                    <div class="corte-item">
                        <div class="corte-info">
                            <p class="corte-fecha"><?php echo date('d/m/Y H:i', strtotime($corte['fecha_cierre'])); ?></p>
                            <p class="corte-usuario"><?php echo $corte['username']; ?></p>
                        </div>
                        <div class="corte-montos">
                            <p class="corte-total">$<?php echo number_format($corte['monto_final'], 2); ?></p>
                            <p class="corte-ventas"><?php echo $corte['ventas_totales']; ?> ventas</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
                legend: {
                    display: false
                },
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
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php include '../footer.php'; ?>