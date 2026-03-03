<?php
include '../header.php';
?>

<!-- Header del Punto de Venta -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-store"></i>
            <h1>Mi Empresa</h1>
        </div>
        <span class="pv-badge">CAJA-01</span>
    </div>
</div>

<!-- Buscador y botones -->
<div class="pv-buscador-wrapper">
    <div class="pv-buscador">
        <div class="buscador-container">
            <i class="fas fa-barcode buscador-icon"></i>
            <input type="text" class="buscador-input" placeholder="Código de barras o nombre..." autofocus>
            <button class="btn-buscar">
                <i class="fas fa-search"></i>
                Buscar
            </button>
            <span class="buscador-tecla">F2</span>
        </div>
    </div>
    
    <div class="pv-botones-header">
        <button class="btn-header">
            <i class="fas fa-scissors"></i>
            Corte de caja
        </button>
        <button class="btn-header">
            <i class="fas fa-history"></i>
            Historial de ventas
        </button>
    </div>
</div>

<!-- Contenedor con scroll para la tabla -->
<div class="pv-contenedor-scroll">
    <!-- Tabla de Productos -->
    <div class="pv-tabla-container">
        <table class="pv-tabla">
            <thead>
                <tr>
                    <th>CÓD. DE BARRAS</th>
                    <th>DESCRIPCIÓN</th>
                    <th>PRECIO UNITARIO</th>
                    <th>CANTIDAD</th>
                    <th>UNIDAD</th>
                    <th>IMPORTE</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-codigo">750123456789</td>
                    <td class="col-descripcion">GALLETAS 25 GR CHOCOLATE</td>
                    <td class="col-precio">$18.00</td>
                    <td class="col-cantidad">1</td>
                    <td class="col-unidad">PZ</td>
                    <td class="col-importe">$18.00</td>
                    <td class="col-acciones">
                        <button class="btn-eliminar" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="col-codigo">750123456788</td>
                    <td class="col-descripcion">REFRESCO 3L SABOR UVA</td>
                    <td class="col-precio">$25.00</td>
                    <td class="col-cantidad">2</td>
                    <td class="col-unidad">PZ</td>
                    <td class="col-importe">$50.00</td>
                    <td class="col-acciones">
                        <button class="btn-eliminar" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="col-codigo">750123456787</td>
                    <td class="col-descripcion">HUEVO</td>
                    <td class="col-precio">$45.00</td>
                    <td class="col-cantidad">1.5</td>
                    <td class="col-unidad">KG</td>
                    <td class="col-importe">$67.50</td>
                    <td class="col-acciones">
                        <button class="btn-eliminar" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="col-codigo">750123456786</td>
                    <td class="col-descripcion">LECHE 1L ENTERA</td>
                    <td class="col-precio">$22.50</td>
                    <td class="col-cantidad">3</td>
                    <td class="col-unidad">PZ</td>
                    <td class="col-importe">$67.50</td>
                    <td class="col-acciones">
                        <button class="btn-eliminar" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="col-codigo">750123456785</td>
                    <td class="col-descripcion">PAN DE CAJA BLANCO</td>
                    <td class="col-precio">$32.00</td>
                    <td class="col-cantidad">1</td>
                    <td class="col-unidad">PZ</td>
                    <td class="col-importe">$32.00</td>
                    <td class="col-acciones">
                        <button class="btn-eliminar" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Barra Inferior FIJA -->
<div class="pv-barra-inferior">
    <div class="barra-info">
        <div class="info-item">
            <i class="fas fa-cube"></i>
            CAJA-01
        </div>
        <div class="info-item">
            <i class="fas fa-boxes"></i>
            <span>5 Artículos</span>
        </div>
        <div class="info-item">
            <i class="fas fa-user"></i>
            Cliente (F3)
        </div>
    </div>
    
    <div class="total">
        <span class="total-label">Total</span>
        <span class="total-monto">$135.50</span>
    </div>
</div>

<!-- Botones de Acción FIJOS en la parte inferior -->
<div class="pv-botones-accion">
    <button class="btn-accion-inferior">
        <i class="fas fa-times"></i>
        Cancelar (F4)
    </button>
    <button class="btn-accion-inferior">
        <i class="fas fa-pause"></i>
        Poner en espera (F5)
    </button>
    <button class="btn-accion-inferior recuperar">
        <i class="fas fa-play"></i>
        Recuperar
    </button>
    <button class="btn-accion-inferior">
        <i class="fas fa-list"></i>
        Lista de espera (F6)
    </button>
    <button class="btn-accion-inferior cobrar" onclick="procesarVenta()">
        <i class="fas fa-cash-register"></i>
        $ Cobrar (ESC)
    </button>
