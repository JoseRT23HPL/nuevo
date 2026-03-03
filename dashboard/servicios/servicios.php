<?php
include '../header.php';
?>

<!-- Header de Servicios -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-tools"></i>
            <h1>Servicios y Envíos</h1>
        </div>
        <span class="pv-badge">SERV-01</span>
    </div>
</div>

<!-- Buscador y acciones rápidas -->
<div class="pv-buscador-wrapper">
    <div class="pv-buscador" style="flex: 0 0 60%;">
        <div class="buscador-container">
            <i class="fas fa-barcode buscador-icon"></i>
            <input type="text" class="buscador-input" placeholder="Buscar por código, SKU, nombre del producto..." autofocus>
            <button class="btn-buscar" onclick="buscarProducto()">
                <i class="fas fa-search"></i>
                Buscar
            </button>
        </div>
    </div>
    
    <div class="pv-botones-header">
        <button class="btn-header">
            <i class="fas fa-file-invoice"></i>
            Cotizaciones
        </button>
        <button class="btn-header">
            <i class="fas fa-truck"></i>
            Ver envíos
        </button>
        <button class="btn-header">
            <i class="fas fa-history"></i>
            Historial
        </button>
    </div>
</div>

<!-- Grid de dos columnas -->
<div class="servicios-grid">
    <!-- Columna Izquierda: Productos y Búsqueda -->
    <div class="servicios-col-left">
        <!-- Resultados de búsqueda con stock -->
        <div class="resultados-busqueda-servicios">
            <div class="resultados-header">
                <i class="fas fa-box"></i>
                <h3>Productos encontrados</h3>
                <span class="stock-badge">Stock en bodega</span>
            </div>
            
            <div class="productos-lista">
                <!-- Producto 1 -->
                <div class="producto-item" onclick="agregarProducto(this)">
                    <div class="producto-info">
                        <span class="producto-codigo">SKU: CEM-001</span>
                        <span class="producto-nombre">Cemento Portland Gris 50kg</span>
                        <span class="producto-precio">$185.00</span>
                    </div>
                    <div class="producto-stock">
                        <span class="stock-disponible">1245 sacos</span>
                        <button class="btn-agregar-servicio">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Producto 2 -->
                <div class="producto-item" onclick="agregarProducto(this)">
                    <div class="producto-info">
                        <span class="producto-codigo">SKU: VAR-3/8</span>
                        <span class="producto-nombre">Varilla Corrugada 3/8" x 12m</span>
                        <span class="producto-precio">$245.00</span>
                    </div>
                    <div class="producto-stock">
                        <span class="stock-disponible">856 piezas</span>
                        <button class="btn-agregar-servicio">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Producto 3 -->
                <div class="producto-item" onclick="agregarProducto(this)">
                    <div class="producto-info">
                        <span class="producto-codigo">SKU: ARE-001</span>
                        <span class="producto-nombre">Arena Fina x m³</span>
                        <span class="producto-precio">$350.00</span>
                    </div>
                    <div class="producto-stock">
                        <span class="stock-disponible">45 m³</span>
                        <button class="btn-agregar-servicio">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Producto 4 -->
                <div class="producto-item" onclick="agregarProducto(this)">
                    <div class="producto-info">
                        <span class="producto-codigo">SKU: GRA-001</span>
                        <span class="producto-nombre">Grava 3/4" x m³</span>
                        <span class="producto-precio">$420.00</span>
                    </div>
                    <div class="producto-stock">
                        <span class="stock-disponible">38 m³</span>
                        <button class="btn-agregar-servicio">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Producto 5 -->
                <div class="producto-item" onclick="agregarProducto(this)">
                    <div class="producto-info">
                        <span class="producto-codigo">SKU: MAD-001</span>
                        <span class="producto-nombre">Madera Pino 2x4 x 3m</span>
                        <span class="producto-precio">$95.00</span>
                    </div>
                    <div class="producto-stock">
                        <span class="stock-disponible">234 piezas</span>
                        <button class="btn-agregar-servicio">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cotizaciones recientes -->
        <div class="cotizaciones-recientes">
            <div class="cotizaciones-header">
                <i class="fas fa-file-signature"></i>
                <h3>Cotizaciones recientes</h3>
                <button class="btn-ver-todas">Ver todas</button>
            </div>
            
            <div class="cotizaciones-lista">
                <!-- Cotización 1 -->
                <div class="cotizacion-item">
                    <div class="cotizacion-info">
                        <span class="cotizacion-cliente">Cliente: Constructora ABC</span>
                        <span class="cotizacion-fecha">15/03/2024</span>
                        <span class="cotizacion-monto">$12,450.00</span>
                    </div>
                    <button class="btn-convertir" onclick="convertirCotizacion(this)">
                        <i class="fas fa-arrow-right"></i>
                        Convertir a venta
                    </button>
                </div>
                
                <!-- Cotización 2 -->
                <div class="cotizacion-item">
                    <div class="cotizacion-info">
                        <span class="cotizacion-cliente">Cliente: María González</span>
                        <span class="cotizacion-fecha">14/03/2024</span>
                        <span class="cotizacion-monto">$3,250.00</span>
                    </div>
                    <button class="btn-convertir" onclick="convertirCotizacion(this)">
                        <i class="fas fa-arrow-right"></i>
                        Convertir a venta
                    </button>
                </div>
                
                <!-- Cotización 3 -->
                <div class="cotizacion-item">
                    <div class="cotizacion-info">
                        <span class="cotizacion-cliente">Cliente: Obras SA</span>
                        <span class="cotizacion-fecha">13/03/2024</span>
                        <span class="cotizacion-monto">$28,900.00</span>
                    </div>
                    <button class="btn-convertir" onclick="convertirCotizacion(this)">
                        <i class="fas fa-arrow-right"></i>
                        Convertir a venta
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Columna Derecha: Carrito de Servicios y Logística -->
    <div class="servicios-col-right">
        <!-- Productos seleccionados -->
        <div class="servicios-carrito">
            <div class="carrito-header">
                <i class="fas fa-shopping-cart"></i>
                <h3>Productos para servicio</h3>
                <span class="carrito-badge">3 items</span>
            </div>
            
            <div class="carrito-items-lista">
                <!-- Item 1 -->
                <div class="carrito-item-servicio">
                    <div class="item-info">
                        <span class="item-nombre">Cemento Portland Gris 50kg</span>
                        <span class="item-detalle">Cantidad: 20 sacos | $185.00 c/u</span>
                    </div>
                    <div class="item-total">$3,700.00</div>
                    <button class="item-eliminar" onclick="eliminarItem(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Item 2 -->
                <div class="carrito-item-servicio">
                    <div class="item-info">
                        <span class="item-nombre">Varilla Corrugada 3/8"</span>
                        <span class="item-detalle">Cantidad: 50 piezas | $245.00 c/u</span>
                    </div>
                    <div class="item-total">$12,250.00</div>
                    <button class="item-eliminar" onclick="eliminarItem(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Item 3 -->
                <div class="carrito-item-servicio">
                    <div class="item-info">
                        <span class="item-nombre">Arena Fina</span>
                        <span class="item-detalle">Cantidad: 3 m³ | $350.00 c/u</span>
                    </div>
                    <div class="item-total">$1,050.00</div>
                    <button class="item-eliminar" onclick="eliminarItem(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Subtotal productos -->
            <div class="carrito-subtotal">
                <span>Subtotal productos</span>
                <span class="subtotal-monto">$17,000.00</span>
            </div>
        </div>
        
        <!-- Módulo de Logística de Envío -->
        <div class="logistica-envio">
            <div class="logistica-header">
                <i class="fas fa-truck"></i>
                <h3>Logística de envío</h3>
            </div>
            
            <div class="logistica-form">
                <!-- Fecha y hora de entrega -->
                <div class="form-row">
                    <div class="form-group half">
                        <label>
                            <i class="fas fa-calendar"></i>
                            Fecha de entrega
                        </label>
                        <input type="date" class="form-input" id="fechaEntrega" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    <div class="form-group half">
                        <label>
                            <i class="fas fa-clock"></i>
                            Hora
                        </label>
                        <input type="time" class="form-input" id="horaEntrega" value="10:00">
                    </div>
                </div>
                
                <!-- Dirección de entrega -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-map-marker-alt"></i>
                        Dirección de entrega
                    </label>
                    <div class="direccion-input">
                        <input type="text" class="form-input" placeholder="Calle, número, colonia" id="direccion">
                        <button class="btn-geolocalizar" onclick="geolocalizar()">
                            <i class="fas fa-map-pin"></i>
                            Ubicar
                        </button>
                    </div>
                </div>
                
                <!-- Referencias -->
                <div class="form-group">
                    <input type="text" class="form-input" placeholder="Referencias (casa color rojo, junto a tienda...)" id="referencias">
                </div>
                
                <!-- Tipo de vehículo y cálculo de flete -->
                <div class="form-row">
                    <div class="form-group half">
                        <label>
                            <i class="fas fa-truck"></i>
                            Tipo de vehículo
                        </label>
                        <select class="form-input" id="tipoVehiculo" onchange="calcularFlete()">
                            <option value="motocarro">Motocarro - $50</option>
                            <option value="camioneta" selected>Camioneta - $150</option>
                            <option value="camion">Camión grande - $350</option>
                            <option value="plataforma">Plataforma - $500</option>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label>
                            <i class="fas fa-weight"></i>
                            Peso aproximado
                        </label>
                        <div class="input-prefix">
                            <span>kg</span>
                            <input type="number" class="form-input" id="peso" value="850" oninput="calcularFlete()">
                        </div>
                    </div>
                </div>
                
                <!-- Distancia y costo de flete -->
                <div class="form-row">
                    <div class="form-group half">
                        <label>
                            <i class="fas fa-road"></i>
                            Distancia
                        </label>
                        <div class="input-prefix">
                            <span>km</span>
                            <input type="number" class="form-input" id="distancia" value="5.5" step="0.1" oninput="calcularFlete()">
                        </div>
                    </div>
                    <div class="form-group half">
                        <label>
                            <i class="fas fa-dollar-sign"></i>
                            Costo flete
                        </label>
                        <div class="input-prefix">
                            <span>$</span>
                            <input type="text" class="form-input" id="costoFlete" value="425.00" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen de cálculo -->
                <div class="flete-resumen">
                    <div class="resumen-item">
                        <span>Costo base vehículo:</span>
                        <span id="costoBase">$150.00</span>
                    </div>
                    <div class="resumen-item">
                        <span>Costo por km ($50/km):</span>
                        <span id="costoKm">$275.00</span>
                    </div>
                    <div class="resumen-item total">
                        <span>Total flete:</span>
                        <span id="totalFlete">$425.00</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Totales y acciones -->
        <div class="servicios-totales">
            <div class="totales-detalle">
                <div class="total-linea">
                    <span>Subtotal productos:</span>
                    <span>$17,000.00</span>
                </div>
                <div class="total-linea">
                    <span>Costo de envío:</span>
                    <span>$425.00</span>
                </div>
                <div class="total-linea total-final">
                    <span>TOTAL SERVICIO:</span>
                    <span>$17,425.00</span>
                </div>
            </div>
            
            <div class="servicios-acciones">
                <button class="btn-accion-servicio" onclick="guardarCotizacion()">
                    <i class="fas fa-file-invoice"></i>
                    Guardar como cotización
                </button>
                <button class="btn-accion-servicio primary" onclick="procesarVentaServicio()">
                    <i class="fas fa-cash-register"></i>
                    Cobrar servicio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Función para buscar productos
