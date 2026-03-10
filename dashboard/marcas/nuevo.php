<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

$error = '';
$success = '';
$nombre = '';
$descripcion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre de la marca es obligatorio';
    } else {
        // Verificar si ya existe una marca con ese nombre
        $stmt = $conn->prepare("SELECT id FROM marcas WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Ya existe una marca con ese nombre';
        } else {
            // Insertar nueva marca
            $stmt = $conn->prepare("INSERT INTO marcas (nombre, descripcion) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $descripcion);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Marca creada correctamente";
                header('Location: ' . url('dashboard/marcas/index.php'));
                exit;
            } else {
                $error = 'Error al crear la marca: ' . $conn->error;
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
            <i class="fas fa-plus-circle" style="color: var(--primary);"></i>
            <h1>Nueva Marca</h1>
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
    
    <!-- Formulario de nueva marca -->
    <div class="marca-form-wrapper">
        <form method="POST" class="marca-form">
            <div class="form-icon-header">
                <i class="fas fa-trademark"></i>
                <h3>Información de la marca</h3>
            </div>
            
            <!-- Nombre de la marca -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-heading"></i>
                    Nombre de la marca <span class="required">*</span>
                </label>
                <input type="text" name="nombre" class="form-input" 
                       placeholder="Ej: Truper" 
                       value="<?php echo h($nombre); ?>"
                       required autofocus>
                <small class="form-hint">Nombre único para identificar la marca</small>
            </div>
            
            <!-- Descripción -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left"></i>
                    Descripción
                </label>
                <textarea name="descripcion" class="form-textarea" rows="4" 
                          placeholder="Describe brevemente esta marca..."><?php echo h($descripcion); ?></textarea>
                <small class="form-hint">Opcional - Una breve descripción de la marca</small>
            </div>
            
            <!-- Información adicional -->
            <div class="info-box" style="margin-bottom: 2rem;">
                <i class="fas fa-info-circle"></i>
                <div class="info-box-content">
                    <h4>Información importante</h4>
                    <p>El nombre de la marca debe ser único en el sistema. Las marcas te ayudan a organizar mejor tus productos.</p>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Guardar Marca
                </button>
                
                <a href="<?php echo url('dashboard/marcas/index.php'); ?>" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Ejemplos de marcas de ferretería -->
    <div class="ejemplos-container" style="margin-top: 2rem;">
        <div class="ejemplos-header">
            <i class="fas fa-lightbulb"></i>
            <span>Ejemplos de marcas</span>
        </div>
        <div class="ejemplos-grid">
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Truper'">
                <i class="fas fa-trademark"></i>
                <span>Truper</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Pretul'">
                <i class="fas fa-trademark"></i>
                <span>Pretul</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Volteck'">
                <i class="fas fa-trademark"></i>
                <span>Volteck</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Stanley'">
                <i class="fas fa-trademark"></i>
                <span>Stanley</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Bosch'">
                <i class="fas fa-trademark"></i>
                <span>Bosch</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Makita'">
                <i class="fas fa-trademark"></i>
                <span>Makita</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = '3M'">
                <i class="fas fa-trademark"></i>
                <span>3M</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Cruz Azul'">
                <i class="fas fa-trademark"></i>
                <span>Cruz Azul</span>
            </div>
            <div class="ejemplo-item" onclick="document.querySelector('input[name=\"nombre\"]').value = 'Comex'">
                <i class="fas fa-trademark"></i>
                <span>Comex</span>
            </div>
        </div>
    </div>
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

/* Ejemplos de marcas */
.ejemplos-container {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
}

.ejemplos-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: var(--gray-600);
    font-size: 0.9rem;
}

.ejemplos-header i {
    color: var(--warning);
}

.ejemplos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.ejemplo-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background-color: var(--gray-50);
    border-radius: var(--radius-md);
    color: var(--gray-700);
    font-size: 0.8rem;
    transition: all 0.2s;
    cursor: pointer;
}

.ejemplo-item:hover {
    background-color: var(--primary-alpha);
    color: var(--primary);
    transform: translateX(4px);
}

.ejemplo-item i {
    font-size: 0.9rem;
    color: var(--primary);
    width: 1.2rem;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .ejemplos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .ejemplos-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Hacer que los ejemplos sean clickeables y llenen el campo
document.querySelectorAll('.ejemplo-item').forEach(item => {
    item.addEventListener('click', function() {
        const nombre = this.querySelector('span').textContent;
        document.querySelector('input[name="nombre"]').value = nombre;
        document.querySelector('input[name="nombre"]').focus();
    });
});
</script>

<?php include '../footer.php'; ?>