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

// Obtener producto actual
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
    header('Location: ' . url('dashboard/productos/index.php'));
    exit;
}

// Obtener categorías y marcas
$categorias = $conn->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
$marcas = $conn->query("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku'] ?? '');
    $codigo_barras = trim($_POST['codigo_barras'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_categoria = !empty($_POST['categoria']) ? (int)$_POST['categoria'] : null;
    $id_marca = !empty($_POST['marca']) ? (int)$_POST['marca'] : null;
    $precio_compra = floatval($_POST['precio_compra'] ?? 0);
    $precio_venta = floatval($_POST['precio_venta'] ?? 0);
    $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($sku)) {
        $error = 'El código SKU es obligatorio';
    } elseif (empty($nombre)) {
        $error = 'El nombre del producto es obligatorio';
    } elseif ($precio_venta <= 0) {
        $error = 'El precio de venta debe ser mayor a cero';
    } else {
        // Verificar SKU único (excepto este producto)
        $stmt = $conn->prepare("SELECT id FROM productos WHERE sku = ? AND id != ?");
        $stmt->bind_param("si", $sku, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Ya existe otro producto con ese SKU';
        }
        
        // Verificar código de barras único (si se proporcionó)
        if (empty($error) && !empty($codigo_barras)) {
            $stmt = $conn->prepare("SELECT id FROM productos WHERE codigo_barras = ? AND id != ?");
            $stmt->bind_param("si", $codigo_barras, $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Ya existe otro producto con ese código de barras';
            }
        }
        
        if (empty($error)) {
            // Manejo de imagen
            $imagen_url = $producto['imagen_url'];
            
            // Si se subió nueva imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = BASE_PATH . '/assets/images/productos/';
                
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $nombre_archivo = 'producto_' . $id . '_' . time() . '.' . $extension;
                $ruta_completa = $upload_dir . $nombre_archivo;
                
                $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($extension, $tipos_permitidos)) {
                    if ($_FILES['imagen']['size'] <= 2 * 1024 * 1024) {
                        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
                            // Eliminar imagen anterior si existe
                            if (!empty($producto['imagen_url']) && file_exists(BASE_PATH . '/' . $producto['imagen_url'])) {
                                unlink(BASE_PATH . '/' . $producto['imagen_url']);
                            }
                            $imagen_url = 'assets/images/productos/' . $nombre_archivo;
                        } else {
                            $error = 'Error al subir la imagen';
                        }
                    } else {
                        $error = 'La imagen no puede ser mayor a 2MB';
                    }
                } else {
                    $error = 'Tipo de archivo no permitido. Use JPG, PNG, GIF o WEBP';
                }
            }
            
            // Si se solicitó eliminar imagen
            if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
                if (!empty($producto['imagen_url']) && file_exists(BASE_PATH . '/' . $producto['imagen_url'])) {
                    unlink(BASE_PATH . '/' . $producto['imagen_url']);
                }
                $imagen_url = null;
            }
            
            if (empty($error)) {
                $stmt = $conn->prepare("
                    UPDATE productos SET 
                        sku = ?, codigo_barras = ?, nombre = ?, descripcion = ?,
                        id_categoria = ?, id_marca = ?, precio_compra = ?, precio_venta = ?,
                        stock_minimo = ?, imagen_url = ?, activo = ?
                    WHERE id = ?
                ");
                
                $stmt->bind_param(
                    "ssssiiddissi",
                    $sku,
                    $codigo_barras,
                    $nombre,
                    $descripcion,
                    $id_categoria,
                    $id_marca,
                    $precio_compra,
                    $precio_venta,
                    $stock_minimo,
                    $imagen_url,
                    $activo,
                    $id
                );
                
                if ($stmt->execute()) {
                    $success = 'Producto actualizado correctamente';
                    // Recargar datos
                    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $producto = $stmt->get_result()->fetch_assoc();
                } else {
                    $error = 'Error al actualizar: ' . $conn->error;
                }
            }
        }
    }
}

include '../header.php';
?>