function buscarProducto() {
    // Aquí iría la lógica de búsqueda
    alert('Buscando productos...');
}

// Función para agregar producto al carrito
function agregarProducto(elemento) {
    const nombre = elemento.querySelector('.producto-nombre').textContent;
    alert(`Agregando: ${nombre}`);
    // Aquí iría la lógica para agregar al carrito
}

// Función para eliminar item del carrito
function eliminarItem(btn) {
    if (confirm('¿Eliminar este producto del servicio?')) {
        const item = btn.closest('.carrito-item-servicio');
        item.remove();
        actualizarTotalesServicio();
    }
}

// Función para calcular flete
function calcularFlete() {
    const tipoVehiculo = document.getElementById('tipoVehiculo').value;
    const distancia = parseFloat(document.getElementById('distancia').value) || 0;
    const peso = parseFloat(document.getElementById('peso').value) || 0;
    
    // Costos base por vehículo
    const costosBase = {
        'motocarro': 50,
        'camioneta': 150,
        'camion': 350,
        'plataforma': 500
    };
    
    const costoBase = costosBase[tipoVehiculo] || 150;
    const costoPorKm = 50 * distancia;
    const costoPorPeso = Math.floor(peso / 100) * 20; // $20 por cada 100kg extra
    
    const totalFlete = costoBase + costoPorKm + costoPorPeso;
    
    // Actualizar UI
    document.getElementById('costoBase').textContent = `$${costoBase.toFixed(2)}`;
    document.getElementById('costoKm').textContent = `$${costoPorKm.toFixed(2)}`;
    document.getElementById('totalFlete').textContent = `$${totalFlete.toFixed(2)}`;
    document.getElementById('costoFlete').value = totalFlete.toFixed(2);
    
    actualizarTotalesServicio();
}

