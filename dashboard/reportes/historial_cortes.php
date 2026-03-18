<?php
// file: /dashboard/reportes/historial_cortes.php - VERSIÓN CONECTADA A BD
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Obtener el usuario actual
$usuario_actual = getCurrentUser();

// Procesar filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$buscar_usuario = $_GET['buscar_usuario'] ?? '';
$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;

// ===== 1. OBTENER USUARIOS PARA EL AUTOCOMPLETADO =====
$usuarios_query = $conn->query("
    SELECT id, username, nombre 
    FROM usuarios 
    WHERE activo = 1 
    ORDER BY nombre ASC
");

$usuarios = [];
while ($row = $usuarios_query->fetch_assoc()) {
    $usuarios[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'nombre_completo' => $row['nombre'] ?: $row['username']
    ];
}

// ===== 2. CONSTRUIR CONSULTA DE CORTES =====
$sql = "
    SELECT 
        c.*,
        u.username,
        u.nombre as nombre_usuario,
        (SELECT COUNT(*) FROM ventas WHERE fecha_venta BETWEEN c.fecha_apertura AND COALESCE(c.fecha_cierre, NOW())) as total_ventas,
        (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE fecha_venta BETWEEN c.fecha_apertura AND COALESCE(c.fecha_cierre, NOW())) as ingresos_periodo
    FROM cortes_caja c
    JOIN usuarios u ON c.id_usuario = u.id
    WHERE 1=1
";

$params = [];
$types = "";

// Filtro por fecha inicio
if (!empty($fecha_inicio)) {
    $sql .= " AND DATE(c.fecha_apertura) >= ?";
    $params[] = $fecha_inicio;
    $types .= "s";
}

// Filtro por fecha fin
if (!empty($fecha_fin)) {
    $sql .= " AND DATE(c.fecha_apertura) <= ?";
    $params[] = $fecha_fin;
    $types .= "s";
}

// Filtro por usuario ID (si se seleccionó de la búsqueda)
if ($usuario_id > 0) {
    $sql .= " AND c.id_usuario = ?";
    $params[] = $usuario_id;
    $types .= "i";
} 
// Filtro por búsqueda de texto (si no hay ID pero hay texto)
elseif (!empty($buscar_usuario)) {
    $sql .= " AND (u.nombre LIKE ? OR u.username LIKE ?)";
    $termino_busqueda = "%$buscar_usuario%";
    $params[] = $termino_busqueda;
    $params[] = $termino_busqueda;
    $types .= "ss";
}

// Solo cortes cerrados
$sql .= " AND c.fecha_cierre IS NOT NULL";

// Ordenar
$sql .= " ORDER BY c.fecha_cierre DESC";

// Ejecutar consulta
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$cortes = $stmt->get_result();

// ===== 3. CALCULAR TOTALES =====
$totales = [
    'total_cortes' => 0,
    'total_ingresos' => 0
];

// Guardar resultados en array para poder calcular totales
$cortes_array = [];
while ($row = $cortes->fetch_assoc()) {
    $cortes_array[] = $row;
    $totales['total_cortes']++;
    $totales['total_ingresos'] += $row['monto_final'] ?? 0;
}

include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-history"></i>
            <h1>Historial de Cortes de Caja</h1>
        </div>
        <span class="pv-badge">CAJA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="corte_caja.php" class="btn-header primary">
            <i class="fas fa-plus"></i>
            Nuevo Corte
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Consulta todos los cortes de caja realizados</p>

<!-- Filtros con búsqueda de usuarios -->
<div class="filtros-container">
    <form method="GET" class="filtros-form" id="filtrosForm">
        <div class="filtros-grid-cortes">
            <!-- Fecha inicio -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar"></i>
                    Fecha inicio
                </label>
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" class="filtro-input">
            </div>
            
            <!-- Fecha fin -->
            <div class="filtro-group">
                <label class="filtro-label">
                    <i class="fas fa-calendar-check"></i>
                    Fecha fin
                </label>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" class="filtro-input">
            </div>
            
            <!-- Búsqueda de usuario (typeahead) -->
            <div class="filtro-group search-group">
                <label class="filtro-label">
                    <i class="fas fa-user-search"></i>
                    Buscar usuario
                </label>
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="buscar_usuario" 
                           id="buscarUsuario" 
                           value="<?php echo htmlspecialchars($buscar_usuario); ?>" 
                           class="filtro-input search-input" 
                           placeholder="Nombre o usuario..."
                           autocomplete="off">
                    <input type="hidden" name="usuario_id" id="usuarioId" value="<?php echo $usuario_id; ?>">
                    <?php if (!empty($buscar_usuario) || $usuario_id > 0): ?>
                        <button type="button" class="clear-search" onclick="limpiarBusqueda()">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <div id="sugerenciasUsuarios" class="sugerencias-container"></div>
            </div>
            
            <!-- Botones -->
            <div class="filtro-botones">
                <button type="submit" class="btn-filtro btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
                
                <a href="historial_cortes.php" class="btn-filtro btn-secondary">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Estadísticas -->
<div class="stats-grid-cortes">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-cash-register"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Total de cortes</span>
            <span class="stat-valor"><?php echo $totales['total_cortes']; ?></span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Ingresos totales</span>
            <span class="stat-valor">$<?php echo number_format($totales['total_ingresos'], 2); ?></span>
        </div>
    </div>
</div>

<!-- TABLA COMPACTA DE CORTES -->
<div class="tabla-container">
    <div class="tabla-responsive">
        <table class="tabla-cortes">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Fecha de Apertura</th>
                    <th>Fecha de Cierre</th>
                    <th class="text-right">Monto Inicial</th>
                    <th class="text-right">Monto Final</th>
                    <th class="text-right">Ventas</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cortes_array) > 0): ?>
                    <?php foreach($cortes_array as $c): ?>
                    <tr class="fila-corte">
                        <td class="col-usuario">
                            <div class="usuario-info">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($c['nombre_usuario'] ?: $c['username']); ?></span>
                            </div>
                        </td>
                        <td class="col-fecha">
                            <i class="fas fa-play"></i>
                            <?php echo date('d/m/Y', strtotime($c['fecha_apertura'])); ?>
                            <small><?php echo date('H:i', strtotime($c['fecha_apertura'])); ?></small>
                        </td>
                        <td class="col-fecha">
                            <i class="fas fa-stop"></i>
                            <?php echo date('d/m/Y', strtotime($c['fecha_cierre'])); ?>
                            <small><?php echo date('H:i', strtotime($c['fecha_cierre'])); ?></small>
                        </td>
                        <td class="text-right col-monto">
                            $<?php echo number_format($c['monto_inicial'], 2); ?>
                        </td>
                        <td class="text-right col-total">
                            $<?php echo number_format($c['monto_final'], 2); ?>
                        </td>
                        <td class="text-right col-ventas">
                            <?php echo $c['total_ventas']; ?>
                        </td>
                        <td class="text-center col-acciones">
                            <a href="corte_detalle.php?id=<?php echo $c['id']; ?>" class="accion-icon" title="Ver detalle completo">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasRole('super_admin')): ?>
                            <button onclick="confirmarEliminar(<?php echo $c['id']; ?>)" class="accion-icon delete" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state-row">
                            <div class="empty-state-icon">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <h3>No hay cortes de caja registrados</h3>
                            <p>Prueba con otros filtros o realiza tu primer corte</p>
                            <a href="corte_caja.php" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Nuevo Corte
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (count($cortes_array) > 0): ?>
    <div class="tabla-footer">
        <p class="resumen-info">
            <i class="fas fa-info-circle"></i>
            Mostrando <strong><?php echo count($cortes_array); ?></strong> cortes
        </p>
    </div>
    <?php endif; ?>
