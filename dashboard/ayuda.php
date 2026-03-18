<?php
// file: /dashboard/ayuda.php
require_once '../config.php';
requiereAuth();

$conn = getDB();

// Obtener el usuario actual
$usuario_actual = getCurrentUser();

// Estadísticas del sistema
$stats_sistema = [];

// Total de productos
$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$stats_sistema['total_productos'] = $result->fetch_assoc()['total'];

// Total de ventas hoy
$hoy = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha_venta) = ? AND estado = 'completada'");
$stmt->bind_param("s", $hoy);
$stmt->execute();
$stats_sistema['ventas_hoy'] = $stmt->get_result()->fetch_assoc()['total'];

// Total de usuarios activos
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
$stats_sistema['usuarios_activos'] = $result->fetch_assoc()['total'];

// Versión del sistema
$version_sistema = APP_VERSION;
$nombre_empresa = APP_NAME;

include 'header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-question-circle"></i>
            <h1>Centro de Ayuda</h1>
        </div>
        <span class="pv-badge">SOPORTE</span>
    </div>
    
    <div class="pv-header-right">
        <a href="index.php" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver al Dashboard
        </a>
    </div>
</div>

<!-- Subtítulo personalizado -->
<p class="page-subtitle">
    Hola <?php echo h($usuario_actual['nombre'] ?? $usuario_actual['username']); ?>, 
    encuentra respuestas y aprende a usar el sistema
</p>

<!-- Pestañas de ayuda -->
<div class="ayuda-tabs-container">
    <div class="ayuda-tabs">
        <button onclick="showTab('manual')" class="tab-btn active" id="tab-btn-manual">
            <i class="fas fa-book"></i>
            Manual de Usuario
        </button>
        <button onclick="showTab('faq')" class="tab-btn" id="tab-btn-faq">
            <i class="fas fa-question"></i>
            Preguntas Frecuentes
        </button>
        <button onclick="showTab('soporte')" class="tab-btn" id="tab-btn-soporte">
            <i class="fas fa-headset"></i>
            Soporte Técnico
        </button>
        <button onclick="showTab('acerca')" class="tab-btn" id="tab-btn-acerca">
            <i class="fas fa-info-circle"></i>
            Acerca del Sistema
        </button>
    </div>
</div>

<!-- Contenido: Manual de Usuario -->
<div id="tab-manual" class="tab-content active">
    <div class="ayuda-card">
        <div class="card-header">
            <i class="fas fa-book-open"></i>
            <h3>Manual de Usuario - <?php echo h($nombre_empresa); ?></h3>
        </div>
        
        <div class="card-content manual-content">
            <!-- Dashboard -->
            <div class="manual-section">
                <h4 class="section-title">
                    <span class="section-icon blue">📊</span>
                    Dashboard
                </h4>
                <p class="section-description">El panel principal muestra un resumen de tu negocio:</p>
                <ul class="section-list">
                    <li><strong>Tarjetas de estadísticas:</strong> Total de productos, stock bajo, ventas del día, ingresos</li>
                    <li><strong>Gráficos:</strong> Ventas de los últimos 7 días</li>
                    <li><strong>Accesos rápidos:</strong> Botones para las funciones más usadas</li>
                </ul>
            </div>
            
            <!-- Inventario -->
            <div class="manual-section">
                <h4 class="section-title">
                    <span class="section-icon green">📦</span>
                    Inventario
                </h4>
                <ul class="section-list">
                    <li><strong>Productos:</strong> Ver lista completa, buscar, filtrar por categoría/marca</li>
                    <li><strong>Categorías y Marcas:</strong> Administrar clasificaciones de productos</li>
                    <li><strong>Entrada por Escáner:</strong> Usa la pistola para buscar y agregar stock rápidamente</li>
                    <li><strong>Ajustar Stock:</strong> Entradas, salidas y ajustes manuales con motivo</li>
                </ul>
            </div>
            
            <!-- Ventas -->
            <div class="manual-section">
                <h4 class="section-title">
                    <span class="section-icon yellow">🛒</span>
                    Ventas
                </h4>
                <ul class="section-list">
                    <li><strong>Punto de Venta:</strong> Agrega productos por código o nombre, carrito en tiempo real</li>
                    <li><strong>Múltiples métodos de pago:</strong> Efectivo, transferencia</li>
                    <li><strong>Tickets:</strong> Genera e imprime tickets de venta</li>
                    <li><strong>Historial:</strong> Consulta todas las ventas realizadas</li>
                </ul>
            </div>
            
            <!-- Reportes -->
            <div class="manual-section">
                <h4 class="section-title">
                    <span class="section-icon purple">📈</span>
                    Reportes
                </h4>
                <ul class="section-list">
                    <li><strong>Ventas:</strong> Reporte detallado con filtros por fecha, usuario, método de pago</li>
                    <li><strong>Productos más vendidos:</strong> Ranking por unidades e ingresos</li>
                    <li><strong>Cortes de Caja:</strong> Abrir/cerrar caja y ver historial de cortes</li>
                </ul>
            </div>
            
            <!-- Usuarios -->
            <div class="manual-section">
                <h4 class="section-title">
                    <span class="section-icon red">👥</span>
                    Usuarios <?php echo hasRole('super_admin') ? '(Super Admin)' : ''; ?>
                </h4>
                <ul class="section-list">
                    <li><strong>Gestionar usuarios:</strong> Crear, editar, activar/desactivar</li>
                    <li><strong>Roles:</strong> Super Admin (acceso total), Admin (gestión), Cajero (solo ventas)</li>
                </ul>
            </div>
            
            <!-- Configuración -->
            <div class="manual-section">
                <h4 class="section-title">
                    <span class="section-icon gray">⚙️</span>
                    Configuración
                </h4>
                <ul class="section-list">
                    <li><strong>Mi Perfil:</strong> Actualizar tu información personal</li>
                    <li><strong>Cambiar Contraseña:</strong> Actualizar tu password</li>
                    <li><strong>Configuración Empresa:</strong> Datos del negocio y logo (Super Admin)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Contenido: Preguntas Frecuentes -->
