<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Inicializar carrito en sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Inicializar cliente seleccionado
if (!isset($_SESSION['cliente_seleccionado'])) {
    $_SESSION['cliente_seleccionado'] = null;
}

// Obtener el último producto agregado (para mostrar en el DIV superior)
$ultimo_producto = null;
if (!empty($_SESSION['carrito'])) {
    $indices = array_keys($_SESSION['carrito']);
    $ultimo_indice = end($indices);
    $ultimo_producto = $_SESSION['carrito'][$ultimo_indice];
}

// Acciones del carrito
$action = $_GET['action'] ?? '';
$mensaje = '';
$error = '';

// SELECCIONAR CLIENTE
if (isset($_GET['cliente'])) {
    $cliente_id = (int)$_GET['cliente'];
    if ($cliente_id > 0) {
        $stmt = $conn->prepare("SELECT id, nombre, apellidos, email, telefono, documento FROM clientes WHERE id = ? AND activo = 1");
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        if ($cliente) {
            $_SESSION['cliente_seleccionado'] = $cliente;
            $mensaje = "Cliente seleccionado: " . $cliente['nombre'] . ' ' . ($cliente['apellidos'] ?? '');
        }
    }
}

// QUITAR CLIENTE
if (isset($_GET['quitar_cliente'])) {
    $_SESSION['cliente_seleccionado'] = null;
    $mensaje = "Cliente removido";
}

