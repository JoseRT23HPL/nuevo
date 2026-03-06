<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - <?php echo $venta['folio']; ?></title>
    <style>

    *{
    margin:0;
    padding:0;
    box-sizing:border-box;
    }

    body{
    font-family: monospace;
    width:72mm;
    margin:0;
    padding:5px;
    background:white;
    }

    .ticket{
    page-break-inside: avoid;
    }

    .center{
    text-align:center;
    }

    .line{
    border-top:1px dashed black;
    margin:6px 0;
    }

    .small{
    font-size:11px;
    }

    table{
    width:100%;
    font-size:12px;
    border-collapse:collapse;
    }

    th{
    text-align:left;
    padding-bottom:3px;
    border-bottom:1px solid black;
    }

    td{
    padding:3px 0;
    }

    td:last-child{
    text-align:right;
    }

    .total{
    font-weight:bold;
    font-size:14px;
    }

    .footer{
    text-align:center;
    margin-top:10px;
    font-size:12px;
    }

    .volver-btn{
    margin-top:20px;
    padding:10px;
    width:100%;
    background:#2563eb;
    color:white;
    border:none;
    cursor:pointer;
    }

    @media print {

    @page{
        size: 80mm auto;
        margin: 0;
    }

    html, body{
        width: 80mm;
        margin: 0;
        padding: 0;
    }

    .ticket{
        width: 72mm;
    }

    .volver-btn{
        display:none;
    }

}

    </style>
</head>
<body>
    <div class="ticket">

    <div class="center">
    <h2>Mi Empresa</h2>
    <div class="small">Todo para tu ferretería</div>
    <div class="small">
    RFC: ABC123456XYZ<br>
    Tel: 55-1234-5678<br>
    Av. Principal #123
    </div>
    </div>

    <div class="line"></div>

    <div class="center">
    <strong>FOLIO</strong><br>
    <strong><?php echo $venta['folio']; ?></strong>
    </div>

    <div class="line"></div>

    <div class="small">
    Fecha: <?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?><br>
    Hora: <?php echo date('H:i', strtotime($venta['fecha_venta'])); ?><br>
    Cajero: <?php echo $venta['vendedor'] ?: 'Admin'; ?>
    </div>

    <div class="line"></div>

    <table>

    <tr>
    <th>Producto</th>
    <th>Cant</th>
    <th>Total</th>
    </tr>

    <?php foreach ($items as $item): ?>

    <tr>
    <td><?php echo $item['producto_nombre']; ?></td>
    <td><?php echo $item['cantidad']; ?></td>
    <td>$<?php echo number_format($item['subtotal'],2); ?></td>
    </tr>

    <?php endforeach; ?>

    </table>

    <div class="line"></div>

    <table>

    <tr>
    <td>Subtotal</td>
    <td>$<?php echo number_format($venta['subtotal'],2); ?></td>
    </tr>

    <tr>
    <td>IVA 16%</td>
    <td>$<?php echo number_format($venta['iva'],2); ?></td>
    </tr>

    <tr class="total">
    <td>TOTAL</td>
    <td>$<?php echo number_format($venta['total'],2); ?></td>
    </tr>

    </table>

    <div class="line"></div>

    <div class="small">
    Método de pago: <?php echo ucfirst($venta['metodo_pago']); ?>
    </div>

    <div class="line"></div>

    <div class="footer">

    <strong>¡GRACIAS POR SU COMPRA!</strong><br>
    Vuelva pronto

    <br><br>

    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo $venta['folio']; ?>">

    </div>

    </div>

    <!-- Botón para volver al historial (solo visible en pantalla) -->
    <button class="volver-btn" onclick="window.location.href='<?php echo url('dashboard/ventas/index.php'); ?>'">
        ← Volver al historial
    </button>

    <script>

    window.onload = function(){

    setTimeout(function(){

    window.print();

    },300);

    }

    window.onafterprint = function(){

    window.location.href = "<?php echo url('dashboard/ventas/index.php'); ?>";

    }

    </script>
</body>
</html>
<?php include '../footer.php'; ?>