<?php
// dashboard/index.php - VERSIÓN CONECTADA A BD
require_once '../config.php';
requiereAuth();

$conn = getDB();

// ===== 1. TOTAL DE PRODUCTOS =====
$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$total_productos = $result->fetch_assoc()['total'];

// ===== 2. PRODUCTOS CON STOCK BAJO (stock_actual < stock_minimo) =====
$result = $conn->query("
    SELECT COUNT(*) as total 
    FROM productos 
    WHERE activo = 1 AND stock_actual < stock_minimo
");
$stock_bajo_count = $result->fetch_assoc()['total'];

// ===== 3. VENTAS DE HOY =====
$hoy = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_ventas,
        COALESCE(SUM(total), 0) as total_ingresos
    FROM ventas 
    WHERE DATE(fecha_venta) = ? AND estado = 'completada'
");
$stmt->bind_param("s", $hoy);
$stmt->execute();
$ventas_hoy = $stmt->get_result()->fetch_assoc();

// ===== 4. USUARIOS ACTIVOS =====
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
$usuarios_activos = $result->fetch_assoc()['total'];

// ===== 5. ÚLTIMAS 5 VENTAS =====
$ultimas_ventas = $conn->query("
    SELECT 
        v.id,
        v.folio,
        v.fecha_venta,
        v.total,
        u.username,
        v.estado
    FROM ventas v
    JOIN usuarios u ON v.id_usuario = u.id
    WHERE v.estado = 'completada'
    ORDER BY v.fecha_venta DESC 
    LIMIT 5
");

// ===== 6. PRODUCTOS CON STOCK BAJO (detalles) =====
$productos_bajo_stock = $conn->query("
    SELECT 
        id,
        nombre,
        sku,
        stock_actual,
        stock_minimo
    FROM productos 
    WHERE activo = 1 AND stock_actual < stock_minimo
    ORDER BY (stock_actual / stock_minimo) ASC
    LIMIT 5
");

// ===== 7. VENTAS POR DÍA PARA EL GRÁFICO (últimos 7 días) =====
$fechas = [];
$ventas_diarias = [];
$dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $dia_semana = $dias[date('w', strtotime($fecha))];
    $fechas[] = $dia_semana . ' ' . date('d/m', strtotime($fecha));
    
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total), 0) as total 
        FROM ventas 
        WHERE DATE(fecha_venta) = ? AND estado = 'completada'
    ");
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $ventas_diarias[] = $stmt->get_result()->fetch_assoc()['total'];
}

include 'header.php';
?>

<!-- ===== SECCIÓN DE BIENVENIDA ===== -->
<section class="welcome-section">
    <div class="welcome-container">
        <!-- Saludo personalizado con nombre real -->
        <div class="welcome-header">
            <span class="welcome-greeting">¡Hola de nuevo,</span>
            <h1 class="welcome-name">
                <?php echo h($_SESSION['username'] ?? 'Usuario'); ?>! 👋
            </h1>
        </div>
        
        <!-- Mensaje motivacional dinámico -->
        <p class="welcome-message">
            <?php
            $hora = date('H');
            if ($hora < 12) {
                echo "¡Buenos días! Comienza tu jornada con energía.";
            } elseif ($hora < 18) {
                echo "¡Buena tarde! Sigue avanzando con tus ventas.";
            } else {
                echo "¡Buena noche! Revisa el cierre del día.";
            }
            ?>
        </p>
        
        <!-- Indicador de scroll con animación -->
        <div class="scroll-indicator" id="scrollIndicator">
            <div class="scroll-text">Descubre tu dashboard</div>
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>
    
    <!-- Decoración de fondo -->
    <div class="welcome-decoration">
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>
    </div>
</section>

