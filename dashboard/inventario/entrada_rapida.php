<?php
include '../header.php';

// Simulación de producto encontrado por escáner
$producto_encontrado = null;
$error = '';
$success = '';

// Simular búsqueda por código (ejemplo)
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
    
    // Simular búsqueda en base de datos
    $productos_db = [
        '750123456789' => [
            'id' => 1,
            'codigo_barras' => '750123456789',
            'nombre' => 'Martillo de Uña 16oz',
            'stock_actual' => 15,
            'stock_minimo' => 5,
            'imagen' => 'https://via.placeholder.com/200?text=Martillo'
        ],
        '750123456788' => [
            'id' => 2,
            'codigo_barras' => '750123456788',
            'nombre' => 'Taladro Percutor 500W',
            'stock_actual' => 8,
            'stock_minimo' => 3,
            'imagen' => 'https://via.placeholder.com/200?text=Taladro'
        ],
        '750123456787' => [
            'id' => 3,
            'codigo_barras' => '750123456787',
            'nombre' => 'Caja de Tornillos 1/2"',
            'stock_actual' => 25,
            'stock_minimo' => 10,
            'imagen' => 'https://via.placeholder.com/200?text=Tornillos'
        ]
    ];
    
    if (isset($productos_db[$codigo])) {
        $producto_encontrado = $productos_db[$codigo];
    } else {
        $error = "Producto no encontrado con código: $codigo";
    }
}

// Simular procesamiento de entrada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $id_producto = $_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];
    $motivo = $_POST['motivo'];
    
    if ($cantidad > 0) {
        $success = "Entrada registrada: +$cantidad unidades";
        $producto_encontrado = null; // Limpiar para siguiente entrada
    } else {
        $error = 'La cantidad debe ser mayor a cero';
    }
}
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
        <a href="/dashboard/productos/index.php" class="btn-header" style="text-decoration: none;">
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
                <li>1. Escanea el código de barras con la pistola</li>
                <li>2. El producto se cargará automáticamente</li>
                <li>3. Ingresa la cantidad y motivo</li>
                <li>4. Confirma la entrada</li>
            </ul>
        </div>
    </div>
    
    <!-- Buscador por escáner -->
    <div class="escaner-box">
        <div class="escaner-icono">
            <i class="fas fa-barcode"></i>
        </div>
        <h2>Escanear Producto</h2>
        
        <form method="GET" class="escaner-form" autocomplete="off">
            <div class="escaner-input-group">
                <div class="escaner-input-wrapper">
                    <i class="fas fa-camera"></i>
                    <input type="text" name="codigo" id="codigoInput" 
                           value="<?php echo htmlspecialchars($_GET['codigo'] ?? ''); ?>"
                           placeholder="Código de barras" class="escaner-input" autofocus>
                </div>
                <button type="submit" class="btn-escanear">
                    <i class="fas fa-search"></i>
                    Buscar
                </button>
            </div>
            <p class="escaner-hint">
                <i class="fas fa-info-circle"></i>
                Enfoca la pistola al código de barras y escanea
            </p>
        </form>
    </div>
    
    <!-- Mensajes de alerta -->
    <?php if ($error): ?>
        <div class="alerta error">
            <i class="fas fa-exclamation-circle"></i>
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alerta success">
            <i class="fas fa-check-circle"></i>
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Formulario de entrada si hay producto -->
    <?php if ($producto_encontrado): ?>
        <div class="producto-encontrado-card">
            <div class="producto-encontrado-header">
                <h3>Producto Encontrado</h3>
                <span class="badge-activo">ACTIVO</span>
            </div>
            
            <div class="producto-encontrado-body">
                <!-- Imagen del producto -->
                <div class="producto-imagen">
                    <img src="<?php echo $producto_encontrado['imagen']; ?>" 
                         alt="<?php echo $producto_encontrado['nombre']; ?>">
                </div>
                
                <!-- Información del producto -->
                <div class="producto-info-detalle">
                    <div class="info-item">
                        <span class="info-label">Código de barras</span>
                        <span class="info-valor codigo"><?php echo $producto_encontrado['codigo_barras']; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Nombre del producto</span>
                        <span class="info-valor nombre"><?php echo $producto_encontrado['nombre']; ?></span>
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
                    <a href="entrada_rapida.php" class="btn-escanear-otro">
                        <i class="fas fa-barcode"></i>
                        Escanear otro
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Accesos rápidos -->
    <div class="accesos-rapidos">
        <h3>Accesos Rápidos</h3>
        <div class="accesos-grid">
            <a href="/dashboard/productos/index.php" class="acceso-card">
                <div class="acceso-icono" style="background: var(--primary-alpha);">
                    <i class="fas fa-list" style="color: var(--primary);"></i>
                </div>
                <div class="acceso-info">
                    <h4>Lista de Productos</h4>
                    <p>Ver inventario</p>
                </div>
            </a>
            
            <a href="/dashboard/productos/nuevo.php" class="acceso-card">
                <div class="acceso-icono" style="background: var(--success-light);">
                    <i class="fas fa-plus" style="color: var(--success);"></i>
                </div>
                <div class="acceso-info">
                    <h4>Nuevo Producto</h4>
                    <p>Agregar al catálogo</p>
                </div>
            </a>
            
            <a href="/dashboard/ventas/index.php" class="acceso-card">
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

/* Efecto de pulso para el input al escanear */
@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 var(--primary-alpha); }
    50% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
}

.escaner-input:focus {
    animation: pulse 1s infinite;
}
</style>

<script>
// Auto-enfocar el campo de escaneo
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('codigoInput');
    if (input) {
        input.focus();
        
        // Si hay un producto, enfocar el campo de cantidad
        <?php if ($producto_encontrado): ?>
            document.querySelector('input[name="cantidad"]').focus();
        <?php endif; ?>
    }
});

// Efecto visual al escanear
document.getElementById('codigoInput')?.addEventListener('keypress', function(e) {
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