<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

$error = '';
$success = '';
$producto_encontrado = null;
$resultados_busqueda = [];

// Buscar por código de barras (viene del escáner o búsqueda manual)
if (isset($_GET['codigo']) || isset($_GET['buscar'])) {
    $termino = isset($_GET['codigo']) ? trim($_GET['codigo']) : trim($_GET['buscar']);
    
    // Buscar por código de barras exacto
    $stmt = $conn->prepare("SELECT * FROM productos WHERE codigo_barras = ? AND activo = 1");
    $stmt->bind_param("s", $termino);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $producto_encontrado = $result->fetch_assoc();
    } else {
        // Si no se encuentra por código, buscar por nombre (para búsqueda manual)
        $termino_like = "%$termino%";
        $stmt = $conn->prepare("
            SELECT id, sku, codigo_barras, nombre, stock_actual, stock_minimo, imagen_url 
            FROM productos 
            WHERE (nombre LIKE ? OR sku LIKE ?) AND activo = 1 
            LIMIT 10
        ");
        $stmt->bind_param("ss", $termino_like, $termino_like);
        $stmt->execute();
        $resultados_busqueda = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($resultados_busqueda)) {
            $error = "No se encontraron productos con: $termino";
        }
    }
}

// Procesar entrada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? 'Entrada por escáner');
    
    if ($cantidad <= 0) {
        $error = 'La cantidad debe ser mayor a cero';
    } else {
        // Obtener producto
        $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        
        if ($producto) {
            $stock_anterior = $producto['stock_actual'];
            $stock_nuevo = $stock_anterior + $cantidad;
            
            // Iniciar transacción
            $conn->begin_transaction();
            
            try {
                // Actualizar stock
                $update = $conn->prepare("UPDATE productos SET stock_actual = ? WHERE id = ?");
                $update->bind_param("ii", $stock_nuevo, $id_producto);
                $update->execute();
                
                // Registrar movimiento
                $movimiento = $conn->prepare("
                    INSERT INTO movimientos_inventario 
                    (id_producto, tipo, cantidad, stock_anterior, stock_nuevo, motivo, id_usuario) 
                    VALUES (?, 'entrada', ?, ?, ?, ?, ?)
                ");
                $movimiento->bind_param("iiiisi", $id_producto, $cantidad, $stock_anterior, $stock_nuevo, $motivo, $_SESSION['user_id']);
                $movimiento->execute();
                
                $conn->commit();
                $success = "Entrada registrada: +$cantidad unidades a " . $producto['nombre'];
                
                // Limpiar para siguiente entrada
                $producto_encontrado = null;
                $resultados_busqueda = [];
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Error al registrar entrada: ' . $e->getMessage();
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
            <i class="fas fa-barcode" style="color: var(--primary);"></i>
            <h1>Entrada Rápida por Escáner</h1>
        </div>
        <span class="pv-badge">INVENTARIO</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/productos/index.php'); ?>" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver a Productos
        </a>
    </div>
</div>

<div class="entrada-rapida-container">
    <!-- Instrucciones -->
    <div class="instrucciones-box">
        <div class="instrucciones-icono">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="instrucciones-contenido">
            <h4>Modo de uso:</h4>
            <ul>
                <li>1. Escanea el código de barras o busca por nombre</li>
                <li>2. El producto se cargará automáticamente</li>
                <li>3. Ingresa la cantidad y motivo</li>
                <li>4. Confirma la entrada</li>
            </ul>
        </div>
    </div>
    
    <!-- Buscador por escáner y nombre -->
    <div class="escaner-box">
        <div class="escaner-icono">
            <i class="fas fa-barcode"></i>
        </div>
        <h2>Buscar Producto</h2>
        
        <form method="GET" class="escaner-form" autocomplete="off">
            <div class="escaner-input-group">
                <div class="escaner-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" name="buscar" id="buscarInput" 
                           value="<?php echo isset($_GET['buscar']) ? h($_GET['buscar']) : ''; ?>"
                           placeholder="Código de barras o nombre del producto" 
                           class="escaner-input" autofocus>
                </div>
                <button type="submit" class="btn-escanear">
                    <i class="fas fa-search"></i>
                    Buscar
                </button>
            </div>
            <p class="escaner-hint">
                <i class="fas fa-info-circle"></i>
                Puedes escanear el código de barras o escribir el nombre del producto
            </p>
        </form>
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
    
    <!-- Resultados de búsqueda múltiples -->
    <?php if (!empty($resultados_busqueda) && !$producto_encontrado): ?>
        <div class="resultados-multiples">
            <h3>Selecciona un producto:</h3>
            <div class="resultados-grid">
                <?php foreach ($resultados_busqueda as $prod): ?>
                <a href="?codigo=<?php echo urlencode($prod['codigo_barras'] ?: $prod['sku']); ?>" class="resultado-item-card">
                    <div class="resultado-imagen">
                        <?php if (!empty($prod['imagen_url'])): ?>
                            <img src="<?php echo BASE_URL . '/' . $prod['imagen_url']; ?>" 
                                 alt="<?php echo h($prod['nombre']); ?>"
                                 onerror="this.onerror=null; this.src='<?php echo asset('images/no-image.png'); ?>';">
                        <?php else: ?>
                            <img src="<?php echo asset('images/no-image.png'); ?>" alt="Sin imagen">
                        <?php endif; ?>
                    </div>
                    <div class="resultado-info">
                        <strong><?php echo h($prod['nombre']); ?></strong>
                        <small>SKU: <?php echo h($prod['sku']); ?></small>
                        <?php if ($prod['codigo_barras']): ?>
                            <small>Código: <?php echo $prod['codigo_barras']; ?></small>
                        <?php endif; ?>
                        <span class="resultado-stock">Stock: <?php echo $prod['stock_actual']; ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Formulario de entrada si hay producto encontrado -->
    <?php if ($producto_encontrado): ?>
        <div class="producto-encontrado-card">
            <div class="producto-encontrado-header">
                <h3>Producto Encontrado</h3>
                <span class="badge-activo">ACTIVO</span>
            </div>
            
            <div class="producto-encontrado-body">
                <!-- Imagen del producto (estilo Facebook) -->
                <div class="producto-imagen">
                    <?php if (!empty($producto_encontrado['imagen_url'])): ?>
                        <img src="<?php echo BASE_URL . '/' . $producto_encontrado['imagen_url']; ?>" 
                             alt="<?php echo h($producto_encontrado['nombre']); ?>"
                             onerror="this.onerror=null; this.src='<?php echo asset('images/no-image.png'); ?>';">
                    <?php else: ?>
                        <img src="<?php echo asset('images/no-image.png'); ?>" alt="Sin imagen">
                    <?php endif; ?>
                </div>
                
                <!-- Información del producto -->
                <div class="producto-info-detalle">
                    <div class="info-item">
                        <span class="info-label">Código de barras</span>
                        <span class="info-valor codigo"><?php echo $producto_encontrado['codigo_barras'] ?: '—'; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">SKU</span>
                        <span class="info-valor"><?php echo h($producto_encontrado['sku']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Nombre del producto</span>
                        <span class="info-valor nombre"><?php echo h($producto_encontrado['nombre']); ?></span>
                    </div>
                    
                    <div class="stock-info-grid">
                        <div class="stock-info-item">
                            <span class="stock-label">Stock actual</span>
                            <span class="stock-valor actual"><?php echo $producto_encontrado['stock_actual']; ?></span>
                        </div>
                        <div class="stock-info-item">
                            <span class="stock-label">Stock mínimo</span>
                            <span class="stock-valor minimo"><?php echo $producto_encontrado['stock_minimo']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="entrada-form">
                <input type="hidden" name="id_producto" value="<?php echo $producto_encontrado['id']; ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            Cantidad a agregar <span class="required">*</span>
                        </label>
                        <input type="number" name="cantidad" min="1" value="1" required
                               class="form-input cantidad-input" autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Motivo de entrada</label>
                        <select name="motivo" class="form-select">
                            <option value="Compra a proveedor">🛒 Compra a proveedor</option>
                            <option value="Devolución">🔄 Devolución</option>
                            <option value="Ajuste de inventario">📊 Ajuste de inventario</option>
                            <option value="Transferencia">📦 Transferencia</option>
                            <option value="Otro">❓ Otro</option>
                        </select>
                    </div>
                </div>
                
                <div class="stock-preview">
                    <p class="preview-label">Stock después de la entrada:</p>
                    <p class="preview-valor">
                        <span class="stock-anterior"><?php echo $producto_encontrado['stock_actual']; ?></span>
                        <span class="flecha">→</span>
                        <span class="stock-nuevo"><?php echo $producto_encontrado['stock_actual'] + 1; ?> unidades</span>
                    </p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-registrar">
                        <i class="fas fa-save"></i>
                        Registrar Entrada
                    </button>
                    <a href="<?php echo url('dashboard/inventario/entrada_rapida.php'); ?>" class="btn-escanear-otro">
                        <i class="fas fa-barcode"></i>
                        Buscar otro
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Accesos rápidos -->
    <div class="accesos-rapidos">
        <h3>Accesos Rápidos</h3>
        <div class="accesos-grid">
            <a href="<?php echo url('dashboard/productos/index.php'); ?>" class="acceso-card">
                <div class="acceso-icono" style="background: var(--primary-alpha);">
                    <i class="fas fa-list" style="color: var(--primary);"></i>
                </div>
                <div class="acceso-info">
                    <h4>Lista de Productos</h4>
                    <p>Ver inventario</p>
                </div>
            </a>
            
            <a href="<?php echo url('dashboard/productos/nuevo.php'); ?>" class="acceso-card">
                <div class="acceso-icono" style="background: var(--success-light);">
                    <i class="fas fa-plus" style="color: var(--success);"></i>
                </div>
                <div class="acceso-info">
                    <h4>Nuevo Producto</h4>
                    <p>Agregar al catálogo</p>
                </div>
            </a>
            
            <a href="<?php echo url('dashboard/ventas/index.php'); ?>" class="acceso-card">
                <div class="acceso-icono" style="background: var(--secondary-alpha);">
                    <i class="fas fa-shopping-cart" style="color: var(--secondary);"></i>
                </div>
                <div class="acceso-info">
                    <h4>Punto de Venta</h4>
                    <p>Ir a ventas</p>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
/* Animación para el producto encontrado */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.producto-encontrado-card {
    animation: fadeInUp 0.3s ease-out;
}

/* Resultados múltiples */
.resultados-multiples {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-md);
}

.resultados-multiples h3 {
    margin-bottom: 1rem;
    color: var(--gray-700);
}

.resultados-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.resultado-item-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.resultado-item-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.resultado-imagen {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-md);
    overflow: hidden;
    flex-shrink: 0;
    background: white;
}

.resultado-imagen img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.resultado-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.resultado-info strong {
    font-size: 0.9rem;
    color: var(--gray-800);
}

.resultado-info small {
    font-size: 0.7rem;
    color: var(--gray-500);
}

.resultado-stock {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--primary);
    margin-top: 0.25rem;
}

/* Efecto de pulso para el input */
@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 var(--primary-alpha); }
    50% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
}

.escaner-input:focus {
    animation: pulse 1s infinite;
}

/* Responsive */
@media (max-width: 768px) {
    .resultados-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Auto-enfocar el campo de búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('buscarInput');
    if (input) {
        input.focus();
        
        // Si hay un producto, enfocar el campo de cantidad
        <?php if ($producto_encontrado): ?>
            document.querySelector('input[name="cantidad"]').focus();
        <?php endif; ?>
    }
});

// Efecto visual al buscar
document.getElementById('buscarInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.classList.add('animate-pulse');
        setTimeout(() => this.classList.remove('animate-pulse'), 300);
    }
});

// Actualizar vista previa de stock
document.querySelector('input[name="cantidad"]')?.addEventListener('input', function() {
    const cantidad = parseInt(this.value) || 0;
    const stockActual = <?php echo $producto_encontrado ? $producto_encontrado['stock_actual'] : 0; ?>;
    const stockNuevo = stockActual + cantidad;
    
    const previewElement = document.querySelector('.stock-nuevo');
    if (previewElement) {
        previewElement.textContent = stockNuevo + ' unidades';
        
        if (stockNuevo > stockActual) {
            previewElement.style.color = 'var(--success)';
        }
    }
});
</script>

<?php include '../footer.php'; ?>