</div>

<!-- Script para búsqueda de usuarios -->
<script>
// Datos de usuarios para el autocompletado
const usuarios = <?php echo json_encode($usuarios); ?>;

// Función para limpiar búsqueda
function limpiarBusqueda() {
    document.getElementById('buscarUsuario').value = '';
    document.getElementById('usuarioId').value = '';
    document.getElementById('sugerenciasUsuarios').style.display = 'none';
    document.getElementById('filtrosForm').submit();
}

// Búsqueda en tiempo real
document.getElementById('buscarUsuario').addEventListener('input', function() {
    const termino = this.value.toLowerCase().trim();
    const sugerenciasDiv = document.getElementById('sugerenciasUsuarios');
    
    if (termino.length < 1) {
        sugerenciasDiv.style.display = 'none';
        document.getElementById('usuarioId').value = '';
        return;
    }
    
    // Filtrar usuarios
    const resultados = usuarios.filter(u => 
        u.nombre_completo.toLowerCase().includes(termino) || 
        u.username.toLowerCase().includes(termino)
    );
    
    if (resultados.length === 0) {
        sugerenciasDiv.innerHTML = '<div class="sugerencia-item no-results">No se encontraron usuarios</div>';
        sugerenciasDiv.style.display = 'block';
        return;
    }
    
    // Mostrar sugerencias
    let html = '';
    resultados.slice(0, 5).forEach(u => {
        html += `
            <div class="sugerencia-item" onclick="seleccionarUsuario(${u.id}, '${u.username}')">
                <i class="fas fa-user-circle"></i>
                <div class="sugerencia-info">
                    <strong>${u.nombre_completo}</strong>
                    <small>@${u.username}</small>
                </div>
            </div>
        `;
    });
    
    sugerenciasDiv.innerHTML = html;
    sugerenciasDiv.style.display = 'block';
});

// Función para seleccionar un usuario
function seleccionarUsuario(id, username) {
    document.getElementById('buscarUsuario').value = username;
    document.getElementById('usuarioId').value = id;
    document.getElementById('sugerenciasUsuarios').style.display = 'none';
    document.getElementById('filtrosForm').submit();
}

// Ocultar sugerencias al hacer clic fuera
document.addEventListener('click', function(e) {
    const searchGroup = document.querySelector('.search-group');
    const sugerencias = document.getElementById('sugerenciasUsuarios');
    
    if (!searchGroup.contains(e.target)) {
        sugerencias.style.display = 'none';
    }
});

// Confirmar eliminación
function confirmarEliminar(id) {
    if (confirm('¿Estás seguro de eliminar este corte? Esta acción no se puede deshacer.')) {
        window.location.href = 'eliminar_corte.php?id=' + id;
    }
}
</script>

<?php include '../footer.php'; ?>