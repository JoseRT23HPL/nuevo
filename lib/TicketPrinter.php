<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Mostrar mensajes de sesión
if (isset($_SESSION['error_impresion'])) {
    $error_impresion = $_SESSION['error_impresion'];
    unset($_SESSION['error_impresion']);
}

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

$id_venta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_venta <= 0) {
    header('Location: ' . url('dashboard/ventas/index.php'));
    exit;
}

// Obtener datos de la venta
$stmt = $conn->prepare("
    SELECT v.*, u.nombre as vendedor, u.username,
           c.nombre as cliente_nombre, c.apellidos as cliente_apellidos,
           c.documento as cliente_documento
    FROM ventas v
    JOIN usuarios u ON v.id_usuario = u.id
    LEFT JOIN clientes c ON v.id_cliente = c.id
    WHERE v.id = ?
");
$stmt->bind_param("i", $id_venta);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) {
    header('Location: ' . url('dashboard/ventas/index.php'));
    exit;
}

// Obtener detalles de la venta
$stmt = $conn->prepare("
    SELECT dv.*, p.nombre as producto_nombre, p.sku
    FROM detalle_ventas dv
    JOIN productos p ON dv.id_producto = p.id
    WHERE dv.id_venta = ?
    ORDER BY dv.id ASC
");
$stmt->bind_param("i", $id_venta);
$stmt->execute();
$detalles = $stmt->get_result();

$items = [];
while ($row = $detalles->fetch_assoc()) {
    $items[] = $row;
}

$total_items = array_sum(array_column($items, 'cantidad'));

// Datos de la empresa (puedes ponerlos en config.php después)
$empresa = [
    'nombre' => APP_NAME,
    'slogan' => 'Todo para tu ferretería',
    'telefono' => '55-1234-5678',
    'direccion' => 'Av. Principal #123',
    'rfc' => 'ABC123456XYZ'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - <?php echo $venta['folio']; ?></title>
    <style>
        /* ===== RESET ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ===== ESTILOS GENERALES ===== */
        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 2mm;
            font-size: 12px;
            line-height: 1.3;
        }

        .ticket {
            width: 100%;
        }

        /* ===== UTILIDADES ===== */
        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* ===== LÍNEAS SEPARADORAS ===== */
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .line-double {
            border-top: 2px solid #000;
            margin: 5px 0;
        }

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header .slogan {
            font-size: 10px;
            color: #333;
            margin: 2px 0;
        }

        .header .info {
            font-size: 9px;
            line-height: 1.4;
            color: #555;
        }

        /* ===== FOLIO ===== */
        .folio {
            text-align: center;
            margin: 8px 0;
            padding: 5px 0;
            background: #f5f5f5;
        }

        .folio span {
            font-size: 10px;
            display: block;
            color: #666;
        }

        .folio strong {
            font-size: 16px;
            letter-spacing: 2px;
        }

        /* ===== INFO GRID ===== */
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 11px;
        }

        .info-item {
            flex: 1;
        }

        .info-item .label {
            font-weight: bold;
            font-size: 9px;
            color: #666;
        }

        /* ===== CLIENTE ===== */
        .cliente {
            background: #f9f9f9;
            padding: 5px;
            margin: 5px 0;
            border-left: 3px solid #000;
            font-size: 10px;
        }

        .cliente small {
            font-size: 9px;
            color: #666;
        }

        /* ===== TABLA DE PRODUCTOS ===== */
        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 11px;
        }

        .tabla-productos th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-size: 10px;
        }

        .tabla-productos td {
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        .tabla-productos td:last-child {
            text-align: right;
        }

        .producto-sku {
            font-size: 8px;
            color: #666;
            display: block;
        }

        /* ===== TOTALES ===== */
        .totales {
            margin: 5px 0;
        }

        .total-linea {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 11px;
        }

        .total-grande {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
            margin-top: 3px;
            padding-top: 3px;
        }

        /* ===== MÉTODO DE PAGO ===== */
        .metodo-pago {
            background: #e8f5e9;
            padding: 8px;
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 13px;
            border: 1px dashed #4caf50;
        }

        .metodo-pago span:last-child {
            color: #2e7d32;
        }

        /* ===== QR CODE ===== */
        .qr {
            text-align: center;
            margin: 10px 0;
        }

        .qr img {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            padding: 3px;
        }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #000;
            font-size: 11px;
        }

        .footer .gracias {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 3px;
        }

        /* ===== MENSAJES DE ALERTA ===== */
        .alerta {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .alerta.success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
        }

        .alerta.error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
        }

        /* ===== BOTÓN VOLVER ===== */
        .volver-btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .volver-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #6d28d9);
        }

        /* ===== MEDIA PRINT ===== */
        @media print {
            @page {
                size: 80mm auto;
                margin: 2mm;
            }

            body {
                width: 76mm;
                margin: 0;
                padding: 0;
                background: white;
            }

            .volver-btn,
            .alerta {
                display: none !important;
            }

            .qr img {
                max-width: 80px;
                max-height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Mensajes de alerta (solo en pantalla) -->
    <?php if (isset($mensaje)): ?>
    <div class="alerta success">
        ✅ <?php echo $mensaje; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($error_impresion)): ?>
    <div class="alerta error">
        <strong>⚠️ Error de impresión:</strong> <?php echo $error_impresion; ?><br>
        <small>Puedes imprimir este ticket manualmente con el botón de imprimir del navegador.</small>
    </div>
    <?php endif; ?>

    <div class="ticket">
        <!-- HEADER -->
        <div class="header">
            <h1><?php echo $empresa['nombre']; ?></h1>
            <div class="slogan"><?php echo $empresa['slogan']; ?></div>
            <div class="info">
                RFC: <?php echo $empresa['rfc']; ?><br>
                Tel: <?php echo $empresa['telefono']; ?><br>
                <?php echo $empresa['direccion']; ?>
            </div>
        </div>

        <div class="line"></div>

        <!-- FOLIO -->
        <div class="folio">
            <span>FOLIO DE VENTA</span>
            <strong><?php echo $venta['folio']; ?></strong>
        </div>

        <div class="line"></div>

        <!-- FECHA Y CAJERO -->
        <div class="info-grid">
            <div class="info-item">
                <div class="label">FECHA</div>
                <div><?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?></div>
            </div>
            <div class="info-item">
                <div class="label">HORA</div>
                <div><?php echo date('H:i', strtotime($venta['fecha_venta'])); ?></div>
            </div>
            <div class="info-item">
                <div class="label">CAJERO</div>
                <div><?php echo $venta['vendedor'] ?: 'Admin'; ?></div>
            </div>
        </div>

        <!-- CLIENTE (si existe) -->
        <?php if (!empty($venta['cliente_nombre'])): ?>
        <div class="cliente">
            <strong>CLIENTE:</strong> <?php echo $venta['cliente_nombre'] . ' ' . ($venta['cliente_apellidos'] ?? ''); ?>
            <?php if (!empty($venta['cliente_documento'])): ?>
                <br><small>Documento: <?php echo $venta['cliente_documento']; ?></small>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="line"></div>

        <!-- TABLA DE PRODUCTOS -->
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th>PRODUCTO</th>
                    <th>CANT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php echo $item['producto_nombre']; ?>
                        <span class="producto-sku">SKU: <?php echo $item['sku']; ?></span>
                    </td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="line"></div>

        <!-- TOTALES -->
        <div class="totales">
            <div class="total-linea">
                <span>SUBTOTAL:</span>
                <span>$<?php echo number_format($venta['subtotal'], 2); ?></span>
            </div>
            <div class="total-linea">
                <span>IVA 16%:</span>
                <span>$<?php echo number_format($venta['iva'], 2); ?></span>
            </div>
            <div class="total-linea total-grande">
                <span>TOTAL:</span>
                <span>$<?php echo number_format($venta['total'], 2); ?></span>
            </div>
        </div>

        <!-- MÉTODO DE PAGO -->
        <div class="metodo-pago">
            <span><?php echo ucfirst($venta['metodo_pago']); ?></span>
            <span>$<?php echo number_format($venta['total'], 2); ?></span>
        </div>

        <!-- CÓDIGO QR -->
        <div class="qr">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo urlencode($venta['folio']); ?>" 
                 alt="QR Folio">
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div class="gracias">¡GRACIAS POR SU COMPRA!</div>
            <div><?php echo $empresa['nombre']; ?></div>
            <div>Vuelva pronto</div>
            <div style="font-size: 8px; margin-top: 3px;"><?php echo date('d/m/Y H:i'); ?></div>
        </div>
    </div>

    <!-- BOTÓN VOLVER (solo en pantalla) -->
    <button class="volver-btn" onclick="window.location.href='<?php echo url('dashboard/ventas/index.php'); ?>'">
        ← Volver al Punto de Venta
    </button>

    <script>
        // Auto-impresión al cargar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };

        // Redirigir después de imprimir
        window.onafterprint = function() {
            window.location.href = "<?php echo url('dashboard/ventas/index.php'); ?>";
        };

        // También redirigir si cancela la impresión (después de 30 seg)
        setTimeout(function() {
            window.location.href = "<?php echo url('dashboard/ventas/index.php'); ?>";
        }, 30000);
    </script>
</body>
</html>
<?php include '../footer.php'; ?>