<div id="tab-faq" class="tab-content">
    <div class="ayuda-card">
        <div class="card-header">
            <i class="fas fa-question-circle"></i>
            <h3>Preguntas Frecuentes</h3>
        </div>
        
        <div class="card-content faq-content">
            <div class="faq-item">
                <button onclick="toggleFAQ(this)" class="faq-question">
                    <span class="faq-question-text">¿Cómo agrego un nuevo producto?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                    <p>Ve a <strong>Inventario → Productos</strong> y haz clic en el botón <span class="badge-primary">+ Nuevo Producto</span>. Completa el formulario con código de barras, nombre, precio y stock inicial.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button onclick="toggleFAQ(this)" class="faq-question">
                    <span class="faq-question-text">¿Cómo uso el escáner para entrada rápida?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                    <p>Ve a <strong>Inventario → Entrada por Escáner</strong>. Escanea el código de barras con la pistola, el producto se cargará automáticamente. Solo ingresa la cantidad y el motivo.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button onclick="toggleFAQ(this)" class="faq-question">
                    <span class="faq-question-text">¿Cómo hago una venta?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                    <p>Ve a <strong>Ventas → Punto de Venta</strong>. Escanea o busca productos, se agregarán al carrito. Puedes ajustar cantidades. Al finalizar, haz clic en "Cobrar" y selecciona el método de pago.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button onclick="toggleFAQ(this)" class="faq-question">
                    <span class="faq-question-text">¿Cómo hago el corte de caja?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                    <p>Ve a <strong>Reportes → Corte de Caja</strong>. Si no hay corte abierto, haz clic en "Abrir Corte" con el monto inicial. Al terminar el turno, haz clic en "Cerrar Corte" e ingresa el monto final.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button onclick="toggleFAQ(this)" class="faq-question">
                    <span class="faq-question-text">¿Qué permisos tengo como <?php echo h($usuario_actual['rol']); ?>?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                    <?php if ($usuario_actual['rol'] == 'super_admin'): ?>
                    <p>Tienes <strong>acceso total</strong> al sistema: puedes gestionar usuarios, configurar la empresa, ver todos los reportes y acceder a todas las funciones.</p>
                    <?php elseif ($usuario_actual['rol'] == 'admin'): ?>
                    <p>Puedes gestionar productos, ver reportes, pero <strong>no puedes crear/modificar usuarios</strong> (solo verlos).</p>
                    <?php else: ?>
                    <p>Tienes acceso limitado a <strong>ventas y consulta de inventario</strong>. No puedes modificar productos ni ver reportes administrativos.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenido: Soporte Técnico -->
