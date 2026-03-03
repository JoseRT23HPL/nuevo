<?php
include '../header.php';
?>

<!-- Header del formulario -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-plus-circle" style="color: var(--primary);"></i>
            <h1>Nueva Categoría</h1>
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
        <p>Ya existe una categoría con ese nombre</p>
    </div>
    
    <div class="alerta success">
        <i class="fas fa-check-circle"></i>
        <div class="alerta-content">
            <p>Categoría creada correctamente</p>
            <a href="/dashboard/categorias/index.php" class="btn-primary small" style="margin-top: 0.5rem;">
                ← Volver a categorías
            </a>
        </div>
    </div>
    -->
    
    <!-- Formulario de nueva categoría -->
    <div class="categoria-form-wrapper">
        <form method="POST" class="categoria-form">
            <div class="form-icon-header">
                <i class="fas fa-tag"></i>
                <h3>Información de la categoría</h3>
            </div>
            
            <!-- Nombre de la categoría -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-heading"></i>
                    Nombre de la categoría <span class="required">*</span>
                </label>
                <input type="text" name="nombre" class="form-input" 
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
                          placeholder="Describe brevemente esta categoría..."></textarea>
                <small class="form-hint">Opcional - Una breve descripción de la categoría</small>
            </div>
            
            <!-- Información adicional -->
            <div class="info-box" style="margin-bottom: 2rem;">
                <i class="fas fa-info-circle"></i>
                <div class="info-box-content">
                    <h4>Información importante</h4>
                    <p>El nombre de la categoría debe ser único en el sistema. Las categorías te ayudan a organizar mejor tus productos.</p>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Guardar Categoría
                </button>
                
                <a href="/dashboard/categorias/index.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Ejemplos de categorías de ferretería -->
    <div class="ejemplos-container" style="margin-top: 2rem;">
        <div class="ejemplos-header">
            <i class="fas fa-lightbulb"></i>
            <span>Ejemplos de categorías</span>
        </div>
        <div class="ejemplos-grid">
            <div class="ejemplo-item">
                <i class="fas fa-wrench"></i>
                <span>Herramientas Manuales</span>
            </div>
            <div class="ejemplo-item">
                <i class="fas fa-bolt"></i>
                <span>Herramientas Eléctricas</span>
            </div>
            <div class="ejemplo-item">
                <i class="fas fa-tools"></i>
                <span>Materiales Construcción</span>
            </div>
            <div class="ejemplo-item">
                <i class="fas fa-water"></i>
                <span>Tubería y Conexiones</span>
            </div>
            <div class="ejemplo-item">
                <i class="fas fa-paint-brush"></i>
                <span>Pinturas</span>
            </div>
            <div class="ejemplo-item">
                <i class="fas fa-bolt"></i>
                <span>Electricidad</span>
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

.categoria-form {
    animation: fadeInForm 0.3s ease-out;
}

/* Estilos específicos para el formulario de categorías */
.categoria-form-wrapper {
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

/* Ejemplos de categorías */
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

<?php include '../footer.php'; ?>