// BÚSQUEDA DE CLIENTES (AJAX) - SOLO PARA MODAL
if (isset($_GET['buscar_clientes'])) {
    $termino = trim($_GET['buscar_clientes']);
    $termino_like = "%$termino%";
    
    $stmt = $conn->prepare("
        SELECT id, nombre, apellidos, documento, email, telefono
        FROM clientes 
        WHERE activo = 1 
        AND (nombre LIKE ? OR apellidos LIKE ? OR documento LIKE ? OR email LIKE ?)
        LIMIT 10
    ");
    $stmt->bind_param("ssss", $termino_like, $termino_like, $termino_like, $termino_like);
    $stmt->execute();
    $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($resultados);
    exit;
}

// BUSCAR PRODUCTO Y AGREGAR AUTOMÁTICAMENTE
if (isset($_GET['agregar'])) {
    $codigo = trim($_GET['agregar']);
    
    // Buscar producto por código de barras, SKU o ID
    $stmt = $conn->prepare("
        SELECT p.*, c.nombre as categoria, m.nombre as marca 
        FROM productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id
        LEFT JOIN marcas m ON p.id_marca = m.id
        WHERE (p.codigo_barras = ? OR p.sku = ? OR p.id = ?) 
        AND p.activo = 1 AND p.stock_actual > 0
    ");
    $stmt->bind_param("sss", $codigo, $codigo, $codigo);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    
    if ($producto) {
        $encontrado = false;
        
        // Buscar si ya está en el carrito
        foreach ($_SESSION['carrito'] as $indice => $item) {
            if ($item['id'] == $producto['id']) {
                // Incrementar cantidad
                $_SESSION['carrito'][$indice]['cantidad']++;
                $encontrado = true;
                break;
            }
        }
        
        if (!$encontrado) {
            // Agregar nuevo producto
            $_SESSION['carrito'][] = [
                'id' => $producto['id'],
                'sku' => $producto['sku'],
                'codigo' => $producto['codigo_barras'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio_venta'],
                'cantidad' => 1,
                'stock' => $producto['stock_actual'],
                'imagen' => $producto['imagen_url']
            ];
        }
        
        // Redirigimos a la misma página para refrescar todo automáticamente
        header('Location: ' . url('dashboard/ventas/index.php'));
        exit;
        
    } else {
        $error = "Producto no encontrado o sin stock";
    }
}

// QUITAR producto del carrito
if (isset($_GET['quitar'])) {
    $index = (int)$_GET['quitar'];
    if (isset($_SESSION['carrito'][$index])) {
        unset($_SESSION['carrito'][$index]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
        header('Location: ' . url('dashboard/ventas/index.php'));
        exit;
    }
}

// VACIAR carrito
if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header('Location: ' . url('dashboard/ventas/index.php'));
    exit;
}

// ACTUALIZAR cantidades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    foreach ($_POST['cantidad'] as $index => $cantidad) {
        if (isset($_SESSION['carrito'][$index])) {
            $cantidad = max(1, min((int)$cantidad, $_SESSION['carrito'][$index]['stock']));
            $_SESSION['carrito'][$index]['cantidad'] = $cantidad;
        }
    }
    header('Location: ' . url('dashboard/ventas/index.php'));
    exit;
}

// RECUPERAR VENTA EN ESPERA
if (isset($_GET['recuperar'])) {
    if (isset($_SESSION['venta_espera']) && !empty($_SESSION['venta_espera'])) {
        $_SESSION['carrito'] = $_SESSION['venta_espera'];
        $_SESSION['venta_espera'] = [];
        $mensaje = "Venta recuperada exitosamente";
    } else {
        $error = "No hay venta en espera";
    }
}

// PONER EN ESPERA
if (isset($_GET['espera'])) {
    if (!empty($_SESSION['carrito'])) {
        $_SESSION['venta_espera'] = $_SESSION['carrito'];
        $_SESSION['carrito'] = [];
        $mensaje = "Venta guardada en espera";
    } else {
        $error = "No hay productos para guardar en espera";
    }
}

// PROCESAR VENTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_venta'])) {
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $monto_recibido = floatval($_POST['monto_recibido'] ?? 0);
    $referencia = trim($_POST['referencia'] ?? '');
    $banco = trim($_POST['banco'] ?? '');
    
    // Calcular totales
    $subtotal = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;
    
    // Validar pago
    $error_pago = '';
    if ($metodo_pago === 'efectivo') {
        if ($monto_recibido < $total) {
            $error_pago = 'El monto recibido es menor al total';
        }
    } elseif ($metodo_pago === 'transferencia') {
        if (empty($referencia)) {
            $error_pago = 'Ingrese el número de referencia';
        }
    } else {
        $error_pago = 'Método de pago no válido';
    }
    
    if (empty($error_pago)) {
        $conn->begin_transaction();
        
        try {
            $folio = generarFolio();
            $id_cliente = $_SESSION['cliente_seleccionado']['id'] ?? null;
            
            $stmt = $conn->prepare("
                INSERT INTO ventas (folio, id_usuario, id_cliente, subtotal, iva, total, metodo_pago, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'completada')
            ");
            $stmt->bind_param("siiddds", $folio, $_SESSION['user_id'], $id_cliente, $subtotal, $iva, $total, $metodo_pago);
            $stmt->execute();
            $id_venta = $conn->insert_id;
            
            foreach ($_SESSION['carrito'] as $item) {
                $subtotal_item = $item['precio'] * $item['cantidad'];
                
                $stmt = $conn->prepare("
                    INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iiidd", $id_venta, $item['id'], $item['cantidad'], $item['precio'], $subtotal_item);
                $stmt->execute();
                
                $stmt = $conn->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['cantidad'], $item['id']);
                $stmt->execute();
                
                $stmt = $conn->prepare("
                    INSERT INTO movimientos_inventario (id_producto, tipo, cantidad, stock_anterior, stock_nuevo, motivo, id_usuario) 
                    SELECT ?, 'salida', ?, stock_actual + ?, stock_actual, ?, ?
                    FROM productos WHERE id = ?
                ");
                $motivo = "Venta #" . $folio;
                $stmt->bind_param("iiissi", $item['id'], $item['cantidad'], $item['cantidad'], $motivo, $_SESSION['user_id'], $item['id']);
                $stmt->execute();
            }
            
            $conn->commit();
            
            $_SESSION['carrito'] = [];
            $_SESSION['cliente_seleccionado'] = null;
            
            header('Location: ' . url('dashboard/ventas/ticket.php?id=' . $id_venta));
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Error al procesar la venta: ' . $e->getMessage();
        }
    } else {
        $error = $error_pago;
    }
}

// BÚSQUEDA en tiempo real de productos (AJAX) - para búsqueda por nombre
if (isset($_GET['buscar_productos'])) {
    $termino = trim($_GET['buscar_productos']);
    $termino_like = "%$termino%";
    
    $stmt = $conn->prepare("
        SELECT id, sku, codigo_barras, nombre, precio_venta, stock_actual, imagen_url
        FROM productos 
        WHERE activo = 1 AND stock_actual > 0
        AND (nombre LIKE ? OR codigo_barras LIKE ? OR sku LIKE ?)
        LIMIT 10
    ");
    $stmt->bind_param("sss", $termino_like, $termino_like, $termino_like);
    $stmt->execute();
    $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($resultados);
    exit;
}

// Calcular totales del carrito
$subtotal = 0;
$total_items = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
    $total_items += $item['cantidad'];
}
$iva = $subtotal * 0.16;
$total = $subtotal + $iva;

include '../header.php';
?>


<!-- CONTENEDOR PRINCIPAL -->
<div class="pos-container">
    
    <!-- Buscador y botones -->
    <div class="pv-buscador-wrapper">
        <div class="pv-buscador">
            <div class="buscador-container">
                <i class="fas fa-barcode buscador-icon"></i>
                <input type="text" id="buscarProducto" class="buscador-input" placeholder="Código de barras, SKU o nombre..." autofocus>
                <button class="btn-buscar" onclick="buscarProducto()">
                    <i class="fas fa-search"></i>
                    Buscar
                </button>
                <span class="buscador-tecla">F2</span>
            </div>
        </div>
        
        <div class="pv-botones-header">
            <button class="btn-header" onclick="window.location.href='<?php echo url('dashboard/reportes/corte_caja.php'); ?>'">
                <i class="fas fa-scissors"></i>
                Corte
            </button>
            <button class="btn-header" onclick="window.location.href='<?php echo url('dashboard/ventas/historial.php'); ?>'">
                <i class="fas fa-history"></i>
                Historial
            </button>
            <button class="btn-header" onclick="buscarCliente()">
                <i class="fas fa-user"></i>
                Clientes
            </button>
        </div>
    </div>

    <!-- GRID PRINCIPAL -->
    <div class="pos-main-grid">
        
        <!-- COLUMNA IZQUIERDA -->
        <div class="pos-left-col">
            
            <!-- DIV SUPERIOR DEL PRODUCTO -->
            <div class="producto-destacado" id="productoDestacado">
                <div class="producto-destacado-imagen" id="destacadoImagen">
                    <?php if ($ultimo_producto && !empty($ultimo_producto['imagen'])): ?>
                        <img src="<?php echo BASE_URL . '/' . $ultimo_producto['imagen']; ?>" 
                             alt="<?php echo h($ultimo_producto['nombre']); ?>"
                             onerror="this.onerror=null; this.src='<?php echo asset('images/no-image.png'); ?>';">
                    <?php else: ?>
                        <img src="<?php echo asset('images/no-image.png'); ?>" alt="Sin imagen">
                    <?php endif; ?>
                </div>
                <div class="producto-destacado-info" id="destacadoInfo">
                    <?php if ($ultimo_producto): ?>
                        <h2 id="destacadoNombre"><?php echo h($ultimo_producto['nombre']); ?></h2>
                        <div class="producto-destacado-detalles">
                            <span class="destacado-sku"><i class="fas fa-barcode"></i> SKU: <?php echo h($ultimo_producto['sku']); ?></span>
                            <span class="destacado-codigo"><i class="fas fa-qrcode"></i> Código: <?php echo $ultimo_producto['codigo'] ?: '—'; ?></span>
                            <span class="destacado-precio"><i class="fas fa-tag"></i> Precio: $<?php echo number_format($ultimo_producto['precio'], 2); ?></span>
                            <span class="destacado-stock"><i class="fas fa-cubes"></i> Stock: <?php echo $ultimo_producto['stock']; ?></span>
                        </div>
                    <?php else: ?>
                        <h2>Esperando producto...</h2>
                        <div class="producto-destacado-detalles">
                            <span>Escanea un código de barras o busca un producto</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Zona de alertas -->
            <div id="alertaContainer" class="alerta-container"></div>
            
            <!-- Panel de cliente seleccionado -->
            <?php if (isset($_SESSION['cliente_seleccionado'])): ?>
            <div class="cliente-seleccionado">
                <div class="cliente-info">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <strong><?php echo h($_SESSION['cliente_seleccionado']['nombre'] . ' ' . ($_SESSION['cliente_seleccionado']['apellidos'] ?? '')); ?></strong>
                        <small><?php echo $_SESSION['cliente_seleccionado']['documento'] ?? ''; ?></small>
                    </div>
                </div>
                <a href="?quitar_cliente=1" class="btn-quitar-cliente" title="Quitar cliente">
                    <i class="fas fa-times"></i>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- TABLA DE PRODUCTOS CON SCROLL -->
            <div class="pos-tabla-contenedor">
                <form method="POST" id="carritoForm">
                    <table class="pv-tabla" id="tablaProductos">
                        <thead>
                            <tr>
                                <th>CÓDIGO</th>
                                <th>DESCRIPCIÓN</th>
                                <th>PRECIO</th>
                                <th>CANTIDAD</th>
                                <th>IMPORTE</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="carritoBody">
                            <?php if (empty($_SESSION['carrito'])): ?>
                                <tr>
                                    <td colspan="6" class="empty-state-row">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <p>El carrito está vacío</p>
                                        <small>Escanea un producto para comenzar</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($_SESSION['carrito'] as $index => $item): ?>
                                <tr data-index="<?php echo $index; ?>">
                                    <td class="col-codigo"><?php echo $item['codigo'] ?: '—'; ?></td>
                                    <td class="col-descripcion">
                                        <?php echo h($item['nombre']); ?>
                                        <small class="producto-sku">SKU: <?php echo h($item['sku']); ?></small>
                                    </td>
                                    <td class="col-precio">$<?php echo number_format($item['precio'], 2); ?></td>
                                    <td class="col-cantidad">
                                        <div class="producto-cantidad">
                                            <button type="button" class="cantidad-btn" onclick="cambiarCantidad(<?php echo $index; ?>, -1)">−</button>
                                            <input type="number" name="cantidad[<?php echo $index; ?>]" 
                                                   value="<?php echo $item['cantidad']; ?>" 
                                                   min="1" max="<?php echo $item['stock']; ?>" 
                                                   class="cantidad-input" 
                                                   data-index="<?php echo $index; ?>"
                                                   data-precio="<?php echo $item['precio']; ?>"
                                                   readonly>
                                            <button type="button" class="cantidad-btn" onclick="cambiarCantidad(<?php echo $index; ?>, 1)">+</button>
                                        </div>
                                    </td>
                                    <td class="col-importe" data-index="<?php echo $index; ?>">
                                        $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                    </td>
                                    <td class="col-acciones">
                                        <a href="?quitar=<?php echo $index; ?>" class="btn-eliminar" onclick="return confirm('¿Quitar producto?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        
        <!-- COLUMNA DERECHA -->
        <div class="pos-right-col">
            
            <!-- TARJETA DE RESUMEN DE VENTA - CON BOTONES ACTUALIZADOS -->
            <div class="resumen-venta-card">
                <div class="resumen-header">
                    <i class="fas fa-receipt"></i>
                    <h3>Resumen de venta</h3>
                </div>
                
                <div class="resumen-items">
                    <i class="fas fa-boxes"></i>
                    <span id="totalItemsDisplay"><?php echo $total_items; ?> artículo(s)</span>
                </div>
                
                <div class="resumen-detalles">
                    <div class="resumen-fila">
                        <span>Subtotal</span>
                        <span class="resumen-monto" id="subtotalDisplay">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="resumen-fila">
                        <span>IVA (16%)</span>
                        <span class="resumen-monto" id="ivaDisplay">$<?php echo number_format($iva, 2); ?></span>
                    </div>
                    <div class="resumen-fila total">
                        <span>TOTAL</span>
                        <span class="resumen-total" id="totalDisplay">$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <!-- BOTONES ACTUALIZADOS: Esperar y Recuperar -->
                <div class="resumen-acciones">
                    <button class="btn-accion-resumen espera" onclick="ponerEnEspera()">
                        <i class="fas fa-pause"></i>
                        Esperar
                    </button>
                    <button class="btn-accion-resumen recuperar" onclick="recuperarVenta()">
                        <i class="fas fa-play"></i>
                        Recuperar
                    </button>
                </div>
            </div>
            
            <!-- BARRA INFERIOR SIMPLIFICADA - SOLO CAJA Y TOTAL -->
            <div class="pos-barra-inferior">
                <div class="barra-info">
                    <div class="info-item">
                        <i class="fas fa-cube"></i>
                        CAJA-01
                    </div>
                </div>
                
                <div class="total">
                    <span class="total-label">Total</span>
                    <span class="total-monto" id="barraTotal">$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <!-- BOTONES DE ACCIÓN SIMPLIFICADOS - SOLO CANCELAR Y COBRAR -->
            <div class="pos-botones-accion">
                <button class="btn-accion-inferior cancelar" onclick="cancelarVenta()">
                    <i class="fas fa-times"></i>
                    Cancelar (F4)
                </button>
                <button class="btn-accion-inferior cobrar" onclick="procesarVenta()">
                    <i class="fas fa-cash-register"></i>
                    Cobrar (ESC)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alerta flotante -->
<div id="alertaFlotante" class="alerta-flotante"></div>

<!-- Modales (se mantienen igual) -->
<div id="clienteModal" class="modal-pago hidden">
    <div class="modal-pago-content">
        <div class="modal-pago-header">
            <h2>
                <i class="fas fa-users"></i>
                Seleccionar Cliente
            </h2>
            <button class="modal-pago-close" onclick="cerrarModalCliente()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-pago-body">
            <div class="buscador-cliente">
                <input type="text" id="buscarClienteInput" class="form-input" placeholder="Buscar por nombre, documento o email...">
            </div>
            <div id="resultadosClientesModal" class="resultados-clientes"></div>
        </div>
        
        <div class="modal-pago-footer">
            <button class="btn-cancelar" onclick="cerrarModalCliente()">Cerrar</button>
            <a href="<?php echo url('dashboard/clientes/nuevo.php'); ?>" class="btn-primary">Nuevo cliente</a>
        </div>
    </div>
</div>

<div id="pagoModal" class="modal-pago hidden">
    <div class="modal-pago-content">
        <div class="modal-pago-header">
            <h2>
                <i class="fas fa-cash-register"></i>
                Procesar Pago
            </h2>
            <button class="modal-pago-close" onclick="cerrarModalPago()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" id="pagoForm">
            <div class="modal-pago-body">
                <div class="total-pagar">
                    <span class="total-pagar-label">Total a pagar</span>
                    <span class="total-pagar-monto" id="totalPagarModal">$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="metodos-pago">
                    <button type="button" class="metodo-btn active" onclick="seleccionarMetodo('efectivo')" id="btnEfectivo">
                        <i class="fas fa-money-bill-wave"></i>
                        Efectivo
                    </button>
                    <button type="button" class="metodo-btn" onclick="seleccionarMetodo('transferencia')" id="btnTransferencia">
                        <i class="fas fa-mobile-alt"></i>
                        Transferencia
                    </button>
                </div>
                
                <input type="hidden" name="metodo_pago" id="metodoPago" value="efectivo">
                
                <div id="efectivoFields" class="pago-fields">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-hand-holding-usd"></i>
                            Monto recibido
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="number" name="monto_recibido" id="montoRecibido" class="form-input" 
                                   value="<?php echo $total; ?>" min="<?php echo $total; ?>" step="0.01" oninput="calcularCambio()">
                        </div>
                    </div>
                    
                    <div class="cambio-box">
                        <span class="cambio-label">Cambio a entregar</span>
                        <span class="cambio-monto" id="cambioDisplay">$0.00</span>
                    </div>
                </div>
                
                <div id="transferenciaFields" class="pago-fields hidden">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-hashtag"></i>
                            Número de referencia
                        </label>
                        <input type="text" name="referencia" class="form-input" placeholder="Últimos 5 dígitos" id="referenciaTransferencia" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-building"></i>
                            Banco
                        </label>
                        <select name="banco" class="form-input" id="bancoTransferencia">
                            <option value="">Seleccionar banco</option>
                            <option value="BBVA">BBVA</option>
                            <option value="Banamex">Banamex</option>
                            <option value="Santander">Santander</option>
                            <option value="HSBC">HSBC</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-pago-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModalPago()">
                    Cancelar
                </button>
                <button type="submit" name="procesar_venta" class="btn-cobrar">
                    <i class="fas fa-check"></i>
                    Cobrar
                </button>
            </div>
        </form>
    </div>
</div>


<script>
// Variables globales
let metodoPagoActual = 'efectivo';
let totalVenta = <?php echo $total; ?>;
let timeoutId;
let clienteTimeoutId;

// ===== FUNCIÓN PARA ACTUALIZAR TOTALES =====
function actualizarTotales() {
    let subtotal = 0;
    let totalItems = 0;
    
    document.querySelectorAll('#carritoBody tr').forEach(row => {
        if (!row.querySelector('.empty-state-row')) {
            const importeCell = row.querySelector('.col-importe');
            if (importeCell) {
                const importeText = importeCell.textContent.replace('$', '');
                subtotal += parseFloat(importeText) || 0;
            }
            
            const cantidadInput = row.querySelector('.cantidad-input');
            if (cantidadInput) {
                totalItems += parseInt(cantidadInput.value) || 0;
            }
        }
    });
    
    const iva = subtotal * 0.16;
    const total = subtotal + iva;
    
    // Actualizar displays
    document.getElementById('totalItemsDisplay').textContent = totalItems + ' artículo(s)';
    document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('ivaDisplay').textContent = '$' + iva.toFixed(2);
    document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
    document.getElementById('barraTotal').textContent = '$' + total.toFixed(2);
    
    return { subtotal, iva, total, totalItems };
}

// ===== FUNCIÓN PARA CAMBIAR CANTIDAD =====
function cambiarCantidad(index, cambio) {
    const input = document.querySelector(`input[name="cantidad[${index}]"]`);
    if (!input) return;
    
    let nuevaCantidad = parseInt(input.value) + cambio;
    const max = parseInt(input.max);
    const precio = parseFloat(input.dataset.precio);
    
    if (nuevaCantidad >= 1 && nuevaCantidad <= max) {
        input.value = nuevaCantidad;
        
        // Actualizar importe de la fila
        const importeCell = document.querySelector(`.col-importe[data-index="${index}"]`);
        if (importeCell) {
            const nuevoImporte = precio * nuevaCantidad;
            importeCell.textContent = '$' + nuevoImporte.toFixed(2);
        }
        
        // Actualizar totales
        actualizarTotales();
        
        // Enviar al servidor via AJAX
        const formData = new FormData();
        formData.append('actualizar', true);
        formData.append(`cantidad[${index}]`, nuevaCantidad);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).catch(error => console.error('Error:', error));
    }
}

// ===== FUNCIÓN PARA BUSCAR Y AGREGAR =====
function buscarYAgregar(termino) {
    if (termino.length >= 2) {
        window.location.href = `?agregar=${encodeURIComponent(termino)}`;
    }
}

// ===== FUNCIONES DE ALERTAS =====
function mostrarAlerta(mensaje, tipo) {
    const container = document.getElementById('alertaContainer');
    const alertaHTML = `
        <div class="alerta ${tipo}">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <p>${mensaje}</p>
        </div>
    `;
    
    container.innerHTML = alertaHTML;
    const flotante = document.getElementById('alertaFlotante');
    flotante.innerHTML = alertaHTML;
    
    setTimeout(() => {
        container.innerHTML = '';
        flotante.innerHTML = '';
    }, 3000);
}

// ===== FUNCIONES DE CLIENTES =====
function buscarCliente() {
    document.getElementById('clienteModal').classList.remove('hidden');
    document.getElementById('clienteModal').classList.add('show');
    document.getElementById('buscarClienteInput').focus();
    
    fetch(`?buscar_clientes=`)
        .then(response => response.json())
        .then(data => mostrarClientes(data));
}

function cerrarModalCliente() {
    document.getElementById('clienteModal').classList.remove('show');
    document.getElementById('clienteModal').classList.add('hidden');
}

function seleccionarCliente(id) {
    window.location.href = `?cliente=${id}`;
}

function mostrarClientes(clientes) {
    const container = document.getElementById('resultadosClientesModal');
    
    if (clientes.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No se encontraron clientes</p></div>';
        return;
    }
    
    let html = '';
    clientes.forEach(c => {
        html += `
            <div class="cliente-item" onclick="seleccionarCliente(${c.id})">
                <i class="fas fa-user-circle"></i>
                <div>
                    <strong>${c.nombre} ${c.apellidos || ''}</strong>
                    <small>${c.documento || ''} | ${c.email || ''}</small>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ===== EVENTOS DE BÚSQUEDA =====
document.getElementById('buscarProducto')?.addEventListener('input', function() {
    clearTimeout(timeoutId);
    const termino = this.value.trim();
    
    if (termino.length >= 2) {
        timeoutId = setTimeout(() => {
            buscarYAgregar(termino);
        }, 500);
    }
});

document.getElementById('buscarClienteInput')?.addEventListener('input', function() {
    clearTimeout(clienteTimeoutId);
    const termino = this.value.trim();
    
    clienteTimeoutId = setTimeout(() => {
        fetch(`?buscar_clientes=${encodeURIComponent(termino)}`)
            .then(response => response.json())
            .then(data => mostrarClientes(data));
    }, 300);
});

function buscarProducto() {
    const input = document.getElementById('buscarProducto');
    if (input.value.trim()) {
        buscarYAgregar(input.value.trim());
    }
}

// ===== FUNCIONES DE ATAJOS =====
function cancelarVenta() {
    if (confirm('¿Cancelar la venta actual?')) {
        window.location.href = '?vaciar=1';
    }
}

function ponerEnEspera() {
    <?php if (empty($_SESSION['carrito'])): ?>
        mostrarAlerta('❌ No hay productos para guardar en espera', 'error');
        return;
    <?php endif; ?>
    window.location.href = '?espera=1';
}

function recuperarVenta() {
    window.location.href = '?recuperar=1';
}

// ===== FUNCIONES DEL MODAL DE PAGO =====
function procesarVenta() {
    <?php if (empty($_SESSION['carrito'])): ?>
        mostrarAlerta('❌ El carrito está vacío', 'error');
        return;
    <?php endif; ?>
    
    const totalElement = document.getElementById('barraTotal');
    if (totalElement) {
        totalVenta = parseFloat(totalElement.textContent.replace('$', '')) || <?php echo $total; ?>;
    }
    
    document.getElementById('totalPagarModal').textContent = '$' + totalVenta.toFixed(2);
    document.getElementById('montoRecibido').value = totalVenta.toFixed(2);
    document.getElementById('montoRecibido').min = totalVenta;
    document.getElementById('cambioDisplay').textContent = '$0.00';
    document.getElementById('referenciaTransferencia').value = '';
    document.getElementById('bancoTransferencia').value = '';
    
    seleccionarMetodo('efectivo');
    
    document.getElementById('pagoModal').classList.remove('hidden');
    document.getElementById('pagoModal').classList.add('show');
    
    setTimeout(() => {
        if (metodoPagoActual === 'efectivo') {
            document.getElementById('montoRecibido').focus();
        } else {
            document.getElementById('referenciaTransferencia').focus();
        }
    }, 300);
}

function cerrarModalPago() {
    document.getElementById('pagoModal').classList.remove('show');
    document.getElementById('pagoModal').classList.add('hidden');
}

function seleccionarMetodo(metodo) {
    metodoPagoActual = metodo;
    document.getElementById('metodoPago').value = metodo;
    
    document.querySelectorAll('.metodo-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    if (metodo === 'efectivo') {
        document.getElementById('btnEfectivo').classList.add('active');
        document.getElementById('efectivoFields').classList.remove('hidden');
        document.getElementById('transferenciaFields').classList.add('hidden');
        setTimeout(() => document.getElementById('montoRecibido').focus(), 100);
    } else {
        document.getElementById('btnTransferencia').classList.add('active');
        document.getElementById('transferenciaFields').classList.remove('hidden');
        document.getElementById('efectivoFields').classList.add('hidden');
        setTimeout(() => document.getElementById('referenciaTransferencia').focus(), 100);
    }
}

function calcularCambio() {
    const montoRecibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
    const cambio = montoRecibido - totalVenta;
    
    const cambioDisplay = document.getElementById('cambioDisplay');
    if (cambio >= 0) {
        cambioDisplay.textContent = '$' + cambio.toFixed(2);
        cambioDisplay.style.color = 'var(--success)';
    } else {
        cambioDisplay.textContent = '$' + Math.abs(cambio).toFixed(2);
        cambioDisplay.style.color = 'var(--danger)';
    }
}

// ===== ATAJOS DE TECLADO =====
document.addEventListener('keydown', function(e) {
    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('buscarProducto').focus();
    }
    
    if (e.key === 'F3') {
        e.preventDefault();
        buscarCliente();
    }
    
    if (e.key === 'F4') {
        e.preventDefault();
        cancelarVenta();
    }
    
    if (e.key === 'Escape') {
        e.preventDefault();
        procesarVenta();
    }
});

// ===== CERRAR MODALES AL HACER CLIC FUERA =====
window.onclick = function(event) {
    const modalPago = document.getElementById('pagoModal');
    const modalCliente = document.getElementById('clienteModal');
    
    if (event.target == modalPago) {
        cerrarModalPago();
    }
    
    if (event.target == modalCliente) {
        cerrarModalCliente();
    }
};

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar alertas de PHP
    <?php if ($error): ?>
        mostrarAlerta('<?php echo addslashes($error); ?>', 'error');
    <?php endif; ?>
    <?php if ($mensaje): ?>
        mostrarAlerta('<?php echo addslashes($mensaje); ?>', 'success');
    <?php endif; ?>
    
    // Inicializar modales
    const modals = document.querySelectorAll('.modal-pago');
    modals.forEach(modal => {
        modal.classList.add('hidden');
        modal.classList.remove('show');
    });
    
    // Actualizar totales iniciales
    actualizarTotales();
});
</script>

<?php include '../footer.php'; ?>