</div>

<!-- Modal de Pago Mejorado -->
<!-- Modal de Pago Mejorado -->
<div id="pagoModal" class="modal-pago hidden"> <!-- Agregué la clase hidden aquí -->
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
        
        <div class="modal-pago-body">
            <!-- Total a pagar -->
            <div class="total-pagar">
                <span class="total-pagar-label">Total a pagar</span>
                <span class="total-pagar-monto" id="totalPagarModal">$135.50</span>
            </div>
            
            <!-- Métodos de pago (SOLO EFECTIVO Y TRANSFERENCIA) -->
            <div class="metodos-pago">
                <button class="metodo-btn active" onclick="seleccionarMetodo('efectivo')" id="btnEfectivo">
                    <i class="fas fa-money-bill-wave"></i>
                    Efectivo
                </button>
                <button class="metodo-btn" onclick="seleccionarMetodo('transferencia')" id="btnTransferencia">
                    <i class="fas fa-mobile-alt"></i>
                    Transferencia
                </button>
            </div>
            
            <!-- Campos para efectivo -->
            <div id="efectivoFields" class="pago-fields">
                <div class="form-group">
                    <label>
                        <i class="fas fa-hand-holding-usd"></i>
                        Monto recibido
                    </label>
                    <div class="input-prefix">
                        <span>$</span>
                        <input type="number" id="montoRecibido" class="form-input" value="135.50" min="135.50" step="0.01" oninput="calcularCambio()">
                    </div>
                </div>
                
                <div class="cambio-box">
                    <span class="cambio-label">Cambio a entregar</span>
                    <span class="cambio-monto" id="cambioDisplay">$0.00</span>
                </div>
            </div>
            
            <!-- Campos para transferencia -->
            <div id="transferenciaFields" class="pago-fields hidden">
                <div class="form-group">
                    <label>
                        <i class="fas fa-hashtag"></i>
                        Número de referencia
                    </label>
                    <input type="text" class="form-input" placeholder="Ingrese los 5 ultimos digitos" id="referenciaTransferencia">
                </div>
                <div class="form-group">
                    <label>
                        <i class="fas fa-building"></i>
                        Banco
                    </label>
                    <select class="form-input" id="bancoTransferencia">
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
            <button class="btn-cancelar" onclick="cerrarModalPago()">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
            <button class="btn-cobrar" onclick="procesarPago()">
                <i class="fas fa-check"></i>
                Cobrar
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let metodoPagoActual = 'efectivo';
let totalVenta = 135.50;

// Función para eliminar producto
function eliminarProducto(btn) {
    if (confirm('¿Eliminar producto del carrito?')) {
        const row = btn.closest('tr');
        row.remove();
        actualizarTotales();
    }
}

// Función para actualizar totales
function actualizarTotales() {
    const items = document.querySelectorAll('.pv-tabla tbody tr');
    let total = 0;
    let totalItems = 0;
    
    items.forEach(row => {
        const importe = parseFloat(row.querySelector('.col-importe').textContent.replace('$', ''));
        total += importe;
        totalItems++;
    });
    
    document.querySelector('.total-monto').textContent = '$' + total.toFixed(2);
    document.querySelector('.barra-info .info-item:nth-child(2) span').textContent = totalItems + ' Artículos';
}

// Función para abrir el modal
function procesarVenta() {
    // Obtener el total actual del carrito
    const totalElement = document.querySelector('.total-monto');
    if (totalElement) {
        totalVenta = parseFloat(totalElement.textContent.replace('$', '')) || 135.50;
    }
    
    // Actualizar el total en el modal
    document.getElementById('totalPagarModal').textContent = '$' + totalVenta.toFixed(2);
    document.getElementById('montoRecibido').value = totalVenta.toFixed(2);
    document.getElementById('montoRecibido').min = totalVenta;
    
    // Resetear campos
    document.getElementById('montoRecibido').value = totalVenta.toFixed(2);
    document.getElementById('cambioDisplay').textContent = '$0.00';
    document.getElementById('referenciaTransferencia').value = '';
    document.getElementById('bancoTransferencia').value = '';
    
    // Resetear a método efectivo por defecto
    seleccionarMetodo('efectivo');
    
    // Mostrar modal (quitando la clase hidden y agregando show)
    document.getElementById('pagoModal').classList.remove('hidden');
    document.getElementById('pagoModal').classList.add('show');
    
    // Enfocar el campo correspondiente
    setTimeout(() => {
        if (metodoPagoActual === 'efectivo') {
            document.getElementById('montoRecibido').focus();
        } else {
            document.getElementById('referenciaTransferencia').focus();
        }
    }, 300);
}

