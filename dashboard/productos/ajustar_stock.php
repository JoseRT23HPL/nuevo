<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener ID del producto
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . url('dashboard/productos/index.php'));
    exit;
}

// Obtener producto
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
    header('Location: ' . url('dashboard/productos/index.php'));
    exit;
}

// Obtener movimientos del producto
$movimientos = [];
$stmt = $conn->prepare("
    SELECT m.*, u.username 
    FROM movimientos_inventario m
    LEFT JOIN usuarios u ON m.id_usuario = u.id
    WHERE m.id_producto = ?
    ORDER BY m.fecha_movimiento DESC
    LIMIT 10
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $movimientos[] = $row;
}

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? '';
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');
    
    // Validaciones
    if ($cantidad <= 0) {
        $error = 'La cantidad debe ser mayor a cero';
    } elseif (empty($motivo)) {
        $error = 'Debes especificar un motivo';
    } else {
        $stock_anterior = $producto['stock_actual'];
        $stock_nuevo = $stock_anterior;
        
        switch ($tipo) {
            case 'entrada':
                $stock_nuevo = $stock_anterior + $cantidad;
                break;
            case 'salida':
                if ($cantidad > $stock_anterior) {
                    $error = 'No hay suficiente stock para realizar la salida';
                } else {
                    $stock_nuevo = $stock_anterior - $cantidad;
                }
                break;
            case 'ajuste':
                $stock_nuevo = $cantidad;
                $cantidad_ajuste = $stock_nuevo - $stock_anterior;
                break;
            default:
                $error = 'Tipo de movimiento no válido';
        }
        
        if (empty($error)) {
            $conn->begin_transaction();
            
            try {
                $update = $conn->prepare("UPDATE productos SET stock_actual = ? WHERE id = ?");
                $update->bind_param("ii", $stock_nuevo, $id);
                $update->execute();
                
                $movimiento = $conn->prepare("
                    INSERT INTO movimientos_inventario 
                    (id_producto, tipo, cantidad, stock_anterior, stock_nuevo, motivo, id_usuario) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($tipo === 'ajuste') {
                    $cantidad_registro = abs($cantidad_ajuste);
                    $tipo_registro = $cantidad_ajuste > 0 ? 'entrada' : 'salida';
                } else {
                    $cantidad_registro = $cantidad;
                    $tipo_registro = $tipo;
                }
                
                $movimiento->bind_param(
                    "isiiiis",
                    $id,
                    $tipo_registro,
                    $cantidad_registro,
                    $stock_anterior,
                    $stock_nuevo,
                    $motivo,
                    $_SESSION['user_id']
                );
                $movimiento->execute();
                
                $conn->commit();
                $success = 'Stock actualizado correctamente';
                
                // Recargar producto
                $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $producto = $stmt->get_result()->fetch_assoc();
                
                // Recargar movimientos
                $movimientos = [];
                $stmt = $conn->prepare("
                    SELECT m.*, u.username 
                    FROM movimientos_inventario m
                    LEFT JOIN usuarios u ON m.id_usuario = u.id
                    WHERE m.id_producto = ?
                    ORDER BY m.fecha_movimiento DESC
                    LIMIT 10
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $movimientos[] = $row;
                }
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Error al actualizar stock: ' . $e->getMessage();
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
            <i class="fas fa-cubes" style="color: var(--primary);"></i>
            <h1>Ajustar Stock</h1>
        </div>
        <span class="pv-badge">INVENTARIO</span>
    </div>
    
    <div class="pv-header-right" style="gap: 0.75rem;">
        <a href="<?php echo url('dashboard/productos/ver.php?id=' . $producto['id']); ?>" class="btn-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; text-decoration: none;">
            <i class="fas fa-eye"></i>
            Ver producto
        </a>
        <a href="<?php echo url('dashboard/productos/index.php'); ?>" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- ===== SECCIÓN DE IMAGEN ESTILO FACEBOOK (IGUAL QUE EN VER.PHP) ===== -->
<div class="producto-perfil-container">
    <div class="producto-perfil-header">
        <div class="perfil-imagen-wrapper">
            <div class="perfil-imagen">
                <?php if (!empty($producto['imagen_url'])): ?>
                    <img src="<?php echo BASE_URL . '/' . $producto['imagen_url']; ?>" 
                         alt="<?php echo h($producto['nombre']); ?>"
                         onerror="this.onerror=null; this.src='<?php echo asset('images/no-image.png'); ?>';">
                <?php else: ?>
                    <img src="<?php echo asset('images/no-image.png'); ?>" 
                         alt="Sin imagen">
                <?php endif; ?>
            </div>
        </div>
        <div class="perfil-info">
            <h1 class="perfil-nombre"><?php echo h($producto['nombre']); ?></h1>
            <div class="perfil-codigos">
                <span class="perfil-sku">SKU: <?php echo h($producto['sku']); ?></span>
                <span class="perfil-barras">Código: <?php echo $producto['codigo_barras'] ?: 'Sin código'; ?></span>
            </div>
            <div class="perfil-estado">
                <span class="badge-estado <?php echo $producto['activo'] ? 'activo' : 'inactivo'; ?>">
                    <?php echo $producto['activo'] ? 'Producto Activo' : 'Producto Inactivo'; ?>
                </span>
                <span class="badge-stock <?php 
                    echo $producto['stock_actual'] <= 0 ? 'agotado' : 
                        ($producto['stock_actual'] <= $producto['stock_minimo'] ? 'bajo' : 'normal'); 
                ?>">
                    <i class="fas fa-cubes"></i>
                    <?php echo $producto['stock_actual']; ?> unidades en stock
                </span>
            </div>
        </div>
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
        <div class="alerta-content">
            <p><?php echo $success; ?></p>
            <a href="<?php echo url('dashboard/productos/ajustar_stock.php?id=' . $producto['id']); ?>" class="btn-primary small" style="margin-top: 0.5rem;">
                <i class="fas fa-plus"></i>
                Hacer otro ajuste
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Formulario de ajuste (solo mostrar si no hay éxito) -->
<?php if (!$success): ?>
<div class="formulario-ajuste">
    <h3 class="formulario-titulo">
        <i class="fas fa-cubes"></i>
        Registrar movimiento de inventario
    </h3>
    
    <form method="POST" id="stockForm" class="ajuste-form">
        <!-- Tipo de movimiento -->
        <div class="tipo-grid">
            <!-- Entrada -->
            <label class="tipo-card <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'entrada') ? 'active' : ''; ?>">
                <input type="radio" name="tipo" value="entrada" class="tipo-radio" onchange="updateFormByType('entrada', this)" required>
                <div class="tipo-content">
                    <div class="tipo-icon entrada">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <h4 class="tipo-titulo">Entrada</h4>
                    <p class="tipo-descripcion">Compras, devoluciones</p>
                </div>
            </label>
            
            <!-- Salida -->
            <label class="tipo-card <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'salida') ? 'active' : ''; ?>">
                <input type="radio" name="tipo" value="salida" class="tipo-radio" onchange="updateFormByType('salida', this)" required>
                <div class="tipo-content">
                    <div class="tipo-icon salida">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <h4 class="tipo-titulo">Salida</h4>
                    <p class="tipo-descripcion">Ventas, mermas</p>
                </div>
            </label>
            
            <!-- Ajuste -->
            <label class="tipo-card <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'ajuste') ? 'active' : ''; ?>">
                <input type="radio" name="tipo" value="ajuste" class="tipo-radio" onchange="updateFormByType('ajuste', this)" required>
                <div class="tipo-content">
                    <div class="tipo-icon ajuste">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h4 class="tipo-titulo">Ajuste manual</h4>
                    <p class="tipo-descripcion">Conteo físico</p>
                </div>
            </label>
        </div>
        
        <!-- Campos dinámicos -->
        <div id="entradaFields" class="campos-dinamicos hidden">
            <div class="form-group">
                <label class="form-label">
                    Cantidad a agregar <span class="required">*</span>
                </label>
                <input type="number" name="cantidad_entrada" class="form-input" 
                       min="1" placeholder="Ej: 10"
                       value="<?php echo isset($_POST['cantidad_entrada']) ? $_POST['cantidad_entrada'] : ''; ?>">
                <small class="form-hint">Cantidad que aumentará al stock actual</small>
            </div>
        </div>
        
        <div id="salidaFields" class="campos-dinamicos hidden">
            <div class="form-group">
                <label class="form-label">
                    Cantidad a retirar <span class="required">*</span>
                </label>
                <input type="number" name="cantidad_salida" class="form-input" 
                       min="1" max="<?php echo $producto['stock_actual']; ?>" 
                       placeholder="Ej: 5"
                       value="<?php echo isset($_POST['cantidad_salida']) ? $_POST['cantidad_salida'] : ''; ?>">
                <small class="form-hint">
                    Stock disponible: <strong><?php echo $producto['stock_actual']; ?></strong> unidades
                </small>
            </div>
        </div>
        
        <div id="ajusteFields" class="campos-dinamicos hidden">
            <div class="form-group">
                <label class="form-label">
                    Nuevo stock exacto <span class="required">*</span>
                </label>
                <input type="number" name="cantidad_ajuste" class="form-input" 
                       min="0" value="<?php echo isset($_POST['cantidad_ajuste']) ? $_POST['cantidad_ajuste'] : $producto['stock_actual']; ?>">
                <small class="form-hint">
                    Stock actual: <strong><?php echo $producto['stock_actual']; ?></strong> unidades
                </small>
            </div>
        </div>
        
        <!-- Campo de motivo -->
        <div id="motivoField" class="campos-dinamicos hidden">
            <div class="form-group">
                <label class="form-label">
                    Motivo del movimiento <span class="required">*</span>
                </label>
                <textarea name="motivo" class="form-textarea" rows="3" 
                          placeholder="Ej: Compra a proveedor, venta, ajuste por inventario..."><?php echo isset($_POST['motivo']) ? h($_POST['motivo']) : ''; ?></textarea>
            </div>
        </div>
        
        <!-- Botones -->
        <div id="submitBtn" class="form-actions hidden">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i>
                Registrar Movimiento
            </button>
            <a href="<?php echo url('dashboard/productos/ver.php?id=' . $producto['id']); ?>" class="btn-cancel">
                <i class="fas fa-times"></i>
                Cancelar
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Últimos movimientos -->
<div class="movimientos-container">
    <h3 class="movimientos-titulo">
        <i class="fas fa-history"></i>
        Últimos movimientos de este producto
    </h3>
    
    <?php if (count($movimientos) > 0): ?>
        <div class="tabla-responsive">
            <table class="tabla-movimientos">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Stock Ant.</th>
                        <th class="text-right">Stock Nuevo</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $mov): ?>
                    <tr>
                        <td class="fecha-mov">
                            <?php echo date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])); ?>
                        </td>
                        <td>
                            <span class="tipo-badge <?php echo $mov['tipo']; ?>">
                                <?php 
                                echo $mov['tipo'] == 'entrada' ? '➕ Entrada' : 
                                    ($mov['tipo'] == 'salida' ? '➖ Salida' : '✏️ Ajuste'); 
                                ?>
                            </span>
                        </td>
                        <td class="text-right cantidad <?php echo $mov['tipo']; ?>">
                            <?php echo $mov['cantidad']; ?>
                        </td>
                        <td class="text-right"><?php echo $mov['stock_anterior']; ?></td>
                        <td class="text-right stock-nuevo"><?php echo $mov['stock_nuevo']; ?></td>
                        <td><?php echo h($mov['motivo']); ?></td>
                        <td><?php echo $mov['username'] ?: 'Sistema'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>No hay movimientos registrados para este producto</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Animación del formulario */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.campos-dinamicos {
    animation: slideDown 0.3s ease-out;
}
</style>

