<?php
// Este archivo es solo HTML
include 'header.php';
?>

<!-- ===== SECCIÓN DE BIENVENIDA ===== -->
<section class="welcome-section">
    <div class="welcome-container">
        <!-- Saludo personalizado -->
        <div class="welcome-header">
            <span class="welcome-greeting">¡Hola de nuevo,</span>
            <h1 class="welcome-name">Admin User! 👋</h1>
        </div>
        
        <!-- Mensaje motivacional -->
        <p class="welcome-message">
            Vamos a ver qué tenemos hoy en tu negocio
        </p>
        
        <!-- Indicador de scroll con animación -->
        <div class="scroll-indicator" id="scrollIndicator">
            <div class="scroll-text">Descubre tu dashboard</div>
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>
    
    <!-- Decoración de fondo -->
    <div class="welcome-decoration">
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>
    </div>
</section>

<!-- ===== CONTENIDO DEL DASHBOARD ===== -->
<section class="dashboard-content" id="dashboardContent">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">Aquí tienes el resumen de tu negocio hoy</p>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
        <!-- Total Productos -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
                <span class="stat-badge">Inventario</span>
            </div>
            <h3 class="stat-value">1,234</h3>
            <p class="stat-label">Total Productos</p>
            <div class="stat-footer">
                <span class="stat-trend">+5%</span>
                <span>vs mes anterior</span>
            </div>
        </div>
        
        <!-- Stock Bajo -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <span class="stat-badge">Alerta</span>
            </div>
            <h3 class="stat-value">12</h3>
            <p class="stat-label">Stock Bajo</p>
            <a href="/dashboard/inventario/stock_bajo.php" class="stat-link">
                Revisar stock <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <!-- Ventas Hoy -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon green">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="stat-badge">Hoy</span>
            </div>
            <h3 class="stat-value">24</h3>
            <p class="stat-label">Ventas del día</p>
        </div>
        
        <!-- Ingresos Hoy -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon yellow">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <span class="stat-badge">Hoy</span>
            </div>
            <h3 class="stat-value">$1,250.00</h3>
            <p class="stat-label">Ingresos del día</p>
        </div>
        
        <!-- Usuarios Activos -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <span class="stat-badge">Sistema</span>
            </div>
            <h3 class="stat-value">8</h3>
            <p class="stat-label">Usuarios Activos</p>
        </div>
    </div>

    <!-- Grid principal -->
    <div class="two-column-grid">
        <!-- Últimas Ventas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Últimas Ventas
                </h3>
                <a href="/dashboard/ventas/historial.php" class="card-link">
                    Ver todas <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th class="amount">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="folio">#V001234</td>
                                <td>15/03/2024 14:30</td>
                                <td>Admin</td>
                                <td class="amount">$1,250.00</td>
                            </tr>
                            <tr>
                                <td class="folio">#V001233</td>
                                <td>15/03/2024 12:15</td>
                                <td>María G.</td>
                                <td class="amount">$890.50</td>
                            </tr>
                            <tr>
                                <td class="folio">#V001232</td>
                                <td>15/03/2024 10:45</td>
                                <td>Carlos R.</td>
                                <td class="amount">$2,340.00</td>
                            </tr>
                            <tr>
                                <td class="folio">#V001231</td>
                                <td>14/03/2024 18:20</td>
                                <td>Admin</td>
                                <td class="amount">$560.00</td>
                            </tr>
                            <tr>
                                <td class="folio">#V001230</td>
                                <td>14/03/2024 16:00</td>
                                <td>Laura M.</td>
                                <td class="amount">$1,890.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Productos con Stock Bajo -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-circle warning-icon"></i>
                    Stock Bajo
                </h3>
                <a href="/dashboard/inventario/stock_bajo.php" class="card-link">
                    Ver todos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="stock-list">
                    <!-- Producto 1 -->
                    <div class="stock-item">
                        <div class="stock-info">
                            <p class="stock-name">Martillo de Uña 16oz</p>
                            <p class="stock-code">Código: MT-001</p>
                        </div>
                        <div class="stock-details">
                            <div class="stock-numbers">
                                <span class="stock-current zero">0</span>
                                <span class="stock-min">/ 5</span>
                            </div>
                            <span class="stock-badge danger">AGOTADO</span>
                            <a href="#" class="stock-action">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Producto 2 -->
                    <div class="stock-item">
                        <div class="stock-info">
                            <p class="stock-name">Taladro Percutor 500W</p>
                            <p class="stock-code">Código: TL-023</p>
                        </div>
                        <div class="stock-details">
                            <div class="stock-numbers">
                                <span class="stock-current">2</span>
                                <span class="stock-min">/ 3</span>
                            </div>
                            <span class="stock-badge warning">BAJO</span>
                            <a href="#" class="stock-action">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Producto 3 -->
                    <div class="stock-item">
                        <div class="stock-info">
                            <p class="stock-name">Caja de Tornillos 1/2"</p>
                            <p class="stock-code">Código: TR-456</p>
                        </div>
                        <div class="stock-details">
                            <div class="stock-numbers">
                                <span class="stock-current">3</span>
                                <span class="stock-min">/ 10</span>
                            </div>
                            <span class="stock-badge warning">BAJO</span>
                            <a href="#" class="stock-action">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Producto 4 -->
                    <div class="stock-item">
                        <div class="stock-info">
                            <p class="stock-name">Sierra Circular 7-1/4"</p>
                            <p class="stock-code">Código: SR-789</p>
                        </div>
                        <div class="stock-details">
                            <div class="stock-numbers">
                                <span class="stock-current">1</span>
                                <span class="stock-min">/ 2</span>
                            </div>
                            <span class="stock-badge warning">BAJO</span>
                            <a href="#" class="stock-action">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ventas -->
    <div class="chart-container">
        <div class="chart-header">
            <h3>
                <i class="fas fa-chart-line"></i>
                Ventas de los últimos 7 días
            </h3>
        </div>
        <div class="chart-body">
            <canvas id="ventasChart" class="chart-canvas"></canvas>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="quick-actions">
        <a href="/dashboard/productos/nuevo.php" class="action-card">
            <div class="action-icon blue">
                <i class="fas fa-box"></i>
            </div>
            <div class="action-info">
                <h4>Nuevo Producto</h4>
                <p>Agregar al inventario</p>
            </div>
        </a>
        
        <a href="/dashboard/ventas/index.php" class="action-card">
            <div class="action-icon green">
                <i class="fas fa-cash-register"></i>
            </div>
            <div class="action-info">
                <h4>Nueva Venta</h4>
                <p>Ir al punto de venta</p>
            </div>
        </a>
        
        <a href="/dashboard/clientes/nuevo.php" class="action-card">
            <div class="action-icon purple">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="action-info">
                <h4>Nuevo Cliente</h4>
                <p>Registrar cliente</p>
            </div>
        </a>
        
        <a href="/dashboard/reportes/corte_caja.php" class="action-card">
            <div class="action-icon yellow">
                <i class="fas fa-cash-register"></i>
            </div>
            <div class="action-info">
                <h4>Corte de Caja</h4>
                <p>Cerrar turno</p>
            </div>
        </a>
    </div>
