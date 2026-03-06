<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

$action = $_GET['action'] ?? '';
$error = '';
$success = '';

// Verificar si hay corte abierto para el usuario actual
$corte_abierto = null;
$stmt = $conn->prepare("
    SELECT * FROM cortes_caja 
    WHERE id_usuario = ? 
    AND fecha_cierre IS NULL
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $corte_abierto = $result->fetch_assoc();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'abrir') {
        $monto_inicial = floatval($_POST['monto_inicial'] ?? 0);
        $tipo_corte = $_POST['tipo_corte'] ?? 'turno'; // 'turno' o 'dia'
        
        if ($monto_inicial < 0) {
            $error = 'El monto inicial no puede ser negativo';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO cortes_caja (id_usuario, monto_inicial, tipo) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("ids", $_SESSION['user_id'], $monto_inicial, $tipo_corte);
            
            if ($stmt->execute()) {
                $success = 'Corte de caja abierto correctamente';
                // Recargar corte abierto
                $stmt = $conn->prepare("
                    SELECT * FROM cortes_caja 
                    WHERE id_usuario = ? 
                    AND fecha_cierre IS NULL
                ");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $corte_abierto = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Error al abrir corte';
            }
        }
    } elseif ($action === 'cerrar' && $corte_abierto) {
        $tipo_cierre = $_POST['tipo_cierre'] ?? 'final'; // 'parcial', 'final', 'conciliacion'
        
        // Obtener ventas del turno actual
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_ventas,
                COALESCE(SUM(total), 0) as total_ingresos,
                COALESCE(SUM(CASE WHEN metodo_pago = 'efectivo' THEN total ELSE 0 END), 0) as efectivo,
                COALESCE(SUM(CASE WHEN metodo_pago = 'tarjeta' THEN total ELSE 0 END), 0) as tarjeta,
                COALESCE(SUM(CASE WHEN metodo_pago = 'transferencia' THEN total ELSE 0 END), 0) as transferencia
            FROM ventas 
            WHERE fecha_venta >= ?
        ");
        $stmt->bind_param("s", $corte_abierto['fecha_apertura']);
        $stmt->execute();
        $ventas_turno = $stmt->get_result()->fetch_assoc();
        
        $monto_final = floatval($_POST['monto_final'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');
        
        // Calcular diferencias
        $esperado = $corte_abierto['monto_inicial'] + $ventas_turno['efectivo'];
        $diferencia = $monto_final - $esperado;
        $diferencia_justificada = isset($_POST['diferencia_justificada']) ? 1 : 0;
        $motivo_diferencia = trim($_POST['motivo_diferencia'] ?? '');
    
        $stmt = $conn->prepare("
            UPDATE cortes_caja SET 
                fecha_cierre = NOW(),
                tipo_cierre = ?,
                monto_final = ?,
                ventas_totales = ?,
                total_efectivo = ?,
                total_transferencia = ?,
                diferencia = ?,
                diferencia_justificada = ?,
                motivo_diferencia = ?,
                observaciones = ?
            WHERE id = ?
        ");
        
        if ($stmt) {
            // Cambia de 11 parámetros a 10 parámetros
            $stmt->bind_param(
                "sdddddissi",  // 10 caracteres: s, d, d, d, d, d, i, s, s, i
                $tipo_cierre,
                $monto_final,
                $ventas_turno['total_ingresos'],
                $ventas_turno['efectivo'],
                $ventas_turno['transferencia'],  // Solo transferencia, sin tarjeta
                $diferencia,
                $diferencia_justificada,
                $motivo_diferencia,
                $observaciones,
                $corte_abierto['id']
            );
            
            if ($stmt->execute()) {
                $success = 'Corte de caja cerrado correctamente';
                if (abs($diferencia) > 0.01) {
                    $success .= " | Diferencia: $" . number_format($diferencia, 2);
                    if ($diferencia_justificada) {
                        $success .= " (JUSTIFICADA)";
                    }
                }
                $corte_abierto = null;
            } else {
                $error = 'Error al cerrar corte: ' . $stmt->error;
            }
        }
    } elseif ($action === 'retirar' && $corte_abierto) {
        // Retiro parcial de efectivo (corte parcial)
        $monto_retiro = floatval($_POST['monto_retiro'] ?? 0);
        $motivo_retiro = trim($_POST['motivo_retiro'] ?? '');
        
        if ($monto_retiro <= 0) {
            $error = 'El monto a retirar debe ser mayor a cero';
        } elseif ($monto_retiro > $corte_abierto['monto_inicial'] + $ventas_turno['efectivo']) {
            $error = 'No hay suficiente efectivo en caja';
        } else {
            // Registrar retiro parcial
            $stmt = $conn->prepare("
                INSERT INTO retiros_caja (id_corte, monto, motivo, id_usuario) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("idsi", $corte_abierto['id'], $monto_retiro, $motivo_retiro, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Retiro registrado correctamente';
            } else {
                $error = 'Error al registrar retiro';
            }
        }
    }
}

include '../header.php';
?>

<!-- Header de la página -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-cash-register" style="color: var(--primary);"></i>
            <h1>Corte de Caja</h1>
        </div>
        <span class="pv-badge">CAJA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/reportes/index.php'); ?>" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver a Reportes
        </a>
    </div>
</div>

<!-- Mensajes de alerta -->
<?php if ($error): ?>
    <div class="alerta error">
        <i class="fas fa-exclamation-circle"></i>
        <p><?php echo h($error); ?></p>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alerta success">
        <i class="fas fa-check-circle"></i>
        <p><?php echo $success; ?></p>
    </div>
<?php endif; ?>

<?php if ($corte_abierto): ?>
    <!-- CORTE ABIERTO - Vista detallada -->
    <div class="corte-container">
        <!-- Header del corte -->
        <div class="corte-header">
            <div class="corte-header-left">
                <i class="fas fa-cash-register"></i>
                <h2>Corte Abierto</h2>
            </div>
            <span class="corte-badge activo">ACTIVO</span>
        </div>
        
        <div class="corte-content">
            <!-- Información básica -->
            <div class="info-basica-grid">
                <div class="info-item">
                    <span class="info-label">Fecha de apertura</span>
                    <span class="info-valor"><?php echo date('d/m/Y', strtotime($corte_abierto['fecha_apertura'])); ?></span>
                    <span class="info-sub"><?php echo date('H:i:s', strtotime($corte_abierto['fecha_apertura'])); ?> hrs</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Monto inicial</span>
                    <span class="info-valor monto">$<?php echo number_format($corte_abierto['monto_inicial'], 2); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tipo de corte</span>
                    <span class="badge-tipo-corte"><?php echo $corte_abierto['tipo'] == 'dia' ? 'Fin de día' : 'Turno'; ?></span>
                </div>
            </div>
            
            <?php
            // Obtener ventas del turno actual
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_ventas,
                    COALESCE(SUM(total), 0) as total_ingresos,
                    COALESCE(SUM(CASE WHEN metodo_pago = 'efectivo' THEN total ELSE 0 END), 0) as efectivo,
                    COALESCE(SUM(CASE WHEN metodo_pago = 'transferencia' THEN total ELSE 0 END), 0) as transferencia
                FROM ventas 
                WHERE fecha_venta >= ?
            ");
            $stmt->bind_param("s", $corte_abierto['fecha_apertura']);
            $stmt->execute();
            $ventas_turno = $stmt->get_result()->fetch_assoc();
            
            // Obtener retiros parciales
            $retiros = [];
            $stmt = $conn->prepare("SELECT * FROM retiros_caja WHERE id_corte = ? ORDER BY fecha_retiro DESC");
            $stmt->bind_param("i", $corte_abierto['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $retiros[] = $row;
            }
            
            $total_retiros = array_sum(array_column($retiros, 'monto'));
            $total_esperado = $corte_abierto['monto_inicial'] + $ventas_turno['efectivo'] - $total_retiros;
            ?>
            
            <!-- Ventas del turno -->
            <h3 class="seccion-titulo">Ventas del Turno</h3>
            
            <div class="ventas-resumen-grid">
                <div class="resumen-card blue">
                    <span class="resumen-label">Total ventas</span>
                    <span class="resumen-valor"><?php echo $ventas_turno['total_ventas']; ?></span>
                </div>
                <div class="resumen-card green">
                    <span class="resumen-label">Ingresos totales</span>
                    <span class="resumen-valor">$<?php echo number_format($ventas_turno['total_ingresos'], 2); ?></span>
                </div>
            </div>
            
            <!-- Desglose por método de pago -->
            <div class="metodos-pago-grid">
                <div class="metodo-item">
                    <div class="metodo-icon efectivo">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="metodo-info">
                        <span class="metodo-label">Efectivo</span>
                        <span class="metodo-monto">$<?php echo number_format($ventas_turno['efectivo'], 2); ?></span>
                    </div>
                </div>
                
                <div class="metodo-item">
                    <div class="metodo-icon transferencia">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="metodo-info">
                        <span class="metodo-label">Transferencia</span>
                        <span class="metodo-monto">$<?php echo number_format($ventas_turno['transferencia'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Retiros parciales (si existen) -->
            <?php if (!empty($retiros)): ?>
            <div class="retiros-section">
                <h4>Retiros Parciales</h4>
                <?php foreach ($retiros as $retiro): ?>
                <div class="retiro-item">
                    <div>
                        <span class="retiro-monto">-$<?php echo number_format($retiro['monto'], 2); ?></span>
                        <span class="retiro-motivo"><?php echo h($retiro['motivo']); ?></span>
                    </div>
                    <span class="retiro-fecha"><?php echo date('H:i', strtotime($retiro['fecha_retiro'])); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Total esperado -->
            <div class="total-esperado">
                <span class="total-label">Total esperado en caja:</span>
                <span class="total-monto">$<?php echo number_format($total_esperado, 2); ?></span>
            </div>
            
            <!-- Pestañas para diferentes acciones -->
            <div class="corte-tabs">
                <button class="tab-btn active" onclick="showTab('cierre')">Cierre</button>
                <button class="tab-btn" onclick="showTab('retiro')">Retiro Parcial</button>
                <button class="tab-btn" onclick="showTab('conciliacion')">Conciliación</button>
            </div>
            
            <!-- Formulario Cierre -->
            <div id="tab-cierre" class="tab-pane active">
                <form method="POST" action="?action=cerrar" class="corte-form">
                    <input type="hidden" name="tipo_cierre" value="final">
                    
                    <div class="form-group">
                        <label class="form-label">
                            Tipo de cierre <span class="required">*</span>
                        </label>
                        <select name="tipo_cierre" class="form-select" required>
                            <option value="parcial">🔄 Corte Parcial (Cambio de turno)</option>
                            <option value="final">✅ Corte Final (Fin de día)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Monto final en efectivo <span class="required">*</span>
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="number" name="monto_final" step="0.01" min="0" 
                                   value="<?php echo $total_esperado; ?>" required
                                   class="form-input" oninput="calcularDiferencia(this.value)">
                        </div>
                    </div>
                    
                    <div id="diferencia-container" style="display: none;" class="diferencia-box">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="diferencia_justificada" class="checkbox-input">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">Justificar diferencia</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Motivo de la diferencia</label>
                            <textarea name="motivo_diferencia" class="form-textarea" rows="2" 
                                      placeholder="Ej: Error en conteo, cliente pagó con cambio exacto..."></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-textarea" rows="3" 
                                  placeholder="Notas adicionales sobre el corte..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i>
                            Cerrar Corte
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Formulario Retiro Parcial -->
            <div id="tab-retiro" class="tab-pane">
                <form method="POST" action="?action=retirar" class="corte-form">
                    <div class="form-group">
                        <label class="form-label">
                            Monto a retirar <span class="required">*</span>
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="number" name="monto_retiro" step="0.01" min="1" required
                                   class="form-input" placeholder="Ej: 500.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Motivo del retiro <span class="required">*</span>
                        </label>
                        <select name="motivo_retiro" class="form-select" required>
                            <option value="">Seleccionar motivo</option>
                            <option value="Cambio de turno">🔄 Cambio de turno</option>
                            <option value="Pago a proveedor">📦 Pago a proveedor</option>
                            <option value="Gastos menores">💵 Gastos menores</option>
                            <option value="Otro">❓ Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion_retiro" class="form-textarea" rows="2" 
                                  placeholder="Detalle del retiro..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-warning">
                            <i class="fas fa-money-bill-wave"></i>
                            Registrar Retiro
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Formulario Conciliación -->
            <div id="tab-conciliacion" class="tab-pane">
                <form method="POST" action="?action=cerrar" class="corte-form">
                    <input type="hidden" name="tipo_cierre" value="conciliacion">
                    
                    <div class="info-box warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <h4>Conciliación de Diferencias</h4>
                            <p>Registra diferencias entre el efectivo contado y lo esperado</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Efectivo contado <span class="required">*</span>
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="number" name="monto_final" step="0.01" min="0" required
                                   class="form-input" oninput="calcularDiferenciaConciliacion(this.value)">
                        </div>
                    </div>
                    
                    <div class="diferencia-preview" id="diferencia-preview">
                        <span class="diferencia-label">Diferencia:</span>
                        <span class="diferencia-valor">$0.00</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Tipo de diferencia
                        </label>
                        <select name="tipo_diferencia" class="form-select">
                            <option value="sobrante">💰 Sobrante</option>
                            <option value="faltante">💔 Faltante</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Motivo de la diferencia <span class="required">*</span>
                        </label>
                        <textarea name="motivo_diferencia" class="form-textarea" rows="3" required
                                  placeholder="Explica por qué hay esta diferencia..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i>
                            Registrar Conciliación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<?php else: ?>
    <!-- NO HAY CORTE ABIERTO - Opción para abrir -->
    <div class="no-corte-container">
        <div class="corte-card">
            <div class="corte-card-header">
                <i class="fas fa-play"></i>
                <h2>Abrir Nuevo Corte de Caja</h2>
            </div>
            
            <div class="corte-card-body">
                <form method="POST" action="?action=abrir" class="corte-form">
                    <div class="form-group">
                        <label class="form-label">
                            Tipo de corte <span class="required">*</span>
                        </label>
                        <select name="tipo_corte" class="form-select" required>
                            <option value="turno">🔄 Corte por Turno</option>
                            <option value="dia">📅 Corte de Fin de Día</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Monto inicial <span class="required">*</span>
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="number" name="monto_inicial" step="0.01" min="0" value="0" required
                                   class="form-input">
                        </div>
                        <small class="form-hint">Dinero con el que se inicia el turno</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-play"></i>
                            Abrir Corte
                        </button>
                        <a href="<?php echo url('dashboard/reportes/index.php'); ?>" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Últimos cortes realizados -->
        <div class="cortes-recientes">
            <h3>Últimos Cortes Realizados</h3>
            
            <?php
            $ultimos = $conn->query("
                SELECT c.*, u.username 
                FROM cortes_caja c
                JOIN usuarios u ON c.id_usuario = u.id
                WHERE c.fecha_cierre IS NOT NULL
                ORDER BY c.fecha_cierre DESC
                LIMIT 5
            ");
            ?>
            
            <?php if ($ultimos->num_rows > 0): ?>
                <div class="cortes-lista">
                    <?php while($c = $ultimos->fetch_assoc()): ?>
                    <div class="corte-item">
                        <div>
                            <span class="corte-fecha"><?php echo date('d/m/Y H:i', strtotime($c['fecha_cierre'])); ?></span>
                            <span class="corte-usuario"><?php echo h($c['username']); ?></span>
                        </div>
                        <div class="corte-montos">
                            <span class="corte-inicial">$<?php echo number_format($c['monto_inicial'], 0); ?></span>
                            <span class="corte-total">$<?php echo number_format($c['ventas_totales'], 2); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-cash-register"></i>
                    <p>No hay cortes registrados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
function calcularDiferencia(montoFinal) {
    const totalEsperado = <?php echo $total_esperado; ?>;
    const diferencia = parseFloat(montoFinal) - totalEsperado;
    const container = document.getElementById('diferencia-container');
    
    if (Math.abs(diferencia) > 0.01) {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function calcularDiferenciaConciliacion(montoFinal) {
    const totalEsperado = <?php echo $total_esperado; ?>;
    const diferencia = parseFloat(montoFinal) - totalEsperado;
    const preview = document.getElementById('diferencia-preview');
    
    const valorElement = preview.querySelector('.diferencia-valor');
    valorElement.textContent = '$' + diferencia.toFixed(2);
    
    if (diferencia > 0) {
        valorElement.style.color = 'var(--success)';
    } else if (diferencia < 0) {
        valorElement.style.color = 'var(--danger)';
    } else {
        valorElement.style.color = 'var(--gray-600)';
    }
}

function showTab(tabName) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-pane').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Desactivar todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar el tab seleccionado
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php include '../footer.php'; ?>