<script>
let currentType = '<?php echo isset($_POST['tipo']) ? $_POST['tipo'] : ''; ?>';

// Inicializar si hay un tipo preseleccionado
document.addEventListener('DOMContentLoaded', function() {
    if (currentType) {
        const radio = document.querySelector(`input[name="tipo"][value="${currentType}"]`);
        if (radio) {
            updateFormByType(currentType, radio);
        }
    }
});

function updateFormByType(type, element) {
    currentType = type;
    
    // Actualizar estilos de las tarjetas
    document.querySelectorAll('.tipo-card').forEach(card => {
        card.classList.remove('active');
    });
    element.closest('.tipo-card').classList.add('active');
    
    // Ocultar todos los campos específicos
    document.getElementById('entradaFields').classList.add('hidden');
    document.getElementById('salidaFields').classList.add('hidden');
    document.getElementById('ajusteFields').classList.add('hidden');
    
    // Mostrar el campo correspondiente
    document.getElementById(type + 'Fields').classList.remove('hidden');
    
    // Mostrar campo de motivo y botón
    document.getElementById('motivoField').classList.remove('hidden');
    document.getElementById('submitBtn').classList.remove('hidden');
    
    // Remover required de todos los campos
    document.querySelectorAll('input[name^="cantidad"]').forEach(input => {
        input.required = false;
    });
    
    // Agregar required al campo activo
    if (type === 'entrada') {
        document.querySelector('input[name="cantidad_entrada"]').required = true;
    } else if (type === 'salida') {
        document.querySelector('input[name="cantidad_salida"]').required = true;
    } else if (type === 'ajuste') {
        document.querySelector('input[name="cantidad_ajuste"]').required = true;
    }
}