<div id="tab-soporte" class="tab-content">
    <div class="ayuda-card">
        <div class="card-header">
            <i class="fas fa-headset"></i>
            <h3>Soporte Técnico</h3>
        </div>
        
        <div class="card-content">
            <!-- Contacto grid -->
            <div class="contacto-grid">
                <div class="contacto-item blue">
                    <div class="contacto-icon blue">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contacto-info">
                        <span class="contacto-label">Teléfono</span>
                        <span class="contacto-valor">+52 249 156 3269</span>
                        <span class="contacto-horario">Lun-Vie 9am-6pm</span>
                    </div>
                </div>
                
                <div class="contacto-item green">
                    <div class="contacto-icon green">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contacto-info">
                        <span class="contacto-label">Email</span>
                        <span class="contacto-valor">atencion.usuario.pos@gmail.com</span>
                        <span class="contacto-horario">Respuesta 24hrs</span>
                    </div>
                </div>
                
                <div class="contacto-item whatsapp">
                    <div class="contacto-icon whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="contacto-info">
                        <span class="contacto-label">WhatsApp</span>
                        <span class="contacto-valor">+52 238 222 6000</span>
                        <span class="contacto-horario">Chat en vivo</span>
                    </div>
                </div>
                
                <div class="contacto-item purple">
                    <div class="contacto-icon purple">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contacto-info">
                        <span class="contacto-label">Horario</span>
                        <span class="contacto-valor">9:00 AM - 6:00 PM</span>
                        <span class="contacto-horario">Hora del Centro</span>
                    </div>
                </div>
            </div>
            
            <!-- Formulario de contacto -->
            <div class="form-contacto">
                <h4 class="form-contacto-titulo">Envíanos un mensaje</h4>
                
                <form id="soporteForm" method="POST" action="ajax/soporte.php" class="form-contacto-grid">
                    <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="nombre" placeholder="Tu nombre" 
                                   value="<?php echo h($usuario_actual['nombre'] ?? $usuario_actual['username']); ?>" 
                                   required class="form-input">
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Tu email" 
                                   value="<?php echo h($usuario_actual['email'] ?? ''); ?>" 
                                   required class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecciona tipo de consulta</option>
                            <option value="problema">🔧 Problema técnico</option>
                            <option value="duda">❓ Duda del sistema</option>
                            <option value="sugerencia">💡 Sugerencia</option>
                            <option value="otro">📝 Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <textarea name="mensaje" rows="4" placeholder="Describe tu problema o consulta..." required class="form-textarea"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <p class="form-hint">
                            <i class="fas fa-info-circle"></i>
                            Tu mensaje será enviado al equipo de soporte
                        </p>
                        <button type="submit" class="btn-submit small">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Mensaje
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Contenido: Acerca del Sistema -->
<div id="tab-acerca" class="tab-content">
    <div class="ayuda-card">
        <div class="card-header">
            <i class="fas fa-info-circle"></i>
            <h3>Acerca del Sistema</h3>
        </div>
        
        <div class="card-content acerca-content">
            <div class="acerca-logo">
                <i class="fas fa-store"></i>
            </div>
            
            <h2 class="acerca-titulo"><?php echo h($nombre_empresa); ?></h2>
            <p class="acerca-subtitulo">Sistema de Punto de Venta</p>
            
            <p class="acerca-version">Versión <?php echo h($version_sistema); ?></p>
            
            <div class="acerca-grid">
                <div class="acerca-item">
                    <span class="acerca-label">Desarrollado para</span>
                    <span class="acerca-valor"><?php echo h($nombre_empresa); ?></span>
                </div>
                <div class="acerca-item">
                    <span class="acerca-label">Fecha de lanzamiento</span>
                    <span class="acerca-valor">Febrero 2026</span>
                </div>
                <div class="acerca-item">
                    <span class="acerca-label">Última actualización</span>
                    <span class="acerca-valor"><?php echo date('d/m/Y'); ?></span>
                </div>
                <div class="acerca-item">
                    <span class="acerca-label">Tecnologías</span>
                    <span class="acerca-valor">PHP, MySQL, JavaScript</span>
                </div>
            </div>
            
            <div class="acerca-caracteristicas">
                <h4 class="caracteristicas-titulo">Estadísticas del sistema</h4>
                <div class="caracteristicas-grid">
                    <p class="caracteristica-item">
                        <i class="fas fa-box"></i>
                        <?php echo number_format($stats_sistema['total_productos']); ?> productos
                    </p>
                    <p class="caracteristica-item">
                        <i class="fas fa-shopping-cart"></i>
                        <?php echo $stats_sistema['ventas_hoy']; ?> ventas hoy
                    </p>
                    <p class="caracteristica-item">
                        <i class="fas fa-users"></i>
                        <?php echo $stats_sistema['usuarios_activos']; ?> usuarios activos
                    </p>
                    <p class="caracteristica-item">
                        <i class="fas fa-check-circle"></i>
                        Corte de caja integrado
                    </p>
                </div>
            </div>
            
            <div class="acerca-footer">
                <p class="copyright">
                    <i class="far fa-copyright"></i>
                    <?php echo date('Y'); ?> <?php echo h($nombre_empresa); ?>. Todos los derechos reservados.
                </p>
                <p class="copyright-sub">
                    Este software es de uso exclusivo para el personal autorizado.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.getElementById('tab-' + tabName).classList.add('active');
    event.currentTarget.classList.add('active');
}

function toggleFAQ(button) {
    const faqItem = button.closest('.faq-item');
    const answer = faqItem.querySelector('.faq-answer');
    const icon = button.querySelector('.faq-icon');
    
    faqItem.classList.toggle('active');
    
    if (faqItem.classList.contains('active')) {
        answer.style.display = 'block';
        icon.classList.add('rotate-180');
    } else {
        answer.style.display = 'none';
        icon.classList.remove('rotate-180');
    }
}

// Envío del formulario con AJAX
document.getElementById('soporteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('ajax/soporte.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            this.reset();
            document.querySelector('input[name="nombre"]').value = '<?php echo h($usuario_actual['nombre'] ?? $usuario_actual['username']); ?>';
            document.querySelector('input[name="email"]').value = '<?php echo h($usuario_actual['email'] ?? ''); ?>';
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('Error al enviar el mensaje. Intenta de nuevo.');
        console.error(error);
    });
});
</script>

<?php include 'footer.php'; ?>