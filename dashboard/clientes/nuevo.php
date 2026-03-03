<?php
include '../header.php';

// Variables para simular mensajes de éxito/error
$error = '';
$success = '';
$cliente_id = null;

// Simular registro exitoso (para pruebas)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulación de validación
    if (empty($_POST['nombre'])) {
        $error = 'El nombre es obligatorio';
    } elseif (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Email no válido';
    } else {
        $success = 'Cliente registrado correctamente';
        $cliente_id = 123; // ID simulado
    }
}
?>

<!-- Header del formulario -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-user-plus" style="color: var(--primary);"></i>
            <h1>Nuevo Cliente</h1>
        </div>
        <span class="pv-badge">CARTERA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="/dashboard/clientes/index.php" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver a Clientes
        </a>
    </div>
</div>

<!-- Mensajes de alerta -->
<?php if ($error): ?>
    <div class="alerta error">
        <i class="fas fa-exclamation-circle"></i>
        <p><?php echo $error; ?></p>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alerta success">
        <i class="fas fa-check-circle"></i>
        <div class="alerta-content">
            <p><?php echo $success; ?></p>
            <div class="alerta-acciones">
                <a href="/dashboard/clientes/nuevo.php" class="btn-primary small">
                    <i class="fas fa-plus"></i>
                    Otro Cliente
                </a>
                <a href="/dashboard/clientes/index.php" class="btn-secondary small">
                    <i class="fas fa-list"></i>
                    Ver Listado
                </a>
                <?php if ($cliente_id): ?>
                    <a href="/dashboard/clientes/ver.php?id=<?php echo $cliente_id; ?>" class="btn-tertiary small">
                        <i class="fas fa-eye"></i>
                        Ver Cliente
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!$success): ?>
    <!-- Formulario de nuevo cliente -->
    <div class="form-container" style="max-width: 800px; margin: 0 auto;">
        <div class="cliente-form-wrapper">
            <div class="form-icon-header">
                <i class="fas fa-user-circle"></i>
                <h3>Información del Cliente</h3>
            </div>
            
            <form method="POST" class="cliente-form">
                <div class="form-grid">
                    <!-- Tipo y número de documento (CORREGIDO: INE para México) -->
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-id-card"></i>
                                Tipo de documento
                            </label>
                            <select name="tipo_documento" class="form-select">
                                <option value="INE">🇲🇽 INE</option>
                                <option value="CURP">📄 CURP</option>
                                <option value="RFC">🏢 RFC</option>
                                <option value="PASAPORTE">🌎 Pasaporte</option>
                            </select>
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                Número de documento
                            </label>
                            <input type="text" name="documento" class="form-input" 
                                   placeholder="Ej: ABCD123456" (para INE, CURP o RFC)
                                   value="<?php echo htmlspecialchars($_POST['documento'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Nombre y apellidos -->
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-user"></i>
                                Nombre <span class="required">*</span>
                            </label>
                            <input type="text" name="nombre" class="form-input" required
                                   placeholder="Ej: Juan"
                                   value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                Apellidos
                            </label>
                            <input type="text" name="apellidos" class="form-input" 
                                   placeholder="Ej: Pérez García"
                                   value="<?php echo htmlspecialchars($_POST['apellidos'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Email y teléfono -->
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email
                            </label>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="cliente@ejemplo.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-phone-alt"></i>
                                Teléfono
                            </label>
                            <input type="tel" name="telefono" class="form-input" 
                                   placeholder="Ej: 55-1234-5678"
                                   value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Dirección -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Dirección
                        </label>
                        <textarea name="direccion" class="form-textarea" rows="2" 
                                  placeholder="Calle, número, colonia, ciudad, código postal..."><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Fecha de nacimiento -->
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha de nacimiento
                            </label>
                            <input type="date" name="fecha_nacimiento" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['fecha_nacimiento'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Notas -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i>
                            Notas adicionales
                        </label>
                        <textarea name="notas" class="form-textarea" rows="3" 
                                  placeholder="Información relevante sobre el cliente..."><?php echo htmlspecialchars($_POST['notas'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Campos obligatorios -->
                <p class="campos-obligatorios">
                    <span class="required">*</span> Campos obligatorios
                </p>
                
                <!-- Botones -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Guardar Cliente
                    </button>
                    
                    <a href="/dashboard/clientes/index.php" class="btn-cancel">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

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

.cliente-form {
    animation: fadeInForm 0.3s ease-out;
}
</style>

<script>
// Validación de email en tiempo real (opcional)
document.querySelector('input[name="email"]')?.addEventListener('blur', function() {
    const email = this.value;
    if (email && !email.includes('@')) {
        this.style.borderColor = 'var(--danger)';
        this.style.boxShadow = '0 0 0 3px var(--danger-alpha)';
    } else {
        this.style.borderColor = 'var(--gray-200)';
        this.style.boxShadow = 'none';
    }
});

// Formatear teléfono a formato mexicano (55-1234-5678)
document.querySelector('input[name="telefono"]')?.addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 10) value = value.slice(0, 10);
    if (value.length > 6) {
        value = value.slice(0, 2) + '-' + value.slice(2, 6) + '-' + value.slice(6);
    } else if (value.length > 2) {
        value = value.slice(0, 2) + '-' + value.slice(2);
    }
    this.value = value;
});

// Sugerir formato para INE/CURP/RFC
document.querySelector('select[name="tipo_documento"]')?.addEventListener('change', function() {
    const tipo = this.value;
    const inputDoc = document.querySelector('input[name="documento"]');
    
    switch(tipo) {
        case 'INE':
            inputDoc.placeholder = 'Ej: ABCD123456';
            break;
        case 'CURP':
            inputDoc.placeholder = 'Ej: ABCD123456HDFGRR01';
            break;
        case 'RFC':
            inputDoc.placeholder = 'Ej: ABCD123456XYZ';
            break;
        case 'PASAPORTE':
            inputDoc.placeholder = 'Ej: G12345678';
            break;
    }
});
</script>

<?php include '../footer.php'; ?>