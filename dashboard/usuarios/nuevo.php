<?php
require_once '../../config.php';
requiereAuth();

// Solo super_admin puede crear usuarios
if (!hasRole('super_admin')) {
    header('Location: ' . url('dashboard/index.php'));
    exit;
}

$conn = getDB();

$error = '';
$success = '';
$username = '';
$nombre_completo = '';
$email = '';
$rol = 'cajero'; // ← VALOR POR DEFECTO PARA EVITAR EL WARNING

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'] ?? 'cajero';
    
    // Validaciones
    if (empty($username)) {
        $error = 'El nombre de usuario es obligatorio';
    } elseif (empty($email)) {
        $error = 'El email es obligatorio';
    } elseif (empty($password)) {
        $error = 'La contraseña es obligatoria';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email no válido';
    } else {
        // Verificar si el username ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'El nombre de usuario ya está registrado';
        } else {
            // Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'El email ya está registrado';
            } else {
                // Hash de la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                $stmt = $conn->prepare("
                    INSERT INTO usuarios (username, password, nombre, email, rol) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("sssss", $username, $password_hash, $nombre_completo, $email, $rol);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Usuario creado correctamente";
                    header('Location: ' . url('dashboard/usuarios/index.php'));
                    exit;
                } else {
                    $error = 'Error al crear el usuario: ' . $conn->error;
                }
            }
        }
    }
}

include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-user-plus"></i>
            <h1>Nuevo Usuario</h1>
        </div>
        <span class="pv-badge">CREAR</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/usuarios/index.php'); ?>" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver a Usuarios
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Crea un nuevo usuario para el sistema</p>

<!-- Mensajes de alerta -->
<?php if ($error): ?>
<div class="alerta error">
    <i class="fas fa-exclamation-circle"></i>
    <p><?php echo h($error); ?></p>
</div>
<?php endif; ?>

<!-- Formulario de nuevo usuario -->
<div class="form-container">
    <div class="cliente-form-wrapper usuario-form-wrapper">
        <div class="form-icon-header">
            <i class="fas fa-user-circle"></i>
            <h3>Información del Usuario</h3>
        </div>
        
        <form method="POST" class="usuario-form" id="usuarioForm">
            <!-- Nombre de usuario -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-user"></i>
                    Nombre de usuario <span class="required">*</span>
                </label>
                <input type="text" name="username" id="username" class="form-input" required autofocus
                       placeholder="ej: juan.perez"
                       value="<?php echo h($username); ?>">
            </div>
            
            <!-- Nombre completo -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-id-card"></i>
                    Nombre completo
                </label>
                <input type="text" name="nombre_completo" id="nombre_completo" class="form-input" 
                       placeholder="Juan Pérez García"
                       value="<?php echo h($nombre_completo); ?>">
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-envelope"></i>
                    Email <span class="required">*</span>
                </label>
                <input type="email" name="email" id="email" class="form-input" required
                       placeholder="usuario@ejemplo.com"
                       value="<?php echo h($email); ?>">
            </div>
            
            <!-- Contraseña -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i>
                    Contraseña <span class="required">*</span>
                </label>
                <input type="password" name="password" id="password" class="form-input" required minlength="6"
                       placeholder="••••••••">
                
                <!-- Barra de fortaleza -->
                <div class="password-strength">
                    <div class="strength-bar-container">
                        <div id="strengthBar" class="strength-bar" style="width: 0%;"></div>
                    </div>
                    <p class="strength-hint">Mínimo 6 caracteres</p>
                </div>
            </div>
            
            <!-- Confirmar contraseña -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-check-circle"></i>
                    Confirmar contraseña <span class="required">*</span>
                </label>
                <input type="password" name="confirm_password" id="confirm" class="form-input" required
                       placeholder="••••••••">
                <div id="matchMessage" class="match-message"></div>
            </div>
            
            <!-- Rol -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-tag"></i>
                    Rol <span class="required">*</span>
                </label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="cajero" <?php echo $rol === 'cajero' ? 'selected' : ''; ?>>Cajero</option>
                    <option value="admin" <?php echo $rol === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="super_admin" <?php echo $rol === 'super_admin' ? 'selected' : ''; ?>>Super Administrador</option>
                </select>
                
                <!-- Descripción de roles -->
                <div class="roles-info">
                    <div class="role-item">
                        <span class="role-badge cajero">Cajero</span>
                        <span class="role-desc">Solo puede hacer ventas y ver su corte</span>
                    </div>
                    <div class="role-item">
                        <span class="role-badge admin">Admin</span>
                        <span class="role-desc">Puede gestionar productos, usuarios y reportes</span>
                    </div>
                    <div class="role-item">
                        <span class="role-badge super-admin">Super Admin</span>
                        <span class="role-desc">Acceso total al sistema</span>
                    </div>
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
                    Crear Usuario
                </button>
                
                <a href="<?php echo url('dashboard/usuarios/index.php'); ?>" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Información adicional -->
    <div class="info-box" style="margin-top: 1.5rem;">
        <i class="fas fa-info-circle"></i>
        <div class="info-box-content">
            <h4>Información importante</h4>
            <ul>
                <li>• El nombre de usuario debe ser único</li>
                <li>• La contraseña debe tener al menos 6 caracteres</li>
                <li>• El email debe ser válido y único</li>
                <li>• Solo los Super Administradores pueden crear nuevos usuarios</li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Estilos adicionales para el medidor de fortaleza */
