<?php
// file: /dashboard/reportes/historial_cortes.php - VERSIÓN CON TARJETAS
include '../header.php';

// Datos de ejemplo para mostrar
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$usuario = $_GET['usuario'] ?? '';

// Ejemplo de usuarios para filtro
$usuarios = [
    ['id' => 1, 'username' => 'admin', 'nombre_completo' => 'Administrador'],
    ['id' => 2, 'username' => 'jperez', 'nombre_completo' => 'Juan Pérez'],
    ['id' => 3, 'username' => 'mgarcia', 'nombre_completo' => 'María García']
];

// Ejemplo de cortes de caja
$cortes = [
    [
        'id' => 1,
        'fecha_apertura' => '2025-03-14 08:00:00',
        'fecha_cierre' => '2025-03-14 19:30:00',
        'username' => 'admin',
        'nombre_completo' => 'Administrador',
        'monto_inicial' => 1000.00,
        'monto_final' => 8750.50,
        'ventas_totales' => 8750.50,
        'total_efectivo' => 5120.00,
        'total_tarjeta' => 2450.50,
        'total_transferencia' => 1180.00
    ],
    [
        'id' => 2,
        'fecha_apertura' => '2025-03-13 08:15:00',
        'fecha_cierre' => '2025-03-13 19:45:00',
        'username' => 'jperez',
        'nombre_completo' => 'Juan Pérez',
        'monto_inicial' => 500.00,
        'monto_final' => 6230.00,
        'ventas_totales' => 6230.00,
        'total_efectivo' => 3890.00,
        'total_tarjeta' => 1560.00,
        'total_transferencia' => 780.00
    ],
    [
        'id' => 3,
        'fecha_apertura' => '2025-03-12 07:45:00',
        'fecha_cierre' => '2025-03-12 20:00:00',
        'username' => 'mgarcia',
        'nombre_completo' => 'María García',
        'monto_inicial' => 800.00,
        'monto_final' => 11250.75,
        'ventas_totales' => 11250.75,
        'total_efectivo' => 6780.25,
        'total_tarjeta' => 3120.50,
        'total_transferencia' => 1350.00
    ],
    [
        'id' => 4,
        'fecha_apertura' => '2025-03-11 08:30:00',
        'fecha_cierre' => '2025-03-11 19:15:00',
        'username' => 'admin',
        'nombre_completo' => 'Administrador',
        'monto_inicial' => 500.00,
        'monto_final' => 4980.25,
        'ventas_totales' => 4980.25,
        'total_efectivo' => 3010.50,
        'total_tarjeta' => 1450.75,
        'total_transferencia' => 519.00
    ],
    [
        'id' => 5,
        'fecha_apertura' => '2025-03-10 08:00:00',
        'fecha_cierre' => '2025-03-10 19:30:00',
        'username' => 'jperez',
        'nombre_completo' => 'Juan Pérez',
        'monto_inicial' => 700.00,
        'monto_final' => 7340.00,
        'ventas_totales' => 7340.00,
        'total_efectivo' => 4520.00,
        'total_tarjeta' => 2010.00,
        'total_transferencia' => 810.00
    ]
];

// Calcular totales
$totales = [
    'total_cortes' => count($cortes),
    'total_ingresos' => array_sum(array_column($cortes, 'ventas_totales')),
    'total_ventas' => count($cortes)
];
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-history"></i>
            <h1>Historial de Cortes de Caja</h1>
        </div>
        <span class="pv-badge">CAJA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="corte_caja.php" class="btn-header primary">
            <i class="fas fa-plus"></i>
            Nuevo Corte
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Consulta todos los cortes de caja realizados</p>