// Manejar el envío del formulario
document.getElementById('stockForm')?.addEventListener('submit', function(e) {
    if (!currentType) {
        e.preventDefault();
        alert('❌ Selecciona un tipo de movimiento');
        return;
    }
    
    let cantidad;
    let errorMsg = '';
    
    if (currentType === 'entrada') {
        cantidad = document.querySelector('input[name="cantidad_entrada"]').value;
        if (!cantidad) {
            errorMsg = '❌ Ingresa una cantidad';
        } else if (parseInt(cantidad) <= 0) {
            errorMsg = '❌ La cantidad debe ser mayor a cero';
        }
    } else if (currentType === 'salida') {
        cantidad = document.querySelector('input[name="cantidad_salida"]').value;
        if (!cantidad) {
            errorMsg = '❌ Ingresa una cantidad';
        } else if (parseInt(cantidad) <= 0) {
            errorMsg = '❌ La cantidad debe ser mayor a cero';
        } else if (parseInt(cantidad) > <?php echo $producto['stock_actual']; ?>) {
            errorMsg = '❌ No hay suficiente stock disponible';
        }
    } else if (currentType === 'ajuste') {
        cantidad = document.querySelector('input[name="cantidad_ajuste"]').value;
        if (cantidad === '') {
            errorMsg = '❌ Ingresa el nuevo stock';
        } else if (parseInt(cantidad) < 0) {
            errorMsg = '❌ El stock no puede ser negativo';
        }
    }
    
    const motivo = document.querySelector('textarea[name="motivo"]').value;
    if (!motivo) {
        errorMsg = '❌ Ingresa un motivo para el movimiento';
    }
    
    if (errorMsg) {
        e.preventDefault();
        alert(errorMsg);
        return;
    }
    
    // Crear campo oculto con la cantidad unificada
    const inputCantidad = document.querySelector('input[name="cantidad"]');
    if (inputCantidad) inputCantidad.remove();
    
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'cantidad';
    input.value = cantidad;
    this.appendChild(input);
});
</script>

<?php include '../footer.php'; ?>