</section>

<!-- ===== SCRIPTS ===== -->
<!-- Primero main.js -->
<script src="assets/js/main.js"></script>

<!-- Script del gráfico -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll para el indicador
    const scrollIndicator = document.getElementById('scrollIndicator');
    const dashboardContent = document.getElementById('dashboardContent');
    
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            dashboardContent.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        });
    }
    
    // Mostrar/ocultar indicador de scroll
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            scrollIndicator.style.opacity = '0';
            scrollIndicator.style.visibility = 'hidden';
        } else {
            scrollIndicator.style.opacity = '1';
            scrollIndicator.style.visibility = 'visible';
        }
    });

    // Gráfico de ventas
    const ctx = document.getElementById('ventasChart').getContext('2d');
    const fechas = ['01/03', '02/03', '03/03', '04/03', '05/03', '06/03', '07/03'];
    const ventas = [1250, 890, 2340, 560, 1890, 2100, 1750];
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: fechas,
            datasets: [{
                label: 'Ventas ($)',
                data: ventas,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: 'white',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    callbacks: {
                        label: (context) => ' $' + context.parsed.y.toFixed(2)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => '$' + value }
                }
            }
        }
    });
});

function mostrarModalGlobal() {
    alert('Funcionalidad de cierre de sesión');
}
</script>



<?php include 'footer.php'; ?>