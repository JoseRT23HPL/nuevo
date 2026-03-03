<?php
include '../header.php';

// Datos de ejemplo para el producto
$producto = [
    'id' => 1,
    'sku' => 'TRP-001-24',
    'codigo_barras' => '750123456789',
    'nombre' => 'Martillo de Uña 16oz con Mango de Madera',
    'descripcion' => 'Martillo de uña profesional con cabeza forjada en acero al carbono.',
    'stock_actual' => 45,
    'stock_minimo' => 10,
    'precio_venta' => 185.00
];

// Datos de ejemplo para movimientos
$movimientos = [
    [
        'fecha' => '2024-03-15 14:30:00',
        'tipo' => 'entrada',
        'cantidad' => 50,
        'stock_anterior' => 195,
        'stock_nuevo' => 245,
        'motivo' => 'Compra a proveedor',
        'usuario' => 'Admin User'
    ],
    [
        'fecha' => '2024-03-14 11:20:00',
        'tipo' => 'salida',
        'cantidad' => 2,
        'stock_anterior' => 197,
        'stock_nuevo' => 195,
        'motivo' => 'Venta #V-001234',
        'usuario' => 'María G.'
    ],
    [
        'fecha' => '2024-03-13 09:45:00',
        'tipo' => 'ajuste',
        'cantidad' => 5,
        'stock_anterior' => 192,
        'stock_nuevo' => 197,
        'motivo' => 'Ajuste por inventario',
        'usuario' => 'Carlos R.'
    ]
];
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
        <a href="/dashboard/productos/ver.php?id=<?php echo $producto['id']; ?>" class="btn-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; text-decoration: none;">
            <i class="fas fa-eye"></i>
            Ver producto
        </a>
        <a href="/dashboard/productos/index.php" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<div class="ajustar-stock-container">
    <!-- Tarjeta del producto (SIN IMAGEN) - NOMBRE ARRIBA, CÓDIGOS ABAJO -->
    <div class="producto-info-card">
        <div class="producto-info-content">
            <!-- NOMBRE DEL PRODUCTO - GRANDE Y DESTACADO -->
            <h3 class="producto-nombre"><?php echo $producto['nombre']; ?></h3>
            
            <!-- CÓDIGOS - DEBAJO DEL NOMBRE -->
            <div class="producto-codigos">
                <div class="codigo-item">
                    <span class="codigo-label">SKU</span>
                    <span class="codigo-valor sku"><?php echo $producto['sku']; ?></span>
                </div>
                <div class="codigo-item">
                    <span class="codigo-label">Código de barras</span>
                    <span class="codigo-valor"><?php echo $producto['codigo_barras']; ?></span>
                </div>
            </div>
            
            <!-- STOCK ACTUAL -->
            <div class="stock-actual-display">
                <span class="stock-actual-label">Stock actual:</span>
                <span class="stock-actual-valor <?php 
                    echo $producto['stock_actual'] <= 0 ? 'cero' : 
                        ($producto['stock_actual'] <= $producto['stock_minimo'] ? 'bajo' : 'normal'); 
                ?>">
                    <?php echo $producto['stock_actual']; ?>
                </span>
                <span class="stock-actual-unidad">unidades</span>
            </div>
            
            <!-- STOCK MÍNIMO -->
            <div class="stock-minimo-info">
                Stock mínimo: <strong><?php echo $producto['stock_minimo']; ?></strong> unidades
            </div>
        </div>
    </div>
    
    <!-- Mensajes de alerta (ejemplos) -->
    <!-- 
    <div class="alerta error">
        <i class="fas fa-exclamation-circle"></i>
        <p>La cantidad debe ser mayor a cero</p>
    </div>
    
    <div class="alerta success">
        <i class="fas fa-check-circle"></i>
        <div class="alerta-content">
            <p>Stock actualizado correctamente</p>
            <a href="/dashboard/productos/ajustar_stock.php?id=<?php echo $producto['id']; ?>" class="btn-primary small">
                <i class="fas fa-plus"></i>
                Hacer otro ajuste
            </a>
        </div>
    </div>
    -->
    
    <!-- Formulario de ajuste -->
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
                    <input type="radio" name="tipo" value="entrada" class="tipo-radio" onchange="updateFormByType('entrada', this)">
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
                    <input type="radio" name="tipo" value="salida" class="tipo-radio" onchange="updateFormByType('salida', this)">
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
                    <input type="radio" name="tipo" value="ajuste" class="tipo-radio" onchange="updateFormByType('ajuste', this)">
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
                           min="1" placeholder="Ej: 10">
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
                           placeholder="Ej: 5">
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
                           min="0" value="<?php echo $producto['stock_actual']; ?>">
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
                              placeholder="Ej: Compra a proveedor, venta, ajuste por inventario..."></textarea>
                </div>
            </div>
            
            <!-- Botones -->
            <div id="submitBtn" class="form-actions hidden">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Registrar Movimiento
                </button>
                <a href="/dashboard/productos/ver.php?id=<?php echo $producto['id']; ?>" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
    
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
                                <?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?>
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
                            <td><?php echo $mov['motivo']; ?></td>
                            <td><?php echo $mov['usuario']; ?></td>
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
let currentType = '';

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
    
    if (currentType === 'entrada') {
        cantidad = document.querySelector('input[name="cantidad_entrada"]').value;
        if (!cantidad) {
            e.preventDefault();
            alert('❌ Ingresa una cantidad');
            return;
        }
        if (parseInt(cantidad) <= 0) {
            e.preventDefault();
            alert('❌ La cantidad debe ser mayor a cero');
            return;
        }
    } else if (currentType === 'salida') {
        cantidad = document.querySelector('input[name="cantidad_salida"]').value;
        if (!cantidad) {
            e.preventDefault();
            alert('❌ Ingresa una cantidad');
            return;
        }
        if (parseInt(cantidad) <= 0) {
            e.preventDefault();
            alert('❌ La cantidad debe ser mayor a cero');
            return;
        }
        if (parseInt(cantidad) > <?php echo $producto['stock_actual']; ?>) {
            e.preventDefault();
            alert('❌ No hay suficiente stock disponible');
            return;
        }
    } else if (currentType === 'ajuste') {
        cantidad = document.querySelector('input[name="cantidad_ajuste"]').value;
        if (cantidad === '') {
            e.preventDefault();
            alert('❌ Ingresa el nuevo stock');
            return;
        }
        if (parseInt(cantidad) < 0) {
            e.preventDefault();
            alert('❌ El stock no puede ser negativo');
            return;
        }
    }
    
    const motivo = document.querySelector('textarea[name="motivo"]').value;
    if (!motivo) {
        e.preventDefault();
        alert('❌ Ingresa un motivo para el movimiento');
        return;
    }
    
    // Crear campo oculto con la cantidad
    document.querySelector('input[name="cantidad"]')?.remove();
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'cantidad';
    input.value = cantidad;
    this.appendChild(input);
});

// Si hay un tipo preseleccionado
<?php if (isset($_POST['tipo'])): ?>
    window.onload = function() {
        const tipo = '<?php echo $_POST['tipo']; ?>';
        const radio = document.querySelector(`input[name="tipo"][value="${tipo}"]`);
        if (radio) {
            updateFormByType(tipo, radio);
        }
    }
<?php endif; ?>
</script>

<?php include '../footer.php'; ?>