<!-- Header del formulario -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-edit" style="color: var(--primary);"></i>
            <h1>Editar Producto</h1>
        </div>
        <span class="pv-badge">EDITAR</span>
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
            <div class="alerta-acciones">
                <a href="<?php echo url('dashboard/productos/editar.php?id=' . $producto['id']); ?>" class="btn-primary small">
                    <i class="fas fa-edit"></i> Seguir editando
                </a>
                <a href="<?php echo url('dashboard/productos/ver.php?id=' . $producto['id']); ?>" class="btn-secondary small">
                    <i class="fas fa-eye"></i> Ver producto
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- SECCIÓN DE IMAGEN ESTILO FACEBOOK -->
<div class="producto-imagen-container">
    <div class="producto-imagen-header">
        <div class="imagen-wrapper">
            <div class="imagen-producto" id="imagenProducto">
                <?php if (!empty($producto['imagen_url'])): ?>
                    <img src="<?php echo BASE_URL . '/' . $producto['imagen_url']; ?>" 
                         alt="<?php echo h($producto['nombre']); ?>" 
                         id="imagenPreview"
                         onerror="this.onerror=null; this.src='<?php echo asset('images/no-image.png'); ?>';">
                <?php else: ?>
                    <img src="<?php echo asset('images/no-image.png'); ?>" 
                         alt="Sin imagen" 
                         id="imagenPreview">
                <?php endif; ?>
                <div class="imagen-overlay" id="imagenOverlay">
                    <i class="fas fa-camera"></i>
                    <span>Cambiar foto</span>
                </div>
                <input type="file" name="imagen" id="imagenInput" accept="image/*" class="hidden">
            </div>
        </div>
        <div class="producto-titulo-imagen">
            <h2><?php echo h($producto['nombre']); ?></h2>
            <p class="producto-sku">SKU: <?php echo h($producto['sku']); ?> | Código: <?php echo $producto['codigo_barras'] ?: 'Sin código'; ?></p>
            <div class="producto-estado-imagen">
                <span class="badge-estado <?php echo $producto['activo'] ? 'activo' : 'inactivo'; ?>">
                    <?php echo $producto['activo'] ? 'Producto Activo' : 'Producto Inactivo'; ?>
                </span>
                <span class="badge-stock">
                    Stock: <strong><?php echo $producto['stock_actual']; ?></strong> unidades
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Formulario de edición -->
<div class="form-container">
    <form method="POST" id="formProducto" class="producto-form" enctype="multipart/form-data">
        <div class="form-grid">
            <!-- Columna izquierda - Información básica -->
            <div class="form-col">
                <h3 class="form-section-title">
                    <i class="fas fa-info-circle"></i>
                    Información básica
                </h3>
                
                <!-- SKU -->
                <div class="form-group">
                    <label class="form-label">
                        Código SKU <span class="required">*</span>
                    </label>
                    <input type="text" name="sku" class="form-input" 
                           value="<?php echo h($producto['sku']); ?>"
                           placeholder="Ej: TRP-001-24" required>
                    <small class="form-hint">Identificador único del producto</small>
                </div>
                
                <!-- Código de barras -->
                <div class="form-group">
                    <label class="form-label">
                        Código de barras
                    </label>
                    <input type="text" name="codigo_barras" class="form-input" 
                           value="<?php echo h($producto['codigo_barras']); ?>"
                           placeholder="Ej: 7501234567890">
                    <small class="form-hint">Opcional - Código de barras del producto</small>
                </div>
                
                <!-- Nombre -->
                <div class="form-group">
                    <label class="form-label">
                        Nombre del producto <span class="required">*</span>
                    </label>
                    <input type="text" name="nombre" class="form-input" required
                           value="<?php echo h($producto['nombre']); ?>"
                           placeholder="Ej: Martillo de Uña 16oz">
                </div>
                
                <!-- Descripción -->
                <div class="form-group">
                    <label class="form-label">
                        Descripción
                    </label>
                    <textarea name="descripcion" class="form-textarea" rows="4" 
                              placeholder="Descripción detallada del producto"><?php echo h($producto['descripcion']); ?></textarea>
                </div>
                
                <!-- Categoría y Marca -->
                <div class="form-row">
                    <div class="form-group half">
                        <label class="form-label">
                            Categoría
                        </label>
                        <select name="categoria" class="form-select">
                            <option value="">Seleccionar categoría</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $producto['id_categoria'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo h($cat['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group half">
                        <label class="form-label">
                            Marca
                        </label>
                        <select name="marca" class="form-select">
                            <option value="">Seleccionar marca</option>
                            <?php while ($marca = $marcas->fetch_assoc()): ?>
                                <option value="<?php echo $marca['id']; ?>" 
                                    <?php echo $producto['id_marca'] == $marca['id'] ? 'selected' : ''; ?>>
                                    <?php echo h($marca['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha - Precios y stock -->
            <div class="form-col">
                <h3 class="form-section-title">
                    <i class="fas fa-dollar-sign"></i>
                    Precios y stock
                </h3>
                
                <!-- Precios -->
                <div class="form-row">
                    <div class="form-group half">
                        <label class="form-label">
                            Precio de compra
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="number" name="precio_compra" class="form-input" 
                                   step="0.01" min="0" value="<?php echo $producto['precio_compra']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group half">
                        <label class="form-label">
                            Precio de venta <span class="required">*</span>
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="number" name="precio_venta" class="form-input" 
                                   step="0.01" min="0.01" required value="<?php echo $producto['precio_venta']; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Stock actual (solo lectura) -->
                <div class="form-row">
                    <div class="form-group half">
                        <label class="form-label">
                            Stock actual
                        </label>
                        <div class="stock-actual-display">
                            <span class="stock-actual-valor"><?php echo $producto['stock_actual']; ?></span>
                            <span class="stock-actual-unidad">unidades</span>
                            <a href="<?php echo url('dashboard/productos/ajustar_stock.php?id=' . $producto['id']); ?>" 
                               class="btn-ajustar">
                                Ajustar
                            </a>
                        </div>
                        <small class="form-hint">Usa el botón "Ajustar" para modificar el stock</small>
                    </div>
                    
                    <div class="form-group half">
                        <label class="form-label">
                            Stock mínimo
                        </label>
                        <input type="number" name="stock_minimo" class="form-input" 
                               min="0" value="<?php echo $producto['stock_minimo']; ?>">
                    </div>
                </div>
                
                <!-- Opciones de imagen -->
                <?php if (!empty($producto['imagen_url'])): ?>
                <div class="opciones-imagen" id="opcionesImagen">
                    <button type="button" class="btn-eliminar-imagen" onclick="eliminarImagen()">
                        <i class="fas fa-trash"></i> Eliminar imagen actual
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Estado activo -->
                <div class="estado-activo-container">
                    <label class="checkbox-label">
                        <input type="checkbox" name="activo" class="checkbox-input" 
                               <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">Producto activo</span>
                    </label>
                    <p class="estado-ayuda">Si está inactivo, no aparecerá en el punto de venta</p>
                </div>
                
                <!-- Información adicional -->
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div class="info-box-content">
                        <h4>Información importante</h4>
                        <p>
                            El stock se gestiona desde el botón "Ajustar". 
                            El código SKU debe ser único en el sistema.
                            Haz clic en la imagen para cambiarla.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i>
                Guardar Cambios
            </button>
            
            <a href="<?php echo url('dashboard/productos/index.php'); ?>" class="btn-cancel">
                <i class="fas fa-times"></i>
                Cancelar
            </a>
        </div>
    </form>
</div>

<style>
/* Animación del formulario */
@keyframes fadeInForm {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.producto-form {
    animation: fadeInForm 0.3s ease-out;
}

/* ===== ESTILOS PARA IMAGEN ESTILO FACEBOOK ===== */
.producto-imagen-container {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
}

.producto-imagen-header {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.imagen-wrapper {
    position: relative;
    flex-shrink: 0;
}

.imagen-producto {
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 4px solid var(--gray-200);
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
}

.imagen-producto:hover {
    border-color: var(--primary);
    transform: scale(1.05);
}

.imagen-producto img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.imagen-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.imagen-producto:hover .imagen-overlay {
    opacity: 1;
}

.imagen-overlay i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.imagen-overlay span {
    font-size: 0.8rem;
    font-weight: 500;
}

.producto-titulo-imagen {
    flex: 1;
}

.producto-titulo-imagen h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.producto-sku {
    font-size: 1rem;
    color: var(--gray-500);
    margin-bottom: 1rem;
    font-family: monospace;
}

.producto-estado-imagen {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.badge-estado {
    padding: 0.4rem 1rem;
    border-radius: var(--radius-lg);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-estado.activo {
    background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
    color: var(--success);
}

.badge-estado.inactivo {
    background: linear-gradient(135deg, var(--danger-light) 0%, #fee2e2 100%);
    color: var(--danger);
}

.badge-stock {
    padding: 0.4rem 1rem;
    background: linear-gradient(135deg, var(--primary-alpha) 0%, var(--secondary-alpha) 100%);
    border-radius: var(--radius-lg);
    font-size: 0.8rem;
    color: var(--gray-700);
}

.badge-stock strong {
    color: var(--primary);
}

/* Botón eliminar imagen */
.btn-eliminar-imagen {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: var(--danger-light);
    border: 1px solid var(--danger);
    border-radius: var(--radius-md);
    color: var(--danger);
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 1rem;
}

.btn-eliminar-imagen:hover {
    background-color: var(--danger);
    color: white;
}

.hidden {
    display: none;
}

/* Responsive */
@media (max-width: 768px) {
    .producto-imagen-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .producto-titulo-imagen h2 {
        font-size: 1.4rem;
    }
    
    .producto-estado-imagen {
        justify-content: center;
        flex-wrap: wrap;
    }
}
</style>

<script>
// Variable para detectar cambios
let formChanged = false;

// Detectar cambios en todos los campos
document.querySelectorAll('#formProducto input, #formProducto select, #formProducto textarea').forEach(input => {
    input.addEventListener('change', () => formChanged = true);
    input.addEventListener('input', () => formChanged = true);
});

// Resetear el flag cuando se envía el formulario
document.getElementById('formProducto').addEventListener('submit', function() {
    formChanged = false;
});

// Confirmar antes de salir si hay cambios
window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'Hay cambios sin guardar. ¿Estás seguro de salir?';
    }
});

// ===== FUNCIONALIDAD PARA LA IMAGEN =====
const imagenProducto = document.getElementById('imagenProducto');
const imagenInput = document.getElementById('imagenInput');
const imagenPreview = document.getElementById('imagenPreview');
const opcionesImagen = document.getElementById('opcionesImagen');

// Click en la imagen para seleccionar archivo
if (imagenProducto) {
    imagenProducto.addEventListener('click', function() {
        imagenInput.click();
    });
}

// Cuando se selecciona un archivo
imagenInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('❌ La imagen no puede ser mayor a 2MB');
            return;
        }
        
        // Validar tipo
        const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!tiposPermitidos.includes(file.type)) {
            alert('❌ Solo se permiten archivos JPG, PNG, GIF o WEBP');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            imagenPreview.src = e.target.result;
            if (opcionesImagen) opcionesImagen.style.display = 'none';
            formChanged = true;
        };
        reader.readAsDataURL(file);
    }
});

// Función para eliminar imagen
function eliminarImagen() {
    if (confirm('¿Eliminar la imagen actual?')) {
        <?php if (!empty($producto['imagen_url'])): ?>
        imagenPreview.src = '<?php echo asset('images/no-image.png'); ?>';
        if (opcionesImagen) opcionesImagen.style.display = 'none';
        formChanged = true;
        
        // Crear un input oculto para indicar que se eliminó la imagen
        const inputEliminar = document.createElement('input');
        inputEliminar.type = 'hidden';
        inputEliminar.name = 'eliminar_imagen';
        inputEliminar.value = '1';
        document.getElementById('formProducto').appendChild(inputEliminar);
        <?php endif; ?>
    }
}

// Validar que precio venta >= precio compra
document.querySelector('input[name="precio_venta"]')?.addEventListener('change', function() {
    const compra = parseFloat(document.querySelector('input[name="precio_compra"]').value) || 0;
    const venta = parseFloat(this.value) || 0;
    
    if (venta < compra) {
        if (!confirm('⚠️ El precio de venta es menor que el precio de compra. ¿Estás seguro?')) {
            this.value = compra + 1;
        }
    }
});

// Prevenir envío si hay errores
document.getElementById('formProducto').addEventListener('submit', function(e) {
    const precioVenta = parseFloat(document.querySelector('input[name="precio_venta"]').value) || 0;
    const sku = document.querySelector('input[name="sku"]').value.trim();
    
    if (precioVenta <= 0) {
        e.preventDefault();
        alert('❌ El precio de venta debe ser mayor a cero');
        return;
    }
    
    if (!sku) {
        e.preventDefault();
        alert('❌ El código SKU es obligatorio');
        return;
    }
});
</script>

<?php include '../footer.php'; ?>