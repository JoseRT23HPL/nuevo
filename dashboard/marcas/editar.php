<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

$error = '';
$success = '';

// Obtener ID de la marca
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . url('dashboard/marcas/index.php'));
    exit;
}

// Obtener datos actuales de la marca
$stmt = $conn->prepare("SELECT * FROM marcas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$marca = $result->fetch_assoc();

if (!$marca) {
    header('Location: ' . url('dashboard/marcas/index.php'));
    exit;
}

// Obtener productos asociados a esta marca
$productos = [];
$stmt = $conn->prepare("
    SELECT id, nombre, sku, stock_actual 
    FROM productos 
    WHERE id_marca = ? 
    ORDER BY fecha_creacion DESC 
    LIMIT 5
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

// Estadísticas de productos
$stats = [
    'total' => 0,
    'con_stock' => 0,
    'agotados' => 0
];

$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN stock_actual > 0 THEN 1 ELSE 0 END) as con_stock,
        SUM(CASE WHEN stock_actual = 0 THEN 1 ELSE 0 END) as agotados
    FROM productos 
    WHERE id_marca = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre de la marca es obligatorio';
    } else {
        // Verificar si ya existe otra marca con ese nombre (excluyendo la actual)
        $stmt = $conn->prepare("SELECT id FROM marcas WHERE nombre = ? AND id != ?");
        $stmt->bind_param("si", $nombre, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Ya existe otra marca con ese nombre';
        } else {
            // Actualizar marca
            $stmt = $conn->prepare("UPDATE marcas SET nombre = ?, descripcion = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("ssii", $nombre, $descripcion, $activo, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Marca actualizada correctamente";
                header('Location: ' . url('dashboard/marcas/index.php'));
                exit;
            } else {
                $error = 'Error al actualizar la marca: ' . $conn->error;
            }
        }
    }
}

include '../header.php';
?>

<!-- Header del formulario -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-edit" style="color: var(--primary);"></i>
            <h1>Editar Marca</h1>
        </div>
        <span class="pv-badge">FERREFÁCIL</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/marcas/index.php'); ?>" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver a marcas
        </a>
    </div>
</div>