<!-- Filtros -->
<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <div class="filtros-grid-cortes">
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
                <select name="usuario" class="filtro-select">
                    <option value="">Todos los usuarios</option>
                    <?php foreach($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo $usuario == $u['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Botones -->
            <div class="filtro-botones">
                <button type="submit" class="btn-filtro btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
                
                <a href="historial_cortes.php" class="btn-filtro btn-secondary">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Estadísticas en cards -->
<div class="stats-grid-cortes">
    <!-- Total cortes -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-cash-register"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total de cortes</span>
            <span class="stat-valor"><?php echo $totales['total_cortes']; ?></span>
        </div>
    </div>
    
    <!-- Ingresos totales -->
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Ingresos totales</span>
            <span class="stat-valor">$<?php echo number_format($totales['total_ingresos'], 2); ?></span>
        </div>
    </div>
    
    <!-- Ventas totales -->
    <div class="stat-card">
        <div class="stat-icon yellow">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Ventas totales</span>
            <span class="stat-valor"><?php echo $totales['total_ventas']; ?></span>
        </div>
    </div>
</div>

<!-- Grid de cortes (TARJETAS EN LUGAR DE TABLA) -->
<div class="cortes-grid">
    <?php if (count($cortes) > 0): ?>
        <?php foreach($cortes as $c): 
            $esperado = $c['monto_inicial'] + ($c['total_efectivo'] ?? 0);
            $diferencia = $c['monto_final'] - $esperado;
        ?>
        <div class="corte-card">
            <!-- Header de la tarjeta -->
            <div class="corte-card-header">
                <div class="corte-fechas">
                    <span class="corte-fecha-label">
                        <i class="fas fa-play"></i>
                        <?php echo date('d/m/Y', strtotime($c['fecha_apertura'])); ?> 
                        <small><?php echo date('H:i', strtotime($c['fecha_apertura'])); ?></small>
                    </span>
                    <span class="corte-fecha-label">
                        <i class="fas fa-stop"></i>
                        <?php echo date('d/m/Y', strtotime($c['fecha_cierre'])); ?> 
                        <small><?php echo date('H:i', strtotime($c['fecha_cierre'])); ?></small>
                    </span>
                </div>
                <div class="corte-usuario">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($c['nombre_completo'] ?: $c['username']); ?>
                </div>
            </div>
            
            <!-- Cuerpo de la tarjeta -->
            <div class="corte-card-body">
                <div class="corte-monto-row">
                    <span class="corte-monto-label">Inicial</span>
                    <span class="corte-monto-valor">$<?php echo number_format($c['monto_inicial'], 2); ?></span>
                </div>
                <div class="corte-monto-row">
                    <span class="corte-monto-label">Final</span>
                    <span class="corte-monto-valor final">$<?php echo number_format($c['monto_final'], 2); ?></span>
                </div>
                <div class="corte-divisor"></div>
                
                <div class="corte-metodos">
                    <div class="corte-metodo">
                        <span class="metodo-icon efectivo">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <span class="metodo-monto">$<?php echo number_format($c['total_efectivo'] ?? 0, 2); ?></span>
                    </div>
                    <div class="corte-metodo">
                        <span class="metodo-icon tarjeta">
                            <i class="fas fa-credit-card"></i>
                        </span>
                        <span class="metodo-monto">$<?php echo number_format($c['total_tarjeta'] ?? 0, 2); ?></span>
                    </div>
                    <div class="corte-metodo">
                        <span class="metodo-icon transferencia">
                            <i class="fas fa-mobile-alt"></i>
                        </span>
                        <span class="metodo-monto">$<?php echo number_format($c['total_transferencia'] ?? 0, 2); ?></span>
                    </div>
                </div>
                
                <div class="corte-divisor"></div>
                
                <div class="corte-diferencia">
                    <span class="diferencia-label">Diferencia:</span>
                    <?php if (abs($diferencia) > 0.01): ?>
                        <?php if ($diferencia > 0): ?>
                            <span class="diferencia-valor positiva">
                                +$<?php echo number_format($diferencia, 2); ?> (Sobra)
                            </span>
                        <?php else: ?>
                            <span class="diferencia-valor negativa">
                                -$<?php echo number_format(abs($diferencia), 2); ?> (Falta)
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="diferencia-valor cero">$0.00</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Footer de la tarjeta -->
            <div class="corte-card-footer">
                <div class="corte-total-ventas">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Ventas: <strong>$<?php echo number_format($c['ventas_totales'] ?? 0, 2); ?></strong></span>
                </div>
                <a href="corte_detalle.php?id=<?php echo $c['id']; ?>" class="corte-ver-btn" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <h3>No hay cortes de caja registrados</h3>
            <p>Prueba con otros filtros o realiza tu primer corte</p>
            <a href="corte_caja.php" class="btn-primary">
                <i class="fas fa-plus"></i>
                Nuevo Corte
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>