// Función para cerrar el modal
function cerrarModalPago() {
    document.getElementById('pagoModal').classList.remove('show');
    document.getElementById('pagoModal').classList.add('hidden');
}

// Función para seleccionar método de pago (SOLO EFECTIVO Y TRANSFERENCIA)
function seleccionarMetodo(metodo) {
    metodoPagoActual = metodo;
    
    // Actualizar clases activas
    document.querySelectorAll('.metodo-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    if (metodo === 'efectivo') {
        document.getElementById('btnEfectivo').classList.add('active');
        document.getElementById('efectivoFields').classList.remove('hidden');
        document.getElementById('transferenciaFields').classList.add('hidden');
        setTimeout(() => document.getElementById('montoRecibido').focus(), 100);
    } else { // transferencia
        document.getElementById('btnTransferencia').classList.add('active');
        document.getElementById('transferenciaFields').classList.remove('hidden');
        document.getElementById('efectivoFields').classList.add('hidden');
        setTimeout(() => document.getElementById('referenciaTransferencia').focus(), 100);
    }
}

// Función para calcular cambio (solo para efectivo)
function calcularCambio() {
    const montoRecibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
    const cambio = montoRecibido - totalVenta;
    
    if (cambio >= 0) {
        document.getElementById('cambioDisplay').textContent = '$' + cambio.toFixed(2);
        document.getElementById('cambioDisplay').style.color = 'var(--success)';
    } else {
        document.getElementById('cambioDisplay').textContent = '$' + Math.abs(cambio).toFixed(2);
        document.getElementById('cambioDisplay').style.color = 'var(--danger)';
    }
}

// Función para procesar el pago (SOLO EFECTIVO Y TRANSFERENCIA)
function procesarPago() {
    let validado = true;
    let mensaje = '';
    
    if (metodoPagoActual === 'efectivo') {
        const montoRecibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
        if (montoRecibido < totalVenta) {
            alert('❌ El monto recibido es menor al total a pagar');
            validado = false;
        } else {
            const cambio = montoRecibido - totalVenta;
            mensaje = `💰 Pago en efectivo procesado\n\nMonto recibido: $${montoRecibido.toFixed(2)}\nCambio: $${cambio.toFixed(2)}`;
        }
    } else { // transferencia
        const referencia = document.getElementById('referenciaTransferencia').value;
        if (!referencia) {
            alert('❌ Ingrese el número de referencia');
            validado = false;
        } else {
            const banco = document.getElementById('bancoTransferencia').value;
            mensaje = `📱 Pago con transferencia procesado\n\nReferencia: ${referencia}\nBanco: ${banco || 'No especificado'}`;
        }
    }
    
    if (validado) {
        alert('✅ Venta completada exitosamente\n\n' + mensaje);
        cerrarModalPago();
        // Aquí puedes agregar lógica para limpiar el carrito
        
        // Opcional: limpiar la tabla después de la venta
        if (confirm('¿Desea limpiar el carrito?')) {
            const tbody = document.querySelector('.pv-tabla tbody');
            tbody.innerHTML = '';
            actualizarTotales();
        }
    }
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('pagoModal');
    if (event.target == modal) {
        cerrarModalPago();
    }
}

// Manejar tecla ESC para cerrar
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('pagoModal');
        if (modal.classList.contains('show')) {
            cerrarModalPago();
        }
    }
});

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    // Establecer color inicial del cambio
    const cambioDisplay = document.getElementById('cambioDisplay');
    if (cambioDisplay) {
        cambioDisplay.style.color = 'var(--success)';
    }
    
    // Asegurar que el modal empiece oculto
    const modal = document.getElementById('pagoModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('show');
    }
});
</script>

<?php include '../footer.php'; ?>