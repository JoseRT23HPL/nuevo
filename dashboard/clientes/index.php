<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Procesar eliminación (si viene por GET)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verificar si tiene ventas asociadas
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ventas WHERE id_cliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $check = $result->fetch_assoc();
    
    if ($check['total'] == 0) {
        // Eliminar cliente
        $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Cliente eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar el cliente";
        }
    } else {
        $_SESSION['error'] = "No se puede eliminar el cliente porque tiene {$check['total']} ventas asociadas";
    }
    
    header('Location: ' . url('dashboard/clientes/index.php'));
    exit;
}

// Obtener parámetros de búsqueda
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Construir condiciones WHERE
$condiciones = ["activo = 1"];
$params = [];
$types = "";

if (!empty($buscar)) {
    $condiciones[] = "(nombre LIKE ? OR apellidos LIKE ? OR documento LIKE ? OR email LIKE ? OR telefono LIKE ?)";
    $buscar_param = "%$buscar%";
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $types .= "sssss";
}

$where = implode(" AND ", $condiciones);

// Obtener clientes
$sql = "SELECT * FROM clientes WHERE $where ORDER BY nombre ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

// Estadísticas
$total_clientes = count($clientes);
$con_email = count(array_filter($clientes, function($c) { return !empty($c['email']); }));
$con_telefono = count(array_filter($clientes, function($c) { return !empty($c['telefono']); }));

// Mostrar mensajes de sesión
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include '../header.php';
?>

<!-- Header de Clientes -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-users" style="color: var(--primary);"></i>
            <h1>Clientes</h1>
        </div>
        <span class="pv-badge">CARTERA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/clientes/nuevo.php'); ?>" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-user-plus"></i>
            Nuevo Cliente
        </a>
    </div>
</div>

<!-- Mensajes de alerta -->
<?php if (isset($success)): ?>
<div class="alerta success">
    <i class="fas fa-check-circle"></i>
    <p><?php echo h($success); ?></p>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alerta error">
    <i class="fas fa-exclamation-circle"></i>
    <p><?php echo h($error); ?></p>
</div>
<?php endif; ?>

<!-- Buscador -->
<div class="buscador-container" style="margin-bottom: 1.5rem; padding: 1.5rem;">
    <form method="GET" class="buscador-form">
        <div class="buscador-wrapper">
            <div class="buscador-input-wrapper">
                <i class="fas fa-search buscador-icon"></i>
                <input type="text" name="buscar" class="buscador-input" 
                       placeholder="Buscar por nombre, documento o email..." 
                       value="<?php echo h($buscar); ?>">
            </div>
            <button type="submit" class="btn-buscar">
                <i class="fas fa-search"></i>
                Buscar
            </button>
            <?php if (!empty($buscar)): ?>
                <a href="<?php echo url('dashboard/clientes/index.php'); ?>" class="btn-limpiar">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($buscar)): ?>
            <p class="resultado-busqueda">
                <i class="fas fa-info-circle"></i>
                Resultados para: <strong>"<?php echo h($buscar); ?>"</strong>
            </p>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de clientes -->
<div class="clientes-container">
    <div class="tabla-responsive">
        <table class="tabla-clientes">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clientes) > 0): ?>
                    <?php foreach ($clientes as $c): ?>
                    <tr class="fila-cliente">
                        <td class="col-documento">
                            <div class="documento-wrapper">
                                <span class="tipo-documento"><?php echo $c['tipo_documento']; ?></span>
                                <span class="documento-numero"><?php echo h($c['documento']); ?></span>
                            </div>
                        </td>
                        <td class="col-nombre">
                            <span class="cliente-nombre">
                                <?php echo h($c['nombre'] . ' ' . ($c['apellidos'] ?? '')); ?>
                            </span>
                        </td>
                        <td class="col-email">
                            <?php if (!empty($c['email'])): ?>
                                <a href="mailto:<?php echo h($c['email']); ?>" class="email-link">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo h($c['email']); ?>
                                </a>
                            <?php else: ?>
                                <span class="sin-info">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-telefono">
                            <?php if (!empty($c['telefono'])): ?>
                                <a href="tel:<?php echo h($c['telefono']); ?>" class="telefono-link">
                                    <i class="fas fa-phone-alt"></i>
                                    <?php echo h($c['telefono']); ?>
                                </a>
                            <?php else: ?>
                                <span class="sin-info">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-acciones">
                            <div class="acciones-wrapper">
                                <a href="<?php echo url('dashboard/clientes/ver.php?id=' . $c['id']); ?>" 
                                   class="accion-icon" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo url('dashboard/clientes/editar.php?id=' . $c['id']); ?>" 
                                   class="accion-icon" title="Editar cliente">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <?php if ($c['id'] > 0): ?>
                                    <a href="?delete=<?php echo $c['id']; ?>" 
                                       class="accion-icon eliminar" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state-row">
                            <div class="empty-state-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>No hay clientes registrados</h3>
                            <?php if (!empty($buscar)): ?>
                                <p>No se encontraron resultados para "<?php echo h($buscar); ?>"</p>
                                <a href="<?php echo url('dashboard/clientes/index.php'); ?>" class="btn-limpiar" style="margin-top: 1rem;">
                                    <i class="fas fa-times"></i>
                                    Limpiar búsqueda
                                </a>
                            <?php else: ?>
                                <p>Comienza registrando tu primer cliente</p>
                                <a href="<?php echo url('dashboard/clientes/nuevo.php'); ?>" class="btn-primary" style="margin-top: 1rem;">
                                    <i class="fas fa-user-plus"></i>
                                    Nuevo Cliente
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <?php if (count($clientes) > 0): ?>
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-users"></i>
                Total de clientes: <strong><?php echo $total_clientes; ?></strong>
            </p>
            <div class="resumen-stats">
                <span class="stat-badge">
                    <i class="fas fa-envelope" style="color: var(--primary);"></i>
                    <span><?php echo $con_email; ?> con email</span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-phone-alt" style="color: var(--success);"></i>
                    <span><?php echo $con_telefono; ?> con teléfono</span>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Animaciones para las filas */
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

.fila-cliente {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-cliente:nth-child(1) { animation-delay: 0.02s; }
.fila-cliente:nth-child(2) { animation-delay: 0.04s; }
.fila-cliente:nth-child(3) { animation-delay: 0.06s; }
.fila-cliente:nth-child(4) { animation-delay: 0.08s; }
.fila-cliente:nth-child(5) { animation-delay: 0.10s; }
.fila-cliente:nth-child(6) { animation-delay: 0.12s; }
</style>

<?php include '../footer.php'; ?>