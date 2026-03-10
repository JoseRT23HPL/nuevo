<?php
require_once __DIR__ . '/../../config.php';
requiereAuth(); // Solo usuarios autenticados pueden acceder

// Obtener categorías y marcas de la base de datos para los selects
$conn = getDB();

// Categorías activas
$categorias = [];
$result = $conn->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Marcas activas
$marcas = [];
$result = $conn->query("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $marcas[] = $row;
    }
}

$error = '';
$success = '';
$producto_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $sku = trim($_POST['sku'] ?? '');
    $codigo_barras = trim($_POST['codigo_barras'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_categoria = !empty($_POST['categoria']) ? (int)$_POST['categoria'] : null;
    $id_marca = !empty($_POST['marca']) ? (int)$_POST['marca'] : null;
    $precio_compra = floatval($_POST['precio_compra'] ?? 0);
    $precio_venta = floatval($_POST['precio_venta'] ?? 0);
    $stock_inicial = (int)($_POST['stock_inicial'] ?? 0);
    $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
    
    // Validaciones
    if (empty($sku)) {
        $error = 'El código SKU es obligatorio';
    } elseif (empty($nombre)) {
        $error = 'El nombre del producto es obligatorio';
    } elseif ($precio_venta <= 0) {
        $error = 'El precio de venta debe ser mayor a cero';
    } elseif ($stock_inicial < 0) {
        $error = 'El stock inicial no puede ser negativo';
    } else {
        // Verificar si el SKU ya existe
        $stmt = $conn->prepare("SELECT id FROM productos WHERE sku = ?");
        $stmt->bind_param("s", $sku);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Ya existe un producto con ese SKU';
        } elseif (!empty($codigo_barras)) {
            // Verificar si el código de barras ya existe (si se proporcionó)
            $stmt = $conn->prepare("SELECT id FROM productos WHERE codigo_barras = ?");
            $stmt->bind_param("s", $codigo_barras);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Ya existe un producto con ese código de barras';
            }
        }
        
        if (empty($error)) {
            // Manejar subida de imagen
            $imagen_url = null;
            
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = BASE_PATH . '/assets/images/productos/';
                
                // Crear directorio si no existe
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $file_name = 'producto_' . time() . '_' . uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_types)) {
                    // Validar tamaño (2MB)
                    if ($_FILES['imagen']['size'] <= 2 * 1024 * 1024) {
                        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $file_path)) {
                            $imagen_url = 'assets/images/productos/' . $file_name;
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
            
            if (empty($error)) {
                // Insertar producto
                $stmt = $conn->prepare("
                    INSERT INTO productos (
                        sku, codigo_barras, nombre, descripcion, 
                        id_categoria, id_marca, precio_compra, precio_venta, 
                        stock_actual, stock_minimo, imagen_url
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param(
                    "ssssiiiddis",
                    $sku,
                    $codigo_barras,
                    $nombre,
                    $descripcion,
                    $id_categoria,
                    $id_marca,
                    $precio_compra,
                    $precio_venta,
                    $stock_inicial,
                    $stock_minimo,
                    $imagen_url
                );
                
                if ($stmt->execute()) {
                    $producto_id = $conn->insert_id;
                    
                    // Registrar movimiento de inventario inicial si hay stock
                    if ($stock_inicial > 0) {
                        $movimiento = $conn->prepare("
                            INSERT INTO movimientos_inventario 
                            (id_producto, tipo, cantidad, stock_anterior, stock_nuevo, motivo, id_usuario) 
                            VALUES (?, 'entrada', ?, 0, ?, 'Stock inicial', ?)
                        ");
                        $movimiento->bind_param("iiii", $producto_id, $stock_inicial, $stock_inicial, $_SESSION['user_id']);
                        $movimiento->execute();
                    }
                    
                    $success = 'Producto creado correctamente';
                } else {
                    $error = 'Error al crear el producto: ' . $conn->error;
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
            <i class="fas fa-plus-circle"></i>
            <h1>Nuevo Producto</h1>
        </div>
        <span class="pv-badge">CREAR</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/productos/index.php'); ?>" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver a la lista
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
                <a href="/dashboard/productos/nuevo.php" class="btn-primary small">
                    <i class="fas fa-plus"></i> Otro Producto
                </a>
                <a href="/dashboard/productos/index.php" class="btn-secondary small">
                    <i class="fas fa-list"></i> Ver Listado
                </a>
                <?php if ($producto_id): ?>
                    <a href="/dashboard/productos/ver.php?id=<?php echo $producto_id; ?>" class="btn-tertiary small">
                        <i class="fas fa-eye"></i> Ver Producto
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!$success): ?>
    <!-- Formulario de nuevo producto -->
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data" class="producto-form">
            <div class="form-grid">
                <!-- Columna izquierda - Información básica -->
                <div class="form-col">
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Información básica
                    </h3>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Código SKU <span class="required">*</span>
                        </label>
                        <input type="text" name="sku" class="form-input" 
                               placeholder="Ej: TRP-001-24" 
                               value="<?php echo h($_POST['sku'] ?? ''); ?>" required>
                        <small class="form-hint">Identificador único del producto (Ej: MARCA-MODELO-TALLA)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Código de barras
                        </label>
                        <input type="text" name="codigo_barras" class="form-input" 
                               placeholder="Ej: 7501234567890"
                               value="<?php echo h($_POST['codigo_barras'] ?? ''); ?>">
                        <small class="form-hint">Opcional - Código de barras del producto</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Nombre del producto <span class="required">*</span>
                        </label>
                        <input type="text" name="nombre" class="form-input" 
                               placeholder="Ej: Martillo de Uña 16oz" 
                               value="<?php echo h($_POST['nombre'] ?? ''); ?>" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Descripción
                        </label>
                        <textarea name="descripcion" class="form-textarea" rows="4" 
                                  placeholder="Descripción detallada del producto"><?php echo h($_POST['descripcion'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                Categoría
                            </label>
                            <select name="categoria" class="form-select">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo h($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                Marca
                            </label>
                            <select name="marca" class="form-select">
                                <option value="">Seleccionar marca</option>
                                <?php foreach ($marcas as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"
                                        <?php echo (isset($_POST['marca']) && $_POST['marca'] == $m['id']) ? 'selected' : ''; ?>>
                                        <?php echo h($m['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Columna derecha - Precios, stock e IMAGEN -->
                <div class="form-col">
                    <h3 class="form-section-title">
                        <i class="fas fa-dollar-sign"></i>
                        Precios y stock
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                Precio de compra
                            </label>
                            <div class="input-prefix">
                                <span>$</span>
                                <input type="number" name="precio_compra" class="form-input" 
                                       step="0.01" min="0" value="<?php echo $_POST['precio_compra'] ?? '0.00'; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                Precio de venta <span class="required">*</span>
                            </label>
                            <div class="input-prefix">
                                <span>$</span>
                                <input type="number" name="precio_venta" class="form-input" 
                                       step="0.01" min="0.01" required
                                       value="<?php echo $_POST['precio_venta'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                Stock inicial
                            </label>
                            <input type="number" name="stock_inicial" class="form-input" 
                                   min="0" value="<?php echo $_POST['stock_inicial'] ?? '0'; ?>">
                            <small class="form-hint">Cantidad que entra al inventario</small>
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                Stock mínimo
                            </label>
                            <input type="number" name="stock_minimo" class="form-input" 
                                   min="0" value="<?php echo $_POST['stock_minimo'] ?? '5'; ?>">
                            <small class="form-hint">Alerta cuando el stock baje de aquí</small>
                        </div>
                    </div>
                    
                    <!-- SECCIÓN DE IMAGEN -->
                    <h3 class="form-section-title" style="margin-top: 1.5rem;">
                        <i class="fas fa-image"></i>
                        Imagen del producto
                    </h3>
                    
                    <div class="form-group">
                        <div class="image-upload-area" id="imageUploadArea">
                            <div class="image-preview" id="imagePreview">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Haz clic o arrastra una imagen aquí</p>
                                <small>Formatos: JPG, PNG, GIF, WEBP (Max. 2MB)</small>
                            </div>
                            <input type="file" name="imagen" id="imagenInput" accept="image/*" class="hidden">
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="info-box" style="margin-top: 1rem;">
                        <i class="fas fa-info-circle"></i>
                        <div class="info-box-content">
                            <h4>Información importante</h4>
                            <p>
                                El stock inicial se registrará automáticamente como un movimiento de entrada.
                                El código SKU debe ser único en el sistema.
                                La imagen es opcional y se mostrará en el catálogo.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Guardar Producto
                </button>
                
                <a href="/dashboard/productos/index.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

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

/* Estilos para el área de carga de imagen */
.image-upload-area {
    border: 2px dashed var(--gray-300);
    border-radius: var(--radius-lg);
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: var(--gray-50);
    margin-bottom: 1rem;
}

.image-upload-area:hover {
    border-color: var(--primary);
    background-color: var(--primary-alpha);
    transform: translateY(-2px);
}

.image-upload-area:hover i {
    color: var(--primary);
    transform: scale(1.1);
}

.image-preview i {
    font-size: 3rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.image-preview p {
    font-size: 1rem;
    color: var(--gray-600);
    margin-bottom: 0.5rem;
}

.image-preview small {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    margin: 0 auto;
    display: block;
}

/* Highlight cuando se arrastra */
.image-upload-area.dragover {
    border-color: var(--primary);
    background-color: var(--primary-alpha);
    transform: scale(1.02);
}

.hidden {
    display: none;
}
</style>

<script>
// Previsualización de imagen
const uploadArea = document.getElementById('imageUploadArea');
const imagenInput = document.getElementById('imagenInput');
const imagePreview = document.getElementById('imagePreview');

// Click en el área para seleccionar archivo
uploadArea.addEventListener('click', function() {
    imagenInput.click();
});

// Arrastrar y soltar
uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        procesarImagen(file);
    } else {
        alert('❌ Por favor, selecciona un archivo de imagen válido');
    }
});

// Cuando se selecciona un archivo
imagenInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        procesarImagen(file);
    }
});

// Función para procesar la imagen
function procesarImagen(file) {
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
        imagePreview.innerHTML = `
            <img src="${e.target.result}" alt="Vista previa">
            <p style="margin-top: 1rem; font-size: 0.9rem; color: var(--success);">
                <i class="fas fa-check-circle"></i> Imagen lista para subir
            </p>
            <small style="color: var(--gray-500);">Haz clic para cambiar</small>
        `;
    };
    reader.readAsDataURL(file);
}

// Validar que precio venta >= precio compra
document.querySelector('input[name="precio_venta"]').addEventListener('change', function() {
    const compra = parseFloat(document.querySelector('input[name="precio_compra"]').value) || 0;
    const venta = parseFloat(this.value) || 0;
    
    if (venta < compra) {
        if (!confirm('⚠️ El precio de venta es menor que el precio de compra. ¿Estás seguro?')) {
            this.value = compra + 1;
        }
    }
});

// Sugerir SKU basado en el nombre (opcional)
document.querySelector('input[name="nombre"]').addEventListener('blur', function() {
    const skuInput = document.querySelector('input[name="sku"]');
    if (!skuInput.value && this.value) {
        const sugerencia = this.value
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '-')
            .replace(/-+/g, '-')
            .substring(0, 15);
        skuInput.placeholder = `Ej: ${sugerencia}-001`;
    }
});

// Prevenir envío si hay errores
document.querySelector('.producto-form').addEventListener('submit', function(e) {
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