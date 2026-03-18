<?php
// file: /dashboard/reportes/imprimir_corte.php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener ID del corte
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID de corte no válido");
}

// ===== 1. OBTENER DATOS DEL CORTE =====
$stmt = $conn->prepare("
    SELECT 
        c.*,
        u.username,
        u.nombre as nombre_completo
    FROM cortes_caja c
    JOIN usuarios u ON c.id_usuario = u.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$corte = $result->fetch_assoc();

if (!$corte) {
    die("Corte no encontrado");
}

// ===== 2. OBTENER VENTAS DEL CORTE =====
$ventas_stmt = $conn->prepare("
    SELECT 
        v.folio,
        DATE_FORMAT(v.fecha_venta, '%H:%i') as hora,
        v.total,
        v.metodo_pago,
        c.nombre as cliente
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    WHERE v.fecha_venta BETWEEN ? AND ?
    ORDER BY v.fecha_venta ASC
");
$ventas_stmt->bind_param("ss", $corte['fecha_apertura'], $corte['fecha_cierre']);
$ventas_stmt->execute();
$ventas = $ventas_stmt->get_result();

// ===== 3. CALCULAR TOTALES =====
$total_efectivo = 0;
$total_transferencia = 0;
$total_ventas = 0;

while ($v = $ventas->fetch_assoc()) {
    if ($v['metodo_pago'] == 'efectivo') {
        $total_efectivo += $v['total'];
    } else {
        $total_transferencia += $v['total'];
    }
    $total_ventas += $v['total'];
}

// Reiniciar puntero de ventas
$ventas->data_seek(0);

// Calcular diferencia
$esperado = ($corte['monto_inicial'] ?? 0) + $total_efectivo;
$diferencia = ($corte['monto_final'] ?? 0) - $esperado;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corte de Caja #<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', monospace;
        }
        
        body {
            background: white;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 18px;
            color: #333;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ccc;
            background: #f9f9f9;
        }
        
        .info-item {
            font-size: 14px;
        }
        
        .info-label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        
        .resumen-corte {
            margin-bottom: 30px;
        }
        
        .resumen-titulo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
        }
        
        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .resumen-card {
            border: 1px solid #ccc;
            padding: 15px;
        }
        
        .resumen-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .resumen-row:last-child {
            border-bottom: none;
        }
        
        .diferencia {
            font-size: 16px;
            font-weight: bold;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
        }
        
        .diferencia.positiva {
            background: #d1fae5;
            color: #059669;
        }
        
        .diferencia.negativa {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .diferencia.cero {
            background: #f3f4f6;
            color: #374151;
        }
        
        .ventas-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        
        .ventas-tabla th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            border: 1px solid #ccc;
        }
        
        .ventas-tabla td {
            padding: 6px 8px;
            border: 1px solid #ccc;
        }
        
        .ventas-tabla .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 20px;
        }
        
        .firma {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .firma-linea {
            width: 200px;
            text-align: center;
        }
        
        .firma-linea .linea {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
            
            .header {
                margin-top: 0;
            }
        }
        
        .btn-imprimir {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .btn-imprimir:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" class="btn-imprimir">
            <i class="fas fa-print"></i> Imprimir / Guardar PDF
        </button>
        <button onclick="window.close()" class="btn-imprimir" style="background: #6b7280;">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>

    <div class="header">
        <h1><?php echo APP_NAME; ?></h1>
        <h2>CORTE DE CAJA #<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></h2>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Usuario:</span>
            <?php echo h($corte['nombre_completo'] ?: $corte['username']); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Apertura:</span>
            <?php echo date('d/m/Y H:i', strtotime($corte['fecha_apertura'])); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Cierre:</span>
            <?php echo date('d/m/Y H:i', strtotime($corte['fecha_cierre'])); ?>
        </div>
    </div>

    <div class="resumen-corte">
        <div class="resumen-titulo">RESUMEN DEL CORTE</div>
        
        <div class="resumen-grid">
            <div class="resumen-card">
                <div class="resumen-row">
                    <span>Monto Inicial:</span>
                    <span>$<?php echo number_format($corte['monto_inicial'] ?? 0, 2); ?></span>
                </div>
                <div class="resumen-row">
                    <span>Ventas Efectivo:</span>
                    <span>$<?php echo number_format($total_efectivo, 2); ?></span>
                </div>
                <div class="resumen-row" style="font-weight: bold;">
                    <span>Esperado en Caja:</span>
                    <span>$<?php echo number_format($esperado, 2); ?></span>
                </div>
                <div class="resumen-row" style="border-bottom: none;">
                    <span>Monto Final Contado:</span>
                    <span style="font-weight: bold; color: #2563eb;">$<?php echo number_format($corte['monto_final'] ?? 0, 2); ?></span>
                </div>
                
                <div class="diferencia <?php 
                    echo $diferencia > 0 ? 'positiva' : ($diferencia < 0 ? 'negativa' : 'cero'); 
                ?>">
                    <strong>Diferencia: 
                        <?php if ($diferencia > 0): ?>
                            +$<?php echo number_format($diferencia, 2); ?> (Sobra)
                        <?php elseif ($diferencia < 0): ?>
                            -$<?php echo number_format(abs($diferencia), 2); ?> (Falta)
                        <?php else: ?>
                            $0.00
                        <?php endif; ?>
                    </strong>
                </div>
                
                <?php if ($corte['diferencia_justificada']): ?>
                <div style="margin-top: 10px; padding: 5px; background: #f3e8ff; color: #8b5cf6; text-align: center;">
                    <i>✓ Diferencia Justificada</i>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="resumen-card">
                <div class="resumen-row">
                    <span>Ventas Totales:</span>
                    <span>$<?php echo number_format($total_ventas, 2); ?></span>
                </div>
                <div class="resumen-row">
                    <span>Total Efectivo:</span>
                    <span>$<?php echo number_format($total_efectivo, 2); ?></span>
                </div>
                <div class="resumen-row">
                    <span>Total Transferencia:</span>
                    <span>$<?php echo number_format($total_transferencia, 2); ?></span>
                </div>
                <div class="resumen-row" style="border-bottom: none;">
                    <span>Número de Ventas:</span>
                    <span><?php echo $ventas->num_rows; ?></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($corte['observaciones']) || !empty($corte['motivo_diferencia'])): ?>
        <div style="margin-top: 20px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9;">
            <strong>Observaciones:</strong><br>
            <?php if (!empty($corte['observaciones'])): ?>
                <p><?php echo nl2br(h($corte['observaciones'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($corte['motivo_diferencia'])): ?>
                <p><strong>Motivo de diferencia:</strong> <?php echo h($corte['motivo_diferencia']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="resumen-titulo" style="margin-top: 30px;">DETALLE DE VENTAS</div>
    
    <table class="ventas-tabla">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Hora</th>
                <th>Cliente</th>
                <th>Método</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ventas->num_rows > 0): ?>
                <?php while($v = $ventas->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $v['folio']; ?></td>
                    <td><?php echo $v['hora']; ?></td>
                    <td><?php echo $v['cliente'] ?: 'General'; ?></td>
                    <td><?php echo ucfirst($v['metodo_pago']); ?></td>
                    <td class="text-right">$<?php echo number_format($v['total'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        No hay ventas en este período
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f3f4f6;">
                <td colspan="4" class="text-right">TOTAL VENTAS:</td>
                <td class="text-right">$<?php echo number_format($total_ventas, 2); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="firma">
        <div class="firma-linea">
            <div class="linea">Entregó</div>
        </div>
        <div class="firma-linea">
            <div class="linea">Recibió</div>
        </div>
    </div>

    <div class="footer">
        <p>Este documento es un comprobante oficial de corte de caja</p>
        <p>Generado el <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
</body>
</html>