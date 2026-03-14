<?php
// file: /dashboard/cambiar_password.php - VERSIÓN CSS PURO (SIN BACKEND)
include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-key"></i>
            <h1>Cambiar Contraseña</h1>
        </div>
        <span class="pv-badge">SEGURIDAD</span>
    </div>
    
    <div class="pv-header-right">
        <a href="perfil.php" class="btn-header primary">
            <i class="fas fa-user"></i>
            Mi Perfil
        </a>
        <a href="index.php" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Actualiza tu contraseña de acceso al sistema</p>

<!-- Mensajes de alerta (ocultos por defecto) -->
<div class="alerta error" style="display: none;" id="errorMensaje">
    <i class="fas fa-exclamation-circle"></i>
    <p>Error al procesar la solicitud</p>
</div>

<div class="alerta success" style="display: none;" id="successMensaje">
    <i class="fas fa-check-circle"></i>
    <p>Contraseña actualizada correctamente</p>
</div>

<div class="password-container">
    <div class="form-container password-form-container">
        <div class="cliente-form-wrapper">
            <div class="form-icon-header">
                <i class="fas fa-lock"></i>
                <h3>Actualizar Contraseña</h3>
            </div>
            
            <form method="POST" class="password-form" id="passwordForm">
                <!-- Contraseña actual -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i>
                        Contraseña actual <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password_actual" id="password_actual" class="form-input password-input" required
                               placeholder="••••••••">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_actual')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Nueva contraseña -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-key"></i>
                        Nueva contraseña <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="password_nueva" id="password_nueva" class="form-input password-input" required minlength="6"
                               placeholder="••••••••">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_nueva')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Barra de fortaleza -->
                    <div class="password-strength">
                        <div class="strength-bar-container">
                            <div id="strengthBarFill" class="strength-bar" style="width: 0%;"></div>
                        </div>
                        <p class="strength-hint">Mínimo 6 caracteres</p>
                    </div>
                </div>
                
                <!-- Confirmar contraseña -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-check-circle"></i>
                        Confirmar nueva contraseña <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <i class="fas fa-check-circle input-icon"></i>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-input password-input" required
                               placeholder="••••••••">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirm')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="matchMessage" class="match-message"></div>
                </div>
                
                <!-- Requisitos de contraseña -->
                <div class="password-requirements">
                    <p class="requirements-title">La contraseña debe tener:</p>
                    <div class="requirement-item" id="req-length">
                        <i class="fas fa-circle"></i>
                        <span>Mínimo 6 caracteres</span>
                    </div>
                    <div class="requirement-item" id="req-match">
                        <i class="fas fa-circle"></i>
                        <span>Las contraseñas coinciden</span>
                    </div>
                </div>
                
                <!-- Campos obligatorios -->
                <p class="campos-obligatorios">
                    <i class="fas fa-asterisk"></i>
                    <span>Campos obligatorios</span>
                </p>
                
                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <i class="fas fa-save"></i>
                        Actualizar Contraseña
                    </button>
                    
                    <a href="perfil.php" class="btn-cancel">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Información adicional -->
    <div class="info-box info-box-security">
        <i class="fas fa-shield-alt"></i>
        <div class="info-box-content">
            <h4>Recomendaciones de seguridad</h4>
            <ul>
                <li>• Usa una combinación de letras, números y símbolos</li>
                <li>• No uses contraseñas que hayas usado antes</li>
                <li>• Cambia tu contraseña periódicamente</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Función para mostrar/ocultar contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Medidor de fortaleza de contraseña