<!-- Contenedor del formulario -->
<div class="form-container" style="max-width: 600px; margin: 0 auto;">
    
    <!-- Mensajes de alerta -->
    <?php if ($error): ?>
    <div class="alerta error">
        <i class="fas fa-exclamation-circle"></i>
        <p><?php echo h($error); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Formulario de edición de marca -->
    <div class="marca-form-wrapper">
        <div class="form-icon-header">
            <i class="fas fa-trademark"></i>
            <h3>Editando: <?php echo h($marca['nombre']); ?></h3>
        </div>
        
        <form method="POST" class="marca-form">
            <!-- ID oculto -->
            <input type="hidden" name="id" value="<?php echo $marca['id']; ?>">
            
            <!-- Nombre de la marca -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-heading"></i>
                    Nombre de la marca <span class="required">*</span>
                </label>
                <input type="text" name="nombre" class="form-input" 
                       value="<?php echo h($marca['nombre']); ?>" 
                       placeholder="Ej: Truper" required autofocus>
                <small class="form-hint">Nombre único para identificar la marca</small>
            </div>
            
            <!-- Descripción -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left"></i>
                    Descripción
                </label>
                <textarea name="descripcion" class="form-textarea" rows="4" 
                          placeholder="Describe brevemente esta marca..."><?php echo h($marca['descripcion']); ?></textarea>
                <small class="form-hint">Opcional - Una breve descripción de la marca</small>
            </div>
            
            <!-- Estado activo/inactivo (checkbox personalizado) -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="activo" class="checkbox-input" 
                           <?php echo $marca['activo'] ? 'checked' : ''; ?>>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Marca activa</span>
                </label>
                <small class="form-hint" style="margin-left: 1.7rem;">
                    Si está inactiva, no aparecerá al crear productos
                </small>
            </div>
            
            <!-- Información adicional -->
            <div class="info-box" style="margin-bottom: 2rem;">
                <i class="fas fa-info-circle"></i>
                <div class="info-box-content">
                    <h4>Información importante</h4>
                    <p>El nombre de la marca debe ser único en el sistema. Al desactivar una marca, los productos existentes mantendrán su clasificación pero no podrás asignarla a nuevos productos.</p>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
                
                <a href="<?php echo url('dashboard/marcas/index.php'); ?>" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Información de productos asociados (solo si hay productos) -->
    <?php if ($stats['total'] > 0): ?>
    <div class="productos-asociados" style="margin-top: 2rem;">
        <div class="productos-header">
            <i class="fas fa-boxes"></i>
            <span>Productos de esta marca</span>
        </div>
        
        <div class="productos-stats">
            <div class="stat-item">
                <span class="stat-label">Total productos:</span>
                <span class="stat-value"><?php echo $stats['total']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Con stock:</span>
                <span class="stat-value success"><?php echo $stats['con_stock']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Agotados:</span>
                <span class="stat-value warning"><?php echo $stats['agotados']; ?></span>
            </div>
        </div>
        
        <?php if (count($productos) > 0): ?>
        <div class="productos-lista-preview">
            <h4>Últimos productos agregados:</h4>
            <ul>
                <?php foreach ($productos as $p): ?>
                <li>
                    <i class="fas fa-cube"></i> 
                    <?php echo h($p['nombre']); ?> 
                    <small style="color: var(--gray-500);">(SKU: <?php echo h($p['sku']); ?> | Stock: <?php echo $p['stock_actual']; ?>)</small>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?php echo url('dashboard/productos/index.php?marca=' . $marca['id']); ?>" class="ver-mas">
                Ver todos los productos →
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
/* Animación del formulario */
@keyframes fadeInForm {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.marca-form {
    animation: fadeInForm 0.3s ease-out;
}

/* Checkbox personalizado */
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    margin-bottom: 0.25rem;
}

.checkbox-input {
    display: none;
}

.checkbox-custom {
    width: 1.2rem;
    height: 1.2rem;
    border: 2px solid var(--gray-300);
    border-radius: var(--radius-sm);
    position: relative;
    transition: all 0.2s;
}

.checkbox-input:checked + .checkbox-custom {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-color: var(--primary);
}

.checkbox-input:checked + .checkbox-custom::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 0.8rem;
}

.checkbox-text {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--gray-700);
}

/* Estilos para productos asociados */
.productos-asociados {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.productos-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: var(--gray-600);
    font-size: 0.9rem;
    font-weight: 500;
}

.productos-header i {
    color: var(--primary);
}

.productos-stats {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background-color: var(--gray-50);
    border-radius: var(--radius-md);
}

.stat-item {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.stat-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--gray-800);
}

.stat-value.success {
    color: var(--success);
}

.stat-value.warning {
    color: #f59e0b;
}

.productos-lista-preview h4 {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.75rem;
}

.productos-lista-preview ul {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem 0;
}

.productos-lista-preview li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px dashed var(--gray-200);
    font-size: 0.85rem;
    color: var(--gray-600);
}

.productos-lista-preview li i {
    color: var(--primary);
    font-size: 0.8rem;
    width: 1.2rem;
    text-align: center;
}

.productos-lista-preview li small {
    font-size: 0.7rem;
    color: var(--gray-500);
}

.ver-mas {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
    margin-top: 0.5rem;
}

.ver-mas:hover {
    text-decoration: underline;
}

/* Estilos específicos para el formulario de marcas */
.marca-form-wrapper {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
}

.form-icon-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--primary);
}

.form-icon-header i {
    font-size: 1.25rem;
    color: var(--primary);
    background: var(--primary-alpha);
    padding: 0.75rem;
    border-radius: 50%;
}

.form-icon-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .marca-form-wrapper {
        padding: 1.5rem;
    }
    
    .productos-stats {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-submit,
    .btn-cancel {
        width: 100%;
    }
}
</style>

<?php include '../footer.php'; ?>