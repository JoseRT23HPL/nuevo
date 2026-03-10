<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// Procesar eliminación (si viene por GET)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verificar si tiene productos asociados
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE id_marca = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $check = $result->fetch_assoc();
    
    if ($check['total'] == 0) {
        // Eliminar marca
        $stmt = $conn->prepare("DELETE FROM marcas WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Marca eliminada correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar la marca";
        }
    } else {
        $_SESSION['error'] = "No se puede eliminar la marca porque tiene {$check['total']} productos asociados";
    }
    
    header('Location: ' . url('dashboard/marcas/index.php'));
    exit;
}

// Obtener todas las marcas con conteo de productos
$marcas = [];
$sql = "
    SELECT m.*, 
           (SELECT COUNT(*) FROM productos WHERE id_marca = m.id) as total_productos
    FROM marcas m
    ORDER BY m.nombre ASC
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $marcas[] = $row;
    }
}

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

<!-- Header de Marcas -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-trademark" style="color: var(--primary);"></i>
            <h1>Marcas</h1>
        </div>
        <span class="pv-badge">FERREFÁCIL</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/marcas/nuevo.php'); ?>" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i>
            Nueva Marca
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

<!-- Tabla de marcas -->
<div class="marcas-container">
    <div class="tabla-responsive">
        <table class="tabla-marcas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">Productos</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($marcas) > 0): ?>
                    <?php foreach ($marcas as $marca): ?>
                    <tr class="fila-marca">
                        <td class="col-id">#<?php echo str_pad($marca['id'], 2, '0', STR_PAD_LEFT); ?></td>
                        <td class="col-nombre">
                            <span class="marca-nombre"><?php echo h($marca['nombre']); ?></span>
                        </td>
                        <td class="col-descripcion">
                            <?php if ($marca['descripcion']): ?>
                                <span class="marca-descripcion"><?php echo h($marca['descripcion']); ?></span>
                            <?php else: ?>
                                <span class="sin-descripcion">Sin descripción</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-productos"><?php echo $marca['total_productos']; ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($marca['activo']): ?>
                                <span class="estado-badge activo">Activo</span>
                            <?php else: ?>
                                <span class="estado-badge inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-acciones">
                            <div class="acciones-wrapper">
                                <a href="<?php echo url('dashboard/marcas/editar.php?id=' . $marca['id']); ?>" 
                                   class="accion-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <?php if ($marca['total_productos'] == 0): ?>
                                    <a href="?delete=<?php echo $marca['id']; ?>" 
                                       class="accion-icon eliminar" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de eliminar esta marca?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="accion-icon disabled" 
                                          title="No se puede eliminar (tiene <?php echo $marca['total_productos']; ?> productos)">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-state-row">
                            <div class="empty-state-icon">
                                <i class="fas fa-trademark"></i>
                            </div>
                            <h3>No hay marcas creadas</h3>
                            <p>Comienza creando tu primera marca</p>
                            <a href="<?php echo url('dashboard/marcas/nuevo.php'); ?>" class="btn-header primary">
                                <i class="fas fa-plus"></i>
                                Nueva Marca
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <?php if (count($marcas) > 0): ?>
    <div class="tabla-footer">
        <p class="resumen-info">
            <i class="fas fa-trademark"></i>
            Total: <strong><?php echo count($marcas); ?></strong> marcas
        </p>
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

.fila-marca {
    animation: fadeInRow 0.3s ease-out forwards;
}

/* Estilos para el empty state */
.empty-state-row {
    text-align: center;
    padding: 3rem !important;
}

.empty-state-icon {
    width: 5rem;
    height: 5rem;
    margin: 0 auto 1.5rem;
    background-color: var(--gray-100);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--gray-400);
}

.empty-state-row h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.empty-state-row p {
    color: var(--gray-500);
    margin-bottom: 1rem;
}
</style>

<?php include '../footer.php'; ?>