document.getElementById('password_nueva').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBarFill');
    const reqLength = document.getElementById('req-length');
    const iconLength = reqLength.querySelector('i');
    
    // Longitud
    if (password.length >= 6) {
        reqLength.classList.add('requirement-met');
        reqLength.classList.remove('requirement-unmet');
        iconLength.classList.remove('fa-circle');
        iconLength.classList.add('fa-check-circle');
    } else {
        reqLength.classList.remove('requirement-met');
        reqLength.classList.add('requirement-unmet');
        iconLength.classList.remove('fa-check-circle');
        iconLength.classList.add('fa-circle');
    }
    
    // Calcular fortaleza
    let strength = 0;
    if (password.length >= 6) strength += 40;
    if (password.length >= 8) strength += 10;
    if (password.match(/[A-Z]/)) strength += 15;
    if (password.match(/[0-9]/)) strength += 15;
    if (password.match(/[^A-Za-z0-9]/)) strength += 20;
    
    // Limitar a 100
    strength = Math.min(strength, 100);
    
    strengthBar.style.width = strength + '%';
    
    // Color según fortaleza
    if (strength < 40) {
        strengthBar.style.backgroundColor = '#ef4444';
    } else if (strength < 60) {
        strengthBar.style.backgroundColor = '#f59e0b';
    } else if (strength < 80) {
        strengthBar.style.backgroundColor = '#3b82f6';
    } else {
        strengthBar.style.backgroundColor = '#10b981';
    }
});

// Verificar que las contraseñas coincidan
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password_nueva').value;
    const confirm = this.value;
    const matchMessage = document.getElementById('matchMessage');
    const reqMatch = document.getElementById('req-match');
    const iconMatch = reqMatch.querySelector('i');
    
    if (password === confirm && password !== '') {
        matchMessage.innerHTML = '✅ Las contraseñas coinciden';
        matchMessage.classList.remove('error');
        matchMessage.classList.add('success');
        
        reqMatch.classList.add('requirement-met');
        reqMatch.classList.remove('requirement-unmet');
        iconMatch.classList.remove('fa-circle');
        iconMatch.classList.add('fa-check-circle');
    } else if (password !== confirm && confirm !== '') {
        matchMessage.innerHTML = '❌ Las contraseñas no coinciden';
        matchMessage.classList.remove('success');
        matchMessage.classList.add('error');
        
        reqMatch.classList.remove('requirement-met');
        reqMatch.classList.add('requirement-unmet');
        iconMatch.classList.remove('fa-check-circle');
        iconMatch.classList.add('fa-circle');
    } else {
        matchMessage.innerHTML = '';
        reqMatch.classList.remove('requirement-met', 'requirement-unmet');
        iconMatch.classList.remove('fa-check-circle');
        iconMatch.classList.add('fa-circle');
    }
});

// Validar formulario antes de enviar (simulado)
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const passwordActual = document.getElementById('password_actual').value;
    const passwordNueva = document.getElementById('password_nueva').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const errorAlert = document.getElementById('errorMensaje');
    const successAlert = document.getElementById('successMensaje');
    
    errorAlert.style.display = 'none';
    successAlert.style.display = 'none';
    
    // Validaciones simuladas
    if (!passwordActual || !passwordNueva || !passwordConfirm) {
        errorAlert.querySelector('p').textContent = 'Todos los campos son obligatorios';
        errorAlert.style.display = 'flex';
        setTimeout(() => { errorAlert.style.display = 'none'; }, 3000);
        return;
    }
    
    if (passwordNueva !== passwordConfirm) {
        errorAlert.querySelector('p').textContent = 'Las contraseñas nuevas no coinciden';
        errorAlert.style.display = 'flex';
        setTimeout(() => { errorAlert.style.display = 'none'; }, 3000);
        return;
    }
    
    if (passwordNueva.length < 6) {
        errorAlert.querySelector('p').textContent = 'La contraseña debe tener al menos 6 caracteres';
        errorAlert.style.display = 'flex';
        setTimeout(() => { errorAlert.style.display = 'none'; }, 3000);
        return;
    }
    
    // Simular contraseña actual incorrecta (solo para ejemplo)
    if (passwordActual !== '123456' && passwordActual !== 'admin123') {
        errorAlert.querySelector('p').textContent = 'La contraseña actual es incorrecta';
        errorAlert.style.display = 'flex';
        setTimeout(() => { errorAlert.style.display = 'none'; }, 3000);
        return;
    }
    
    // Mostrar éxito
    successAlert.style.display = 'flex';
    
    // Limpiar formulario
    this.reset();
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        successAlert.style.display = 'none';
    }, 3000);
    
    // Scroll hacia arriba
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

<?php include '../footer.php'; ?>