.password-strength {
    margin-top: 0.5rem;
}

.strength-bar-container {
    width: 100%;
    height: 6px;
    background-color: var(--gray-200);
    border-radius: 3px;
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0%;
    background-color: var(--gray-400);
    transition: all 0.3s ease;
}

.strength-hint {
    font-size: 0.7rem;
    color: var(--gray-500);
    margin-top: 0.25rem;
}

.match-message {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.match-message.success {
    color: var(--success);
}

.match-message.error {
    color: var(--danger);
}

/* Descripción de roles */
.roles-info {
    background-color: var(--gray-50);
    border-radius: var(--radius-md);
    padding: 1rem;
    margin-top: 0.75rem;
    border: 1px solid var(--gray-200);
}

.role-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px dashed var(--gray-200);
}

.role-item:last-child {
    border-bottom: none;
}

.role-badge {
    min-width: 100px;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-lg);
    font-size: 0.75rem;
    font-weight: 600;
    text-align: center;
}

.role-badge.cajero {
    background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
    color: var(--success);
    border: 1px solid #6ee7b7;
}

.role-badge.admin {
    background: linear-gradient(135deg, var(--info-light) 0%, #bfdbfe 100%);
    color: var(--info);
    border: 1px solid #93c5fd;
}

.role-badge.super-admin {
    background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
    color: #8b5cf6;
    border: 1px solid #c084fc;
}

.role-desc {
    font-size: 0.8rem;
    color: var(--gray-600);
}
</style>

<script>
// ===== MEDIDOR DE FORTALEZA DE CONTRASEÑA =====
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const bar = document.getElementById('strengthBar');
    let strength = 0;
    
    // Longitud
    if (password.length >= 6) strength += 25;
    if (password.length >= 8) strength += 10;
    
    // Complejidad
    if (password.match(/[a-z]/)) strength += 15;
    if (password.match(/[A-Z]/)) strength += 15;
    if (password.match(/[0-9]/)) strength += 15;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 20;
    
    // Limitar a 100
    strength = Math.min(strength, 100);
    
    bar.style.width = strength + '%';
    
    // Color según fortaleza
    if (strength < 40) {
        bar.style.backgroundColor = '#ef4444';
    } else if (strength < 60) {
        bar.style.backgroundColor = '#f59e0b';
    } else if (strength < 80) {
        bar.style.backgroundColor = '#3b82f6';
    } else {
        bar.style.backgroundColor = '#10b981';
    }
});

// ===== VERIFICAR QUE LAS CONTRASEÑAS COINCIDAN =====
document.getElementById('confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    const message = document.getElementById('matchMessage');
    
    if (password === confirm && password !== '') {
        message.innerHTML = '✅ Las contraseñas coinciden';
        message.classList.remove('error');
        message.classList.add('success');
    } else if (password !== confirm && confirm !== '') {
        message.innerHTML = '❌ Las contraseñas no coinciden';
        message.classList.remove('success');
        message.classList.add('error');
    } else {
        message.innerHTML = '';
    }
});

// ===== VALIDAR FORMULARIO ANTES DE ENVIAR =====
document.getElementById('usuarioForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const email = document.getElementById('email').value;
    const username = document.getElementById('username').value;
    const errorDiv = document.getElementById('errorMensaje');
    
    // Ocultar alertas anteriores
    if (errorDiv) errorDiv.style.display = 'none';
    
    // Validaciones adicionales
    if (password !== confirm) {
        e.preventDefault();
        mostrarAlerta('error', 'Las contraseñas no coinciden');
        return;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        mostrarAlerta('error', 'La contraseña debe tener al menos 6 caracteres');
        return;
    }
    
    if (!email.includes('@') || !email.includes('.')) {
        e.preventDefault();
        mostrarAlerta('error', 'Email no válido');
        return;
    }
    
    if (username.length < 3) {
        e.preventDefault();
        mostrarAlerta('error', 'El nombre de usuario debe tener al menos 3 caracteres');
        return;
    }
});

// ===== FUNCIÓN PARA MOSTRAR ALERTAS =====
function mostrarAlerta(tipo, mensaje) {
    const errorAlert = document.getElementById('errorMensaje');
    const successAlert = document.getElementById('successMensaje');
    const successTexto = document.getElementById('successTexto');
    
    if (errorAlert) errorAlert.style.display = 'none';
    if (successAlert) successAlert.style.display = 'none';
    
    if (tipo === 'error' && errorAlert) {
        errorAlert.querySelector('p').textContent = mensaje;
        errorAlert.style.display = 'flex';
        
        setTimeout(() => {
            errorAlert.style.display = 'none';
        }, 5000);
    } else if (tipo === 'success' && successAlert) {
        successTexto.textContent = mensaje;
        successAlert.style.display = 'flex';
        
        setTimeout(() => {
            successAlert.style.display = 'none';
        }, 5000);
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php include '../footer.php'; ?>