// Función para actualizar totales del servicio
function actualizarTotalesServicio() {
    // Calcular subtotal productos
    const items = document.querySelectorAll('.carrito-item-servicio');
    let subtotal = 0;
    
    items.forEach(item => {
        const totalTexto = item.querySelector('.item-total').textContent;
        const total = parseFloat(totalTexto.replace('$', '').replace(',', ''));
        subtotal += total;
    });
    
    const costoFlete = parseFloat(document.getElementById('costoFlete').value) || 0;
    const totalServicio = subtotal + costoFlete;
    
    // Actualizar UI
    document.querySelector('.subtotal-monto').textContent = `$${subtotal.toFixed(2)}`;
    document.querySelector('.total-linea:nth-child(1) span:last-child').textContent = `$${subtotal.toFixed(2)}`;
    document.querySelector('.total-linea:nth-child(3) span:last-child').textContent = `$${totalServicio.toFixed(2)}`;
}

// Función para geolocalizar
function geolocalizar() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            alert(`Ubicación obtenida: Lat ${position.coords.latitude}, Long ${position.coords.longitude}`);
            // Aquí iría la lógica para obtener dirección desde coordenadas
        });
    } else {
        alert('Geolocalización no soportada');
    }
}

// Función para convertir cotización a venta
function convertirCotizacion(btn) {
    const cotizacion = btn.closest('.cotizacion-item');
    const cliente = cotizacion.querySelector('.cotizacion-cliente').textContent;
    
    if (confirm(`¿Convertir ${cliente} a venta?`)) {
        alert('Cotización convertida a venta');
        // Aquí iría la lógica para cargar los productos de la cotización
    }
}

// Función para guardar cotización
function guardarCotizacion() {
    alert('Cotización guardada exitosamente');
}

// Función para procesar venta de servicio
function procesarVentaServicio() {
    const total = document.querySelector('.total-linea.total-final span:last-child').textContent;
    
    if (confirm(`¿Procesar venta de servicio por ${total}?`)) {
        alert('Venta procesada exitosamente');
        // Aquí iría la lógica para limpiar el carrito
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    calcularFlete();
    actualizarTotalesServicio();
});
</script>

<?php include '../footer.php'; ?>