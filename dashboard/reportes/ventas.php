<?php
// file: /dashboard/reportes/ventas.php - VERSIÓN CONECTADA A BD (SIN TARJETA)
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener usuario actual para permisos
$usuario_actual = getCurrentUser();

// Procesar filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;
$metodo_pago = $_GET['metodo_pago'] ?? '';

// ===== 1. OBTENER USUARIOS PARA EL FILTRO =====
$usuarios = $conn->query("
    SELECT id, username, nombre 
    FROM usuarios 
    WHERE activo = 1 
    ORDER BY nombre ASC
");

// ===== 2. CONSTRUIR CONSULTA DE RESUMEN =====
$sql_where = "WHERE v.estado = 'completada' AND DATE(v.fecha_venta) BETWEEN ? AND ?";
$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

if ($usuario_id > 0) {
    $sql_where .= " AND v.id_usuario = ?";
    $params[] = $usuario_id;
    $types .= "i";
}

if (!empty($metodo_pago)) {
    $sql_where .= " AND v.metodo_pago = ?";
    $params[] = $metodo_pago;
    $types .= "s";
}

// ===== 3. RESUMEN DE VENTAS =====
$sql = "
    SELECT 
        COUNT(*) as total_ventas,
        COALESCE(SUM(v.total), 0) as total_ingresos,
        COALESCE(SUM(CASE WHEN v.metodo_pago = 'efectivo' THEN v.total ELSE 0 END), 0) as total_efectivo,
        COALESCE(SUM(CASE WHEN v.metodo_pago = 'transferencia' THEN v.total ELSE 0 END), 0) as total_transferencia
    FROM ventas v
    $sql_where
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resumen = $stmt->get_result()->fetch_assoc();

$resumen['ticket_promedio'] = $resumen['total_ventas'] > 0 ? 
    $resumen['total_ingresos'] / $resumen['total_ventas'] : 0;

// ===== 4. VENTAS POR MÉTODO DE PAGO =====
$sql = "
    SELECT 
        v.metodo_pago,
        COUNT(*) as cantidad,
        COALESCE(SUM(v.total), 0) as total
    FROM ventas v
    $sql_where
    GROUP BY v.metodo_pago
    ORDER BY total DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$ventas_por_metodo = $stmt->get_result();

// ===== 5. VENTAS POR USUARIO =====
$sql = "
    SELECT 
        u.id,
        u.username,
        u.nombre as nombre_completo,
        COUNT(*) as total_ventas,
        COALESCE(SUM(v.total), 0) as total_ingresos
    FROM ventas v
    JOIN usuarios u ON v.id_usuario = u.id
    $sql_where
    GROUP BY u.id, u.username, u.nombre
    ORDER BY total_ingresos DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$ventas_por_usuario = $stmt->get_result();

// ===== 6. DATOS PARA EL GRÁFICO (ÚLTIMOS 7 DÍAS) =====
$fechas = [];
$totales = [];
$fecha_actual = strtotime($fecha_fin);

for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days", $fecha_actual));
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

// ===== 7. ÚLTIMAS VENTAS DEL PERÍODO =====
$sql = "
    SELECT 
        v.id,
        v.folio,
        v.fecha_venta,
        v.total,
        v.metodo_pago,
        u.username
    FROM ventas v
    JOIN usuarios u ON v.id_usuario = u.id
    $sql_where
    ORDER BY v.fecha_venta DESC
    LIMIT 20
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$ultimas_ventas = $stmt->get_result();

// Configuración de métodos de pago (SOLO EFECTIVO Y TRANSFERENCIA)
$metodos_config = [
    'efectivo' => ['icon' => 'money-bill-wave', 'color' => 'green', 'bg' => 'green', 'text' => 'green'],
    'transferencia' => ['icon' => 'mobile-alt', 'color' => 'purple', 'bg' => 'purple', 'text' => 'purple']
];

include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-chart-line"></i>
            <h1>Reporte de Ventas</h1>
        </div>
        <span class="pv-badge">REPORTES</span>
    </div>
    
    <div class="pv-header-right">
        <a href="exportar.php?tipo=ventas&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>" class="btn-header success">
            <i class="fas fa-file-excel"></i>
            Exportar
        </a>
        <a href="index.php" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- Subtítulo con período -->
<p class="page-subtitle">
    Analiza las ventas del período: 
    <strong><?php echo date('d/m/Y', strtotime($fecha_inicio)); ?></strong> 
    al <strong><?php echo date('d/m/Y', strtotime($fecha_fin)); ?></strong>
</p>

<!-- FILTROS -->
<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <div class="filtros-grid-ventas">
            <!-- Fecha inicio -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar"></i>
                    Fecha inicio
                </label>
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" class="filtro-input">
            </div>
            
            <!-- Fecha fin -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar-check"></i>
                    Fecha fin
                </label>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" class="filtro-input">
            </div>
            
            <!-- Usuario -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-user"></i>
                    Usuario
                </label>
                <select name="usuario_id" class="filtro-select">
                    <option value="">Todos los usuarios</option>
                    <?php while($u = $usuarios->fetch_assoc()): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo $usuario_id == $u['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['nombre'] ?: $u['username']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Método de pago -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-credit-card"></i>
                    Método de pago
                </label>
                <select name="metodo_pago" class="filtro-select">
                    <option value="">Todos</option>
                    <option value="efectivo" <?php echo $metodo_pago == 'efectivo' ? 'selected' : ''; ?>>💰 Efectivo</option>
                    <option value="transferencia" <?php echo $metodo_pago == 'transferencia' ? 'selected' : ''; ?>>📱 Transferencia</option>
                </select>
            </div>
            
            <!-- Botones -->
            <div class="filtro-botones">
                <button type="submit" class="btn-filtro btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
                
                <a href="ventas.php" class="btn-filtro btn-secondary">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- RESUMEN DE VENTAS -->
<div class="stats-grid-ventas">
    <!-- Total Ventas -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total Ventas</span>
            <span class="stat-valor"><?php echo number_format($resumen['total_ventas']); ?></span>
        </div>
    </div>
    
    <!-- Ingresos Totales -->
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Ingresos Totales</span>
            <span class="stat-valor">$<?php echo number_format($resumen['total_ingresos'], 2); ?></span>
        </div>
    </div>
    
    <!-- Ticket Promedio -->
    <div class="stat-card">
        <div class="stat-icon yellow">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Ticket Promedio</span>
            <span class="stat-valor">$<?php echo number_format($resumen['ticket_promedio'], 2); ?></span>
        </div>
    </div>
</div>

<!-- GRÁFICO DE VENTAS -->
<div class="grafico-container">
    <div class="grafico-header">
        <h3 class="grafico-titulo">
            <i class="fas fa-chart-bar"></i>
            Ventas por Día
        </h3>
    </div>
    <div class="grafico-wrapper">
        <canvas id="ventasChart" style="height: 300px;"></canvas>
    </div>
</div>

<!-- DOS COLUMNAS -->
<div class="reportes-columnas">
    <!-- Ventas por método de pago -->
    <div class="columna-card">
        <div class="card-header">
            <i class="fas fa-credit-card"></i>
            <h3>Ventas por Método de Pago</h3>
        </div>
        <div class="card-content">
            <?php if ($ventas_por_metodo->num_rows > 0): ?>
                <div class="metodos-ventas">
                    <?php while($mp = $ventas_por_metodo->fetch_assoc()): 
                        $metodo = $mp['metodo_pago'];
                        $config = $metodos_config[$metodo] ?? ['icon' => 'money-bill', 'bg' => 'gray', 'text' => 'gray'];
                        $porcentaje = $resumen['total_ingresos'] > 0 ? ($mp['total'] / $resumen['total_ingresos']) * 100 : 0;
                    ?>
                    <div class="metodo-venta">
                        <div class="metodo-header">
                            <div class="metodo-icono <?php echo $config['bg']; ?>">
                                <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                            </div>
                            <div class="metodo-nombre">
                                <span class="metodo-titulo"><?php echo ucfirst($metodo); ?></span>
                                <span class="metodo-cantidad">(<?php echo $mp['cantidad']; ?> ventas)</span>
                            </div>
                            <span class="metodo-total <?php echo $config['text']; ?>">$<?php echo number_format($mp['total'], 2); ?></span>
                        </div>
                        <div class="barra-porcentaje">
                            <div class="barra-progreso <?php echo $config['bg']; ?>" style="width: <?php echo $porcentaje; ?>%;"></div>
                        </div>
                        <p class="porcentaje-texto"><?php echo number_format($porcentaje, 1); ?>% del total</p>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-credit-card" style="font-size: 2rem; color: var(--gray-400);"></i>
                    <p>No hay datos de métodos de pago</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Ventas por usuario -->
    <div class="columna-card">
        <div class="card-header">
            <i class="fas fa-users"></i>
            <h3>Ventas por Usuario</h3>
        </div>
        <div class="card-content">
            <?php if ($ventas_por_usuario->num_rows > 0): ?>
                <div class="usuarios-ventas">
                    <?php while($vu = $ventas_por_usuario->fetch_assoc()): ?>
                    <div class="usuario-venta">
                        <div class="usuario-info">
                            <p class="usuario-nombre"><?php echo htmlspecialchars($vu['nombre_completo'] ?: $vu['username']); ?></p>
                            <p class="usuario-cantidad"><?php echo $vu['total_ventas']; ?> ventas</p>
                        </div>
                        <div class="usuario-total">
                            <span class="usuario-ingresos">$<?php echo number_format($vu['total_ingresos'], 2); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users" style="font-size: 2rem; color: var(--gray-400);"></i>
                    <p>No hay datos de usuarios</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Últimas ventas del período -->
<div class="tabla-container ventas-periodo">
    <div class="card-header">
        <i class="fas fa-history"></i>
        <h3>Últimas Ventas del Período</h3>
    </div>
    <div class="tabla-responsive">
        <table class="tabla-ventas-periodo">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Método</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ultimas_ventas->num_rows > 0): ?>
                    <?php while($v = $ultimas_ventas->fetch_assoc()): ?>
                    <tr>
                        <td class="folio-cell"><?php echo $v['folio']; ?></td>
                        <td class="fecha-cell">
                            <?php echo date('d/m/Y', strtotime($v['fecha_venta'])); ?>
                            <span class="fecha-hora"><?php echo date('H:i', strtotime($v['fecha_venta'])); ?></span>
                        </td>
                        <td class="usuario-cell"><?php echo htmlspecialchars($v['username']); ?></td>
                        <td>
                            <?php
                            $metodo_class = '';
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
                                    $metodo_class = 'otro';
                                    $metodo_text = ucfirst($v['metodo_pago']);
                            }
                            ?>
                            <span class="metodo-badge <?php echo $metodo_class; ?>">
                                <?php echo $metodo_text; ?>
                            </span>
                        </td>
                        <td class="text-right total-cell">$<?php echo number_format($v['total'], 2); ?></td>
                        <td class="text-center">
                            <a href="../ventas/ticket.php?id=<?php echo $v['id']; ?>" class="accion-icon" title="Ver ticket" target="_blank">
                                <i class="fas fa-receipt"></i>
                            </a>
                            <a href="../ventas/ver.php?id=<?php echo $v['id']; ?>" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-message">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <p>No hay ventas en este período</p>
                            <p class="empty-sub">Prueba con otros filtros</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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