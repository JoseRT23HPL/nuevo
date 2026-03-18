<?php
// file: /dashboard/reportes/eliminar_corte.php
require_once '../../config.php';
requiereAuth();

// Solo super_admin puede eliminar cortes
if (!hasRole('super_admin')) {
    $_SESSION['error'] = "No tienes permisos para realizar esta acción";
    header('Location: historial_cortes.php');
    exit;
}

$conn = getDB();

// Obtener ID del corte
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID de corte no válido";
    header('Location: historial_cortes.php');
    exit;
}

// Verificar que el corte existe y obtener información
$stmt = $conn->prepare("
    SELECT c.*, u.username 
    FROM cortes_caja c
    JOIN usuarios u ON c.id_usuario = u.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$corte = $result->fetch_assoc();

if (!$corte) {
    $_SESSION['error'] = "El corte no existe";
    header('Location: historial_cortes.php');
    exit;
}

// Verificar si hay ventas asociadas a este corte
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM ventas 
    WHERE fecha_venta BETWEEN ? AND ?
");
$stmt->bind_param("ss", $corte['fecha_apertura'], $corte['fecha_cierre']);
$stmt->execute();
$ventas_asociadas = $stmt->get_result()->fetch_assoc()['total'];

// Si se confirmó la eliminación
if (isset($_GET['confirmar']) && $_GET['confirmar'] == 'si') {
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // 1. Primero, verificar si hay retiros asociados
        $stmt = $conn->prepare("DELETE FROM retiros_caja WHERE id_corte = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // 2. Eliminar el corte
        $stmt = $conn->prepare("DELETE FROM cortes_caja WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Confirmar transacción
        $conn->commit();
        
        $_SESSION['success'] = "Corte #" . str_pad($id, 4, '0', STR_PAD_LEFT) . " eliminado correctamente";
        
    } catch (Exception $e) {
        // Revertir cambios si algo sale mal
        $conn->rollback();
        $_SESSION['error'] = "Error al eliminar: " . $e->getMessage();
    }
    
    header('Location: historial_cortes.php');
    exit;
}

include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-trash-alt"></i>
            <h1>Eliminar Corte de Caja</h1>
        </div>
        <span class="pv-badge danger">PELIGRO</span>
    </div>
    
    <div class="pv-header-right">
        <a href="historial_cortes.php" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver al Historial
        </a>
    </div>
</div>

<!-- ALERTA DE ADVERTENCIA -->
<div class="alerta warning">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        <h4>¿Eliminar corte de caja?</h4>
        <p>Esta acción es irreversible y eliminará permanentemente este corte.</p>
    </div>
</div>

<!-- CONTENEDOR PRINCIPAL -->
<div class="eliminar-corte-container">
    <div class="corte-card">
        <div class="corte-card-header">
            <i class="fas fa-cash-register"></i>
            <h3>Detalles del corte a eliminar</h3>
        </div>
        
        <div class="corte-card-body">
            <div class="detalles-grid">
                <!-- ID y Usuario -->
                <div class="detalle-item">
                    <span class="detalle-label">ID del corte:</span>
                    <span class="detalle-valor id">#<?php echo str_pad($corte['id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
                
                <div class="detalle-item">
                    <span class="detalle-label">Usuario:</span>
                    <span class="detalle-valor"><?php echo h($corte['username']); ?></span>
                </div>
                
                <!-- Fecha apertura -->
                <div class="detalle-item">
                    <span class="detalle-label">Fecha apertura:</span>
                    <span class="detalle-valor">
                        <?php echo date('d/m/Y', strtotime($corte['fecha_apertura'])); ?> 
                        <small><?php echo date('H:i', strtotime($corte['fecha_apertura'])); ?> hrs</small>
                    </span>
                </div>
                
                <!-- Fecha cierre -->
                <div class="detalle-item">
                    <span class="detalle-label">Fecha cierre:</span>
                    <span class="detalle-valor">
                        <?php if ($corte['fecha_cierre']): ?>
                            <?php echo date('d/m/Y', strtotime($corte['fecha_cierre'])); ?> 
                            <small><?php echo date('H:i', strtotime($corte['fecha_cierre'])); ?> hrs</small>
                        <?php else: ?>
                            <span class="estado-badge inactivo">Corte abierto</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <!-- Monto inicial -->
                <div class="detalle-item">
                    <span class="detalle-label">Monto inicial:</span>
                    <span class="detalle-valor">$<?php echo number_format($corte['monto_inicial'], 2); ?></span>
                </div>
                
                <!-- Monto final -->
                <div class="detalle-item">
                    <span class="detalle-label">Monto final:</span>
                    <span class="detalle-valor final">$<?php echo number_format($corte['monto_final'] ?? 0, 2); ?></span>
                </div>
                
                <?php if ($ventas_asociadas > 0): ?>
                <!-- Ventas asociadas (ADVERTENCIA) -->
                <div class="detalle-item full-width">
                    <div class="ventas-warning">
                        <i class="fas fa-shopping-cart"></i>
                        <div>
                            <strong>¡Atención!</strong>
                            <p>Este corte tiene <strong><?php echo $ventas_asociadas; ?> venta(s) asociada(s)</strong>. Las ventas NO serán eliminadas, solo el registro del corte.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- ACCIONES -->
            <div class="acciones-eliminar">
                <a href="historial_cortes.php" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
                
                <a href="?id=<?php echo $id; ?>&confirmar=si" 
                   class="btn-danger"
                   onclick="return confirm('⚠️ ¿Estás ABSOLUTAMENTE SEGURO?\n\nEsta acción eliminará permanentemente este corte de caja y no se puede deshacer.')">
                    <i class="fas fa-trash"></i>
                    Sí, eliminar permanentemente
                </a>
            </div>
        </div>
    </div>
    
    <!-- INFO BOX DE SEGURIDAD -->
    <div class="info-box warning">
        <i class="fas fa-shield-alt"></i>
        <div class="info-box-content">
            <h4>¿Por qué no deberías eliminar este corte?</h4>
            <ul>
                <li>Los cortes de caja son registros importantes para la contabilidad</li>
                <li>Eliminar un corte puede afectar los reportes financieros</li>
                <li>Las ventas asociadas quedarán sin un corte de referencia</li>
                <li>Esta acción no se puede deshacer</li>
            </ul>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>