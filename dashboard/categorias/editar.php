<?php
include '../header.php';

// Datos de ejemplo para la categoría a editar
$categoria = [
    'id' => 3,
    'nombre' => 'Materiales de Construcción',
    'descripcion' => 'Cemento, varilla, block, arena, grava y todos los materiales para obra',
    'activo' => true
];
?>

<!-- Header del formulario -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-edit" style="color: var(--primary);"></i>
            <h1>Editar Categoría</h1>
        </div>
        <span class="pv-badge">FERREFÁCIL</span>
    </div>
    
    <div class="pv-header-right">
        <a href="/dashboard/categorias/index.php" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver a categorías
        </a>
    </div>
</div>

<!-- Contenedor del formulario -->
<div class="form-container" style="max-width: 600px; margin: 0 auto;">
    <!-- Mensajes de alerta (ejemplos comentados) -->
    <!-- 
    <div class="alerta error">
        <i class="fas fa-exclamation-circle"></i>
        <p>Ya existe otra categoría con ese nombre</p>
    </div>
    
    <div class="alerta success">
        <i class="fas fa-check-circle"></i>
        <p>Categoría actualizada correctamente</p>
    </div>
    -->
    
    <!-- Formulario de edición de categoría -->
    <div class="categoria-form-wrapper">
        <div class="form-icon-header">
            <i class="fas fa-tag"></i>
            <h3>Editando: <?php echo $categoria['nombre']; ?></h3>
        </div>
        
        <form method="POST" class="categoria-form">
            <!-- ID oculto (para el ejemplo) -->
            <input type="hidden" name="id" value="<?php echo $categoria['id']; ?>">
            
            <!-- Nombre de la categoría -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-heading"></i>
                    Nombre de la categoría <span class="required">*</span>
                </label>
                <input type="text" name="nombre" class="form-input" 
                       value="<?php echo $categoria['nombre']; ?>" 
                       placeholder="Ej: Herramientas Manuales" required autofocus>
                <small class="form-hint">Nombre único para identificar la categoría</small>
            </div>
            
            <!-- Descripción -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left"></i>
                    Descripción
                </label>
                <textarea name="descripcion" class="form-textarea" rows="4" 
                          placeholder="Describe brevemente esta categoría..."><?php echo $categoria['descripcion']; ?></textarea>
                <small class="form-hint">Opcional - Una breve descripción de la categoría</small>
            </div>
            
            <!-- Estado activo/inactivo (checkbox personalizado) -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="activo" class="checkbox-input" 
                           <?php echo $categoria['activo'] ? 'checked' : ''; ?>>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Categoría activa</span>
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
                    <p>El nombre de la categoría debe ser único en el sistema. Al desactivar una categoría, los productos existentes mantendrán su clasificación pero no podrás asignarla a nuevos productos.</p>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
                
                <a href="/dashboard/categorias/index.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Información de productos asociados -->
    <div class="productos-asociados" style="margin-top: 2rem;">
        <div class="productos-header">
            <i class="fas fa-boxes"></i>
            <span>Productos en esta categoría</span>
        </div>
        
        <div class="productos-stats">
            <div class="stat-item">
                <span class="stat-label">Total productos:</span>
                <span class="stat-value">32</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Con stock:</span>
                <span class="stat-value success">28</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Agotados:</span>
                <span class="stat-value warning">4</span>
            </div>
        </div>
        
        <div class="productos-lista-preview">
            <h4>Últimos productos agregados:</h4>
            <ul>
                <li><i class="fas fa-cube"></i> Cemento Portland Gris 50kg</li>
                <li><i class="fas fa-cube"></i> Varilla Corrugada 3/8" x 12m</li>
                <li><i class="fas fa-cube"></i> Block Hueco 15x20x40</li>
                <li><i class="fas fa-cube"></i> Arena Fina x m³</li>
            </ul>
            <a href="/dashboard/productos/index.php?categoria=<?php echo $categoria['id']; ?>" class="ver-mas">
                Ver todos los productos →
            </a>
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

.categoria-form {
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

/* Productos asociados */
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

/* Responsive */
@media (max-width: 768px) {
    .categoria-form-wrapper {
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