<!-- ===== CONTENIDO DEL DASHBOARD ===== -->
<section class="dashboard-content" id="dashboardContent">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">Aquí tienes el resumen de tu negocio hoy - <?php echo date('d/m/Y'); ?></p>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
        <!-- Total Productos -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
                <span class="stat-badge">Inventario</span>
            </div>
            <h3 class="stat-value"><?php echo number_format($total_productos); ?></h3>
            <p class="stat-label">Total Productos</p>
            <?php
            // Calcular porcentaje de crecimiento (esto requeriría datos históricos)
            // Por ahora mostramos un mensaje simple
            ?>
            <div class="stat-footer">
                <span class="stat-trend">✓</span>
                <span>Activos en inventario</span>
            </div>
        </div>
        
        <!-- Stock Bajo -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <span class="stat-badge">Alerta</span>
            </div>
            <h3 class="stat-value"><?php echo $stock_bajo_count; ?></h3>
            <p class="stat-label">Stock Bajo</p>
            <?php if ($stock_bajo_count > 0): ?>
            <a href="<?php echo url('dashboard/inventario/stock_bajo.php'); ?>" class="stat-link">
                Revisar stock <i class="fas fa-arrow-right"></i>
            </a>
            <?php else: ?>
            <span class="stat-link" style="color: var(--success);">Todo bien ✓</span>
            <?php endif; ?>
        </div>
        
        <!-- Ventas Hoy -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon green">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="stat-badge">Hoy</span>
            </div>
            <h3 class="stat-value"><?php echo $ventas_hoy['total_ventas'] ?? 0; ?></h3>
            <p class="stat-label">Ventas del día</p>
        </div>
        
        <!-- Ingresos Hoy -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon yellow">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <span class="stat-badge">Hoy</span>
            </div>
            <h3 class="stat-value">$<?php echo number_format($ventas_hoy['total_ingresos'] ?? 0, 2); ?></h3>
            <p class="stat-label">Ingresos del día</p>
        </div>
        
        <!-- Usuarios Activos -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <span class="stat-badge">Sistema</span>
            </div>
            <h3 class="stat-value"><?php echo $usuarios_activos; ?></h3>
            <p class="stat-label">Usuarios Activos</p>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="two-column-grid">
        <!-- Últimas Ventas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Últimas Ventas
                </h3>
                <a href="<?php echo url('dashboard/ventas/historial.php'); ?>" class="card-link">
                    Ver todas <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th class="amount">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($ultimas_ventas->num_rows > 0): ?>
                                <?php while($venta = $ultimas_ventas->fetch_assoc()): ?>
                                <tr>
                                    <td class="folio"><?php echo h($venta['folio']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></td>
                                    <td><?php echo h($venta['username']); ?></td>
                                    <td class="amount">$<?php echo number_format($venta['total'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay ventas recientes</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Productos con Stock Bajo -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-circle warning-icon"></i>
                    Stock Bajo
                </h3>
                <a href="<?php echo url('dashboard/inventario/stock_bajo.php'); ?>" class="card-link">
                    Ver todos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="stock-list">
                    <?php if ($productos_bajo_stock->num_rows > 0): ?>
                        <?php while($producto = $productos_bajo_stock->fetch_assoc()): 
                            $porcentaje = ($producto['stock_actual'] / $producto['stock_minimo']) * 100;
                            $estado = $producto['stock_actual'] == 0 ? 'danger' : 'warning';
                            $texto_estado = $producto['stock_actual'] == 0 ? 'AGOTADO' : 'BAJO';
                        ?>
                        <div class="stock-item">
                            <div class="stock-info">
                                <p class="stock-name"><?php echo h($producto['nombre']); ?></p>
                                <p class="stock-code">Código: <?php echo h($producto['sku']); ?></p>
                            </div>
                            <div class="stock-details">
                                <div class="stock-numbers">
                                    <span class="stock-current <?php echo $producto['stock_actual'] == 0 ? 'zero' : ''; ?>">
                                        <?php echo $producto['stock_actual']; ?>
                                    </span>
                                    <span class="stock-min">/ <?php echo $producto['stock_minimo']; ?></span>
                                </div>
                                <span class="stock-badge <?php echo $estado; ?>"><?php echo $texto_estado; ?></span>
                                <a href="<?php echo url('dashboard/inventario/entrada_rapida.php?producto=' . $producto['id']); ?>" 
                                   class="stock-action" title="Agregar stock">
                                    <i class="fas fa-cubes"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle" style="color: var(--success); font-size: 2rem;"></i>
                            <p>No hay productos con stock bajo</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ventas -->
    <div class="chart-container">
        <div class="chart-header">
            <h3>
                <i class="fas fa-chart-line"></i>
                Ventas de los últimos 7 días
            </h3>
        </div>
        <div class="chart-body">
            <canvas id="ventasChart" class="chart-canvas"></canvas>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="quick-actions">
        <a href="<?php echo url('dashboard/productos/nuevo.php'); ?>" class="action-card">
            <div class="action-icon blue">
                <i class="fas fa-box"></i>
            </div>
            <div class="action-info">
                <h4>Nuevo Producto</h4>
                <p>Agregar al inventario</p>
            </div>
        </a>
        
        <a href="<?php echo url('dashboard/ventas/index.php'); ?>" class="action-card">
            <div class="action-icon green">
                <i class="fas fa-cash-register"></i>
            </div>
            <div class="action-info">
                <h4>Nueva Venta</h4>
                <p>Ir al punto de venta</p>
            </div>
        </a>
        
        <a href="<?php echo url('dashboard/clientes/nuevo.php'); ?>" class="action-card">
            <div class="action-icon purple">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="action-info">
                <h4>Nuevo Cliente</h4>
                <p>Registrar cliente</p>
            </div>
        </a>
        
        <a href="<?php echo url('dashboard/reportes/corte_caja.php'); ?>" class="action-card">
            <div class="action-icon yellow">
                <i class="fas fa-cash-register"></i>
            </div>
            <div class="action-info">
                <h4>Corte de Caja</h4>
                <p>Cerrar turno</p>
            </div>
        </a>
    </div>
</section>

<!-- ===== SCRIPTS ===== -->
<!-- Primero main.js -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

<!-- Script del gráfico -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll para el indicador
    const scrollIndicator = document.getElementById('scrollIndicator');
    const dashboardContent = document.getElementById('dashboardContent');
    
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            dashboardContent.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        });
    }
    
    // Mostrar/ocultar indicador de scroll
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            scrollIndicator.style.opacity = '0';
            scrollIndicator.style.visibility = 'hidden';
        } else {
            scrollIndicator.style.opacity = '1';
            scrollIndicator.style.visibility = 'visible';
        }
    });

    // Gráfico de ventas con DATOS REALES
    const ctx = document.getElementById('ventasChart').getContext('2d');
    const fechas = <?php echo json_encode($fechas); ?>;
    const ventas = <?php echo json_encode($ventas_diarias); ?>;
    
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
                    callbacks: {
                        label: (context) => ' $' + context.parsed.y.toFixed(2)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => '$' + value }
                }
            }
        }
    });
});

function mostrarModalGlobal() {
    alert('Funcionalidad de cierre de sesión');
}
</script>

<?php include 'footer.php'; ?>