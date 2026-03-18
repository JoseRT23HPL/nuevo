<?php
// file: /dashboard/reportes/corte_detalle.php - VERSIÓN CONECTADA A BD
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener ID del corte
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: historial_cortes.php');
    exit;
}

// ===== 1. OBTENER DATOS DEL CORTE =====
$stmt = $conn->prepare("
    SELECT 
        c.*,
        u.username,
        u.nombre as nombre_completo,
        (SELECT COUNT(*) FROM ventas WHERE fecha_venta BETWEEN c.fecha_apertura AND c.fecha_cierre) as total_ventas,
        (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE fecha_venta BETWEEN c.fecha_apertura AND c.fecha_cierre) as ingresos_periodo
    FROM cortes_caja c
    JOIN usuarios u ON c.id_usuario = u.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$corte = $result->fetch_assoc();

if (!$corte) {
    header('Location: historial_cortes.php');
    exit;
}

// ===== 2. OBTENER VENTAS DEL CORTE =====
$ventas_stmt = $conn->prepare("
    SELECT 
        v.id,
        v.folio,
        DATE_FORMAT(v.fecha_venta, '%H:%i') as hora,
        v.total,
        v.metodo_pago as metodo,
        v.estado,
        c.nombre as cliente_nombre,
        c.telefono as cliente_telefono
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    WHERE v.fecha_venta BETWEEN ? AND ?
    ORDER BY v.fecha_venta ASC
");
$ventas_stmt->bind_param("ss", $corte['fecha_apertura'], $corte['fecha_cierre']);
$ventas_stmt->execute();
$ventas = $ventas_stmt->get_result();

// ===== 3. CALCULAR DIFERENCIA =====
// Usamos los campos reales de la base de datos
$esperado = ($corte['monto_inicial'] ?? 0) + ($corte['total_efectivo'] ?? 0);
$diferencia = ($corte['monto_final'] ?? 0) - $esperado;

include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-cash-register"></i>
            <h1>Detalle de Corte de Caja</h1>
        </div>
        <span class="pv-badge">#<?php echo str_pad($corte['id'], 4, '0', STR_PAD_LEFT); ?></span>
    </div>
    
    <div class="pv-header-right">
        <a href="historial_cortes.php" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver al historial
        </a>
        <?php if (hasRole('super_admin')): ?>
        <button onclick="imprimirCorte(<?php echo $corte['id']; ?>)" class="btn-header purple">
            <i class="fas fa-print"></i>
            Imprimir
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- INFO DEL CORTE - TARJETA SUPERIOR -->
<div class="corte-detalle-header">
    <div class="corte-detalle-info">
        <div class="info-item">
            <i class="fas fa-user-circle"></i>
            <span class="info-label">Usuario:</span>
            <span class="info-valor"><?php echo htmlspecialchars($corte['nombre_completo'] ?: $corte['username']); ?></span>
        </div>
        <div class="info-item">
            <i class="fas fa-calendar-alt"></i>
            <span class="info-label">Fecha apertura:</span>
            <span class="info-valor"><?php echo date('d/m/Y', strtotime($corte['fecha_apertura'])); ?></span>
            <span class="info-hora"><?php echo date('H:i', strtotime($corte['fecha_apertura'])); ?> hrs</span>
        </div>
        <div class="info-item">
            <i class="fas fa-calendar-check"></i>
            <span class="info-label">Fecha cierre:</span>
            <span class="info-valor"><?php echo date('d/m/Y', strtotime($corte['fecha_cierre'])); ?></span>
            <span class="info-hora"><?php echo date('H:i', strtotime($corte['fecha_cierre'])); ?> hrs</span>
        </div>
    </div>
</div>

<!-- GRID DE INFORMACIÓN PRINCIPAL -->
<div class="corte-detalle-grid">
    <!-- Columna Izquierda - Montos -->
    <div class="detalle-card">
        <div class="card-header">
            <i class="fas fa-dollar-sign"></i>
            <h3>Resumen de Montos</h3>
        </div>
        <div class="card-content">
            <div class="monto-row">
                <span class="monto-label">Monto inicial</span>
                <span class="monto-valor">$<?php echo number_format($corte['monto_inicial'] ?? 0, 2); ?></span>
            </div>
            
            <div class="monto-row">
                <span class="monto-label">Efectivo en ventas</span>
                <span class="monto-valor efectivo">$<?php echo number_format($corte['total_efectivo'] ?? 0, 2); ?></span>
            </div>
            
            <div class="monto-row total-parcial">
                <span class="monto-label">Esperado en caja</span>
                <span class="monto-valor esperado">$<?php echo number_format($esperado, 2); ?></span>
            </div>
            
            <div class="divisor"></div>
            
            <div class="monto-row">
                <span class="monto-label">Monto final contado</span>
                <span class="monto-valor final">$<?php echo number_format($corte['monto_final'] ?? 0, 2); ?></span>
            </div>
            
            <div class="diferencia-box <?php 
                echo $diferencia > 0 ? 'positiva' : ($diferencia < 0 ? 'negativa' : 'cero'); 
            ?>">
                <span class="diferencia-label">Diferencia:</span>
                <span class="diferencia-valor">
                    <?php if ($diferencia > 0): ?>
                        +$<?php echo number_format($diferencia, 2); ?> (Sobra)
                    <?php elseif ($diferencia < 0): ?>
                        -$<?php echo number_format(abs($diferencia), 2); ?> (Falta)
                    <?php else: ?>
                        $0.00
                    <?php endif; ?>
                </span>
            </div>
            
            <?php if ($corte['diferencia_justificada']): ?>
            <div class="diferencia-justificada">
                <i class="fas fa-check-circle"></i>
                Diferencia justificada
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Columna Derecha - Métodos de Pago -->
    <div class="detalle-card">
        <div class="card-header">
            <i class="fas fa-credit-card"></i>
            <h3>Métodos de Pago</h3>
        </div>
        <div class="card-content">
            <div class="metodo-row">
                <div class="metodo-icono efectivo">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="metodo-info">
                    <span class="metodo-label">Efectivo</span>
                    <span class="metodo-monto">$<?php echo number_format($corte['total_efectivo'] ?? 0, 2); ?></span>
                </div>
            </div>
            
            <div class="metodo-row">
                <div class="metodo-icono transferencia">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="metodo-info">
                    <span class="metodo-label">Transferencia</span>
                    <span class="metodo-monto">$<?php echo number_format($corte['total_transferencia'] ?? 0, 2); ?></span>
                </div>
            </div>
            
            <div class="total-ventas">
                <span class="total-label">Total ventas:</span>
                <span class="total-valor">$<?php echo number_format($corte['ingresos_periodo'] ?? 0, 2); ?></span>
            </div>
            
            <?php if ($corte['tipo_cierre']): ?>
            <div class="tipo-cierre">
                <span class="tipo-label">Tipo de cierre:</span>
                <span class="tipo-valor">
                    <?php 
                    echo $corte['tipo_cierre'] == 'final' ? 'Final' : 
                        ($corte['tipo_cierre'] == 'parcial' ? 'Parcial' : 'Conciliación'); 
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- OBSERVACIONES -->
<?php if (!empty($corte['observaciones']) || !empty($corte['motivo_diferencia'])): ?>
<div class="detalle-card observaciones-card">
    <div class="card-header">
        <i class="fas fa-sticky-note"></i>
        <h3>Observaciones</h3>
    </div>
    <div class="card-content">
        <?php if (!empty($corte['observaciones'])): ?>
            <p class="observacion-texto"><?php echo nl2br(htmlspecialchars($corte['observaciones'])); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($corte['motivo_diferencia'])): ?>
            <div class="motivo-diferencia">
                <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                <p><strong>Motivo de diferencia:</strong> <?php echo htmlspecialchars($corte['motivo_diferencia']); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- VENTAS DEL CORTE -->
<div class="detalle-card ventas-card">
    <div class="card-header">
        <i class="fas fa-shopping-cart"></i>
        <h3>Ventas realizadas en este corte (<?php echo $ventas->num_rows; ?>)</h3>
    </div>
    <div class="card-content">
        <?php if ($ventas->num_rows > 0): ?>
            <div class="tabla-responsive">
                <table class="tabla-ventas-corte">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($v = $ventas->fetch_assoc()): ?>
                        <tr>
                            <td class="folio-cell"><?php echo $v['folio']; ?></td>
                            <td class="hora-cell"><?php echo $v['hora']; ?></td>
                            <td>
                                <?php if ($v['cliente_nombre']): ?>
                                    <span title="<?php echo htmlspecialchars($v['cliente_telefono'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($v['cliente_nombre']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="sin-cliente">Cliente general</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $metodo_class = '';
                                $metodo_text = '';
                                switch($v['metodo']) {
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
                                        $metodo_text = ucfirst($v['metodo']);
                                }
                                ?>
                                <span class="metodo-badge <?php echo $metodo_class; ?>">
                                    <?php echo $metodo_text; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $estado_class = '';
                                $estado_text = '';
                                switch($v['estado']) {
                                    case 'completada':
                                        $estado_class = 'completada';
                                        $estado_text = 'Completada';
                                        break;
                                    case 'cancelada':
                                        $estado_class = 'cancelada';
                                        $estado_text = 'Cancelada';
                                        break;
                                    default:
                                        $estado_class = 'pendiente';
                                        $estado_text = 'Pendiente';
                                }
                                ?>
                                <span class="estado-badge <?php echo $estado_class; ?>">
                                    <?php echo $estado_text; ?>
                                </span>
                            </td>
                            <td class="text-right total-cell">$<?php echo number_format($v['total'], 2); ?></td>
                            <td class="text-center">
                                <a href="../ventas/ver.php?id=<?php echo $v['id']; ?>" class="accion-icon" title="Ver venta">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart" style="font-size: 2rem; color: var(--gray-400);"></i>
                <p>No hay ventas registradas en este corte</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function imprimirCorte(id) {
    window.open('imprimir_corte.php?id=' + id, '_blank', 'width=800,height=600');
}
</script>

<?php include '../footer.php'; ?>