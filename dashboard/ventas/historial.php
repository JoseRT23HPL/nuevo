<?php
include '../header.php';
?>

<!-- Header del Historial -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-history" style="color: var(--primary);"></i>
            <h1>Historial de Ventas</h1>
        </div>
        <span class="pv-badge">CONSULTA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="/dashboard/ventas/index.php" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i>
            Nueva Venta
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <div class="filtros-grid">
            <!-- Fecha inicio -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar"></i>
                    Fecha inicio
                </label>
                <input type="date" name="fecha_inicio" value="2024-03-01" class="filtro-input">
            </div>
            
            <!-- Fecha fin -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar-check"></i>
                    Fecha fin
                </label>
                <input type="date" name="fecha_fin" value="2024-03-31" class="filtro-input">
            </div>
            
            <!-- Buscar -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-search"></i>
                    Buscar
                </label>
                <input type="text" name="buscar" placeholder="Folio o cajero..." class="filtro-input">
            </div>
            
            <!-- Botones -->
            <div class="filtro-botones">
                <button type="submit" class="btn-filtro btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
                
                <a href="/dashboard/ventas/historial.php" class="btn-filtro btn-secondary">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Estadísticas -->
<div class="estadisticas-grid">
    <!-- Total ventas -->
    <div class="estadistica-card">
        <div class="estadistica-icon blue">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Total de ventas</span>
            <span class="estadistica-valor">156</span>
        </div>
    </div>
    
    <!-- Ingresos totales -->
    <div class="estadistica-card">
        <div class="estadistica-icon green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Ingresos totales</span>
            <span class="estadistica-valor">$45,678.90</span>
        </div>
    </div>
    
    <!-- Ticket promedio -->
    <div class="estadistica-card">
        <div class="estadistica-icon yellow">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="estadistica-info">
            <span class="estadistica-label">Ticket promedio</span>
            <span class="estadistica-valor">$292.81</span>
        </div>
    </div>
</div>

<!-- Tabla de ventas -->
<div class="tabla-container">
    <table class="tabla-historial">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Cajero</th>
                <th class="text-center">Productos</th>
                <th class="text-center">Método</th>
                <th class="text-right">Total</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Venta 1 -->
            <tr class="fila-venta">
                <td class="col-folio">
                    <span class="folio-numero">#V-001234</span>
                </td>
                <td class="col-fecha">
                    <span class="fecha-dia">15/03/2024</span>
                    <span class="fecha-hora">14:30</span>
                </td>
                <td class="col-cajero">
                    <span class="cajero-nombre">Admin User</span>
                </td>
                <td class="text-center">
                    <span class="badge-productos">5</span>
                </td>
                <td class="text-center">
                    <span class="badge-metodo efectivo">Efectivo</span>
                </td>
                <td class="col-total">$1,250.00</td>
                <td class="col-acciones">
                    <div class="acciones-wrapper">
                        <a href="#" class="accion-icon" title="Ver ticket" target="_blank">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="#" class="accion-icon" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
            
            <!-- Venta 2 -->
            <tr class="fila-venta">
                <td class="col-folio">
                    <span class="folio-numero">#V-001233</span>
                </td>
                <td class="col-fecha">
                    <span class="fecha-dia">15/03/2024</span>
                    <span class="fecha-hora">12:15</span>
                </td>
                <td class="col-cajero">
                    <span class="cajero-nombre">María González</span>
                </td>
                <td class="text-center">
                    <span class="badge-productos">3</span>
                </td>
                <td class="text-center">
                    <span class="badge-metodo tarjeta">Tarjeta</span>
                </td>
                <td class="col-total">$890.50</td>
                <td class="col-acciones">
                    <div class="acciones-wrapper">
                        <a href="#" class="accion-icon" title="Ver ticket">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="#" class="accion-icon" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
            
            <!-- Venta 3 -->
            <tr class="fila-venta">
                <td class="col-folio">
                    <span class="folio-numero">#V-001232</span>
                </td>
                <td class="col-fecha">
                    <span class="fecha-dia">15/03/2024</span>
                    <span class="fecha-hora">10:45</span>
                </td>
                <td class="col-cajero">
                    <span class="cajero-nombre">Carlos Rodríguez</span>
                </td>
                <td class="text-center">
                    <span class="badge-productos">8</span>
                </td>
                <td class="text-center">
                    <span class="badge-metodo transferencia">Transferencia</span>
                </td>
                <td class="col-total">$2,340.00</td>
                <td class="col-acciones">
                    <div class="acciones-wrapper">
                        <a href="#" class="accion-icon" title="Ver ticket">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="#" class="accion-icon" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
            
            <!-- Venta 4 -->
            <tr class="fila-venta">
                <td class="col-folio">
                    <span class="folio-numero">#V-001231</span>
                </td>
                <td class="col-fecha">
                    <span class="fecha-dia">14/03/2024</span>
                    <span class="fecha-hora">18:20</span>
                </td>
                <td class="col-cajero">
                    <span class="cajero-nombre">Admin User</span>
                </td>
                <td class="text-center">
                    <span class="badge-productos">2</span>
                </td>
                <td class="text-center">
                    <span class="badge-metodo efectivo">Efectivo</span>
                </td>
                <td class="col-total">$560.00</td>
                <td class="col-acciones">
                    <div class="acciones-wrapper">
                        <a href="#" class="accion-icon" title="Ver ticket">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="#" class="accion-icon" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
            
            <!-- Venta 5 -->
            <tr class="fila-venta">
                <td class="col-folio">
                    <span class="folio-numero">#V-001230</span>
                </td>
                <td class="col-fecha">
                    <span class="fecha-dia">14/03/2024</span>
                    <span class="fecha-hora">16:00</span>
                </td>
                <td class="col-cajero">
                    <span class="cajero-nombre">Laura Méndez</span>
                </td>
                <td class="text-center">
                    <span class="badge-productos">7</span>
                </td>
                <td class="text-center">
                    <span class="badge-metodo tarjeta">Tarjeta</span>
                </td>
                <td class="col-total">$1,890.00</td>
                <td class="col-acciones">
                    <div class="acciones-wrapper">
                        <a href="#" class="accion-icon" title="Ver ticket">
                            <i class="fas fa-receipt"></i>
                        </a>
                        <a href="#" class="accion-icon" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    
    <!-- Resumen de resultados -->
    <div class="tabla-footer">
        <p class="resultados-info">
            <i class="fas fa-info-circle"></i>
            Mostrando: <strong>5 ventas</strong> del período 01/03/2024 al 31/03/2024
        </p>
    </div>
</div>

<!-- Empty state (comentado por ahora) 
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-shopping-cart"></i>
    </div>
    <h3>No hay ventas en este período</h3>
    <p>Prueba con otros filtros o realiza una nueva venta</p>
    <a href="/dashboard/ventas/index.php" class="btn-header primary">
        <i class="fas fa-plus"></i>
        Nueva Venta
    </a>
</div>
-->

<style>
/* Animación para las filas */
@keyframes fadeInRow {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fila-venta {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-venta:nth-child(1) { animation-delay: 0.05s; }
.fila-venta:nth-child(2) { animation-delay: 0.1s; }
.fila-venta:nth-child(3) { animation-delay: 0.15s; }
.fila-venta:nth-child(4) { animation-delay: 0.2s; }
.fila-venta:nth-child(5) { animation-delay: 0.25s; }
</style>

<?php include '../footer.php'; ?>