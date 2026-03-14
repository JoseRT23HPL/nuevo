<?php
// file: /dashboard/usuarios/ver.php - VERSIÓN CON MÉTODOS CORREGIDOS
require_once '../../config.php';
requiereAuth();

// Solo super_admin y admin pueden ver usuarios
if (!hasRole('super_admin') && !hasRole('admin')) {
    header('Location: ' . url('dashboard/index.php'));
    exit;
}

$conn = getDB();
$error = '';

// Obtener ID del usuario de la URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID de usuario no válido";
    header('Location: ' . url('dashboard/usuarios/index.php'));
    exit;
}

// Obtener datos del usuario
$stmt = $conn->prepare("
    SELECT id, username, nombre, email, rol, activo,
           fecha_creacion,
           ultimo_acceso
    FROM usuarios 
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Usuario no encontrado";
    header('Location: ' . url('dashboard/usuarios/index.php'));
    exit;
}


$usuario = $result->fetch_assoc();

// Obtener estadísticas de ventas
$ventas_count = 0;
$ventas_total = 0;
$cortes_count = 0;
$ultimas_ventas = [];

// Verificar si la tabla ventas existe
$check_ventas = $conn->query("SHOW TABLES LIKE 'ventas'");
if ($check_ventas && $check_ventas->num_rows > 0) {
    // Consulta para estadísticas
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as ventas_count,
            COALESCE(SUM(total), 0) as ventas_total
        FROM ventas 
        WHERE id_usuario = ? AND estado = 'completada'
    ");
    $stmt_stats->bind_param("i", $id);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();
    
    $ventas_count = $stats['ventas_count'] ?? 0;
    $ventas_total = $stats['ventas_total'] ?? 0;
    
    // Obtener últimas ventas
    $stmt_ventas = $conn->prepare("
        SELECT 
            id,
            folio,
            fecha_venta as fecha,
            total,
            metodo_pago as metodo,
            estado
        FROM ventas 
        WHERE id_usuario = ?
        ORDER BY fecha_venta DESC
        LIMIT 5
    ");
    $stmt_ventas->bind_param("i", $id);
    $stmt_ventas->execute();
    $result_ventas = $stmt_ventas->get_result();
    $ultimas_ventas = $result_ventas->fetch_all(MYSQLI_ASSOC);
}

// Verificar si la tabla cortes_caja existe
$check_cortes = $conn->query("SHOW TABLES LIKE 'cortes_caja'");
if ($check_cortes && $check_cortes->num_rows > 0) {
    // Ver qué columna usa cortes_caja
    $columnas_cortes = $conn->query("SHOW COLUMNS FROM cortes_caja");
    $columna_usuario_cortes = 'id_usuario'; // Valor por defecto
    
    while ($col = $columnas_cortes->fetch_assoc()) {
        if ($col['Field'] == 'id_usuario' || $col['Field'] == 'usuario_id') {
            $columna_usuario_cortes = $col['Field'];
            break;
        }
    }
    
    $query_cortes = "SELECT COUNT(*) as cortes_count FROM cortes_caja WHERE $columna_usuario_cortes = ?";
    $stmt_cortes = $conn->prepare($query_cortes);
    $stmt_cortes->bind_param("i", $id);
    $stmt_cortes->execute();
    $cortes = $stmt_cortes->get_result()->fetch_assoc();
    $cortes_count = $cortes['cortes_count'] ?? 0;
}

// Extraer variables para la vista
$user_id = $usuario['id'];
$username = $usuario['username'];
$nombre_completo = $usuario['nombre'] ?? '';
$email = $usuario['email'] ?? '';
$rol = $usuario['rol'] ?? 'cajero';
$activo = $usuario['activo'] ?? 1;
$fecha_creacion = $usuario['fecha_creacion'] ?? '';
$ultimo_acceso = $usuario['ultimo_acceso'] ?? '';

// Determinar color y texto del rol
$rol_color = '';
$rol_texto = '';
if ($rol == 'super_admin') {
    $rol_color = 'super-admin';
    $rol_texto = 'Super Administrador';
} elseif ($rol == 'admin') {
    $rol_color = 'admin';
    $rol_texto = 'Administrador';
} else {
    $rol_color = 'cajero';
    $rol_texto = 'Cajero';
}

include '../header.php';
?>

<!-- Mostrar mensajes de sesión -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alerta success">
    <i class="fas fa-check-circle"></i>
    <p><?php echo h($_SESSION['success']); unset($_SESSION['success']); ?></p>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alerta error">
    <i class="fas fa-exclamation-circle"></i>
    <p><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></p>
</div>
<?php endif; ?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-user-circle"></i>
            <h1>Detalle del Usuario</h1>
        </div>
        <span class="pv-badge">PERFIL</span>
    </div>
    
    <div class="pv-header-right">
        <?php if (hasRole('super_admin')): ?>
        <a href="editar.php?id=<?php echo $user_id; ?>" class="btn-header primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <?php endif; ?>
        <a href="index.php" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Información completa y estadísticas del usuario</p>

<!-- TU HTML EXISTENTE -->
<div class="usuario-detalle-container">
    <!-- Tarjeta de perfil -->
    <div class="usuario-perfil-container">
        <div class="usuario-perfil-header"></div>
        <div class="usuario-perfil-content">
            <div class="usuario-perfil-avatar">
                <?php 
                $nombre_para_avatar = !empty($nombre_completo) ? $nombre_completo : $username;
                $inicial = !empty($nombre_para_avatar) ? strtoupper(substr($nombre_para_avatar, 0, 1)) : 'U';
                echo $inicial;
                ?>
            </div>
            <div class="usuario-perfil-info">
                <h2 class="usuario-perfil-nombre"><?php echo htmlspecialchars(!empty($nombre_completo) ? $nombre_completo : $username); ?></h2>
                <p class="usuario-perfil-username">@<?php echo htmlspecialchars($username); ?></p>
                
                <div class="usuario-perfil-badges">
                    <span class="rol-badge <?php echo $rol_color; ?>">
                        <?php 
                        if ($rol == 'super_admin') echo '<i class="fas fa-crown"></i>';
                        elseif ($rol == 'admin') echo '<i class="fas fa-user-tie"></i>';
                        else echo '<i class="fas fa-cash-register"></i>';
                        ?>
                        <?php echo $rol_texto; ?>
                    </span>
                    
                    <?php if ($activo): ?>
                        <span class="estado-badge activo">
                            <span class="estado-punto activo"></span>
                            Activo
                        </span>
                    <?php else: ?>
                        <span class="estado-badge inactivo">
                            <span class="estado-punto inactivo"></span>
                            Inactivo
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Grid de información -->
    <div class="usuario-detalle-grid">
        <!-- Información de contacto -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-address-card"></i>
                <h3>Información de Contacto</h3>
            </div>
            <div class="info-card-content">
                <div class="info-contacto-item">
                    <div class="info-contacto-icon blue">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-contacto-text">
                        <span class="info-contacto-label">Email</span>
                        <span class="info-contacto-valor"><?php echo !empty($email) ? htmlspecialchars($email) : 'No especificado'; ?></span>
                    </div>
                </div>
                
                <div class="info-contacto-item">
                    <div class="info-contacto-icon green">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="info-contacto-text">
                        <span class="info-contacto-label">Miembro desde</span>
                        <span class="info-contacto-valor">
                            <?php 
                            if (!empty($fecha_creacion)) {
                                echo date('d/m/Y', strtotime($fecha_creacion));
                            } else {
                                echo 'No disponible';
                            }
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-contacto-item">
                    <div class="info-contacto-icon purple">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-contacto-text">
                        <span class="info-contacto-label">Último acceso</span>
                        <span class="info-contacto-valor">
                            <?php 
                            if (!empty($ultimo_acceso)) {
                                echo date('d/m/Y', strtotime($ultimo_acceso)) . ' a las ' . date('H:i', strtotime($ultimo_acceso));
                            } else {
                                echo 'Nunca';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-chart-bar"></i>
                <h3>Estadísticas</h3>
            </div>
            <div class="info-card-content">
                <div class="estadisticas-grid">
                    <div class="estadistica-item blue">
                        <span class="estadistica-valor"><?php echo $ventas_count; ?></span>
                        <span class="estadistica-label">Ventas</span>
                    </div>
                    <div class="estadistica-item green">
                        <span class="estadistica-valor">$<?php echo number_format($ventas_total, 0); ?></span>
                        <span class="estadistica-label">Ingresos</span>
                    </div>
                    <div class="estadistica-item purple">
                        <span class="estadistica-valor"><?php echo $cortes_count; ?></span>
                        <span class="estadistica-label">Cortes</span>
                    </div>
                </div>
                
                <?php if ($ventas_count > 0): ?>
                <div class="ticket-promedio">
                    <span class="ticket-label">Ticket promedio:</span>
                    <span class="ticket-valor">$<?php echo number_format($ventas_total / $ventas_count, 2); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Últimas ventas -->
    <?php if (!empty($ultimas_ventas)): ?>
    <div class="info-card ultimas-ventas">
        <div class="info-card-header">
            <i class="fas fa-history"></i>
            <h3>Últimas Ventas</h3>
            <?php if($ventas_count > 5): ?>
            <a href="<?php echo url('dashboard/ventas/index.php?usuario=' . $user_id); ?>" class="btn-ver-todas">
                Ver todas
            </a>
            <?php endif; ?>
        </div>
        <div class="info-card-content">
            <div class="tabla-responsive">
                <table class="tabla-ventas-usuario">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th class="text-right">Total</th>
                            <th>Método</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimas_ventas as $v): ?>
                        <tr>
                            <td class="folio-cell"><?php echo $v['folio']; ?></td>
                            <td class="fecha-cell">
                                <?php echo date('d/m/Y', strtotime($v['fecha'])); ?>
                                <span class="fecha-hora"><?php echo date('H:i', strtotime($v['fecha'])); ?></span>
                            </td>
                            <td class="total-cell">$<?php echo number_format($v['total'], 2); ?></td>
                            <td>
                                <?php
                                $metodo = $v['metodo'] ?? 'efectivo';
                                $metodo_class = '';
                                $metodo_text = '';
                                
                                // SOLO EFECTIVO O TRANSFERENCIA
                                switch($metodo) {
                                    case 'efectivo':
                                        $metodo_class = 'efectivo';
                                        $metodo_text = 'Efectivo';
                                        break;
                                    case 'transferencia':
                                        $metodo_class = 'transferencia';
                                        $metodo_text = 'Transferencia';
                                        break;
                                    default:
                                        // Si hay otro método, lo mostramos como "Otro" pero manteniendo el valor
                                        $metodo_class = 'otro';
                                        $metodo_text = ucfirst($metodo);
                                }
                                ?>
                                <span class="metodo-badge <?php echo $metodo_class; ?>">
                                    <?php echo $metodo_text; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $estado = $v['estado'] ?? 'completada';
                                $estado_class = '';
                                if ($estado == 'completada') {
                                    $estado_class = 'completada';
                                } elseif ($estado == 'cancelada') {
                                    $estado_class = 'cancelada';
                                } else {
                                    $estado_class = 'pendiente';
                                }
                                ?>
                                <span class="estado-venta-badge <?php echo $estado_class; ?>">
                                    <?php echo ucfirst($estado); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="info-box info">
        <i class="fas fa-info-circle"></i>
        <div class="info-box-content">
            <h4>Sin ventas</h4>
            <p>Este usuario aún no ha realizado ventas. Las ventas aparecerán aquí cuando realice su primera venta.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>