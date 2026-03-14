<?php
require_once '../../config.php';
requiereAuth();

// Solo super_admin puede editar usuarios
if (!hasRole('super_admin')) {
    header('Location: ' . url('dashboard/index.php'));
    exit;
}

$conn = getDB();

// Obtener ID del usuario a editar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . url('dashboard/usuarios/index.php'));
    exit;
}

// Obtener datos del usuario actual
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header('Location: ' . url('dashboard/usuarios/index.php'));
    exit;
}

// Variables para permisos
$usuario_id_actual = $_SESSION['user_id'];
$es_super_admin = hasRole('super_admin');
$puede_cambiar_rol = true;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = $_POST['rol'] ?? $usuario['rol'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (empty($email)) {
        $error = 'El email es obligatorio';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email no válido';
    } else {
        // Verificar si el email ya existe (excluyendo el usuario actual)
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'El email ya está registrado por otro usuario';
        } else {
            $stmt = $conn->prepare("
                UPDATE usuarios SET 
                    nombre = ?, 
                    email = ?, 
                    rol = ?, 
                    activo = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("sssii", $nombre_completo, $email, $rol, $activo, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Usuario actualizado correctamente";
                header('Location: ' . url('dashboard/usuarios/index.php'));
                exit;
            } else {
                $error = 'Error al actualizar el usuario: ' . $conn->error;
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
            <i class="fas fa-user-edit"></i>
            <h1>Editar Usuario</h1>
        </div>
        <span class="pv-badge">EDITAR</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/usuarios/ver.php?id=' . $usuario['id']); ?>" class="btn-header primary">
            <i class="fas fa-eye"></i>
            Ver usuario
        </a>
        <a href="<?php echo url('dashboard/usuarios/index.php'); ?>" class="btn-header">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Modifica la información del usuario</p>

<!-- Mensajes de alerta -->
<?php if ($error): ?>
<div class="alerta error">
    <i class="fas fa-exclamation-circle"></i>
    <p><?php echo h($error); ?></p>
</div>
<?php endif; ?>

<!-- Formulario de edición -->
<div class="form-container usuario-editar-form">
    <div class="cliente-form-wrapper">
        <div class="form-icon-header">
            <i class="fas fa-edit"></i>
            <h3>Editar información</h3>
        </div>
        
        <form method="POST" class="usuario-form" id="editarUsuarioForm">
            <!-- Nombre de usuario (solo lectura) -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-user"></i>
                    Nombre de usuario
                </label>
                <input type="text" 
                       value="<?php echo h($usuario['username']); ?>" 
                       disabled
                       class="form-input readonly">
                <small class="form-hint">El nombre de usuario no se puede cambiar</small>
            </div>
            
            <!-- Nombre completo -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-id-card"></i>
                    Nombre completo
                </label>
                <input type="text" name="nombre_completo" id="nombre_completo"
                       value="<?php echo h($usuario['nombre'] ?? ''); ?>"
                       placeholder="Ej: Juan Pérez"
                       class="form-input">
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-envelope"></i>
                    Email <span class="required">*</span>
                </label>
                <input type="email" name="email" id="email" required
                       value="<?php echo h($usuario['email']); ?>"
                       placeholder="usuario@ejemplo.com"
                       class="form-input">
            </div>
            
            <!-- Rol -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-tag"></i>
                    Rol
                </label>
                <select name="rol" id="rol" class="form-select" <?php echo $usuario['id'] == 1 ? 'disabled' : ''; ?>>
                    <option value="cajero" <?php echo $usuario['rol'] == 'cajero' ? 'selected' : ''; ?>>Cajero</option>
                    <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    <?php if ($es_super_admin): ?>
                        <option value="super_admin" <?php echo $usuario['rol'] == 'super_admin' ? 'selected' : ''; ?>>Super Administrador</option>
                    <?php endif; ?>
                </select>
                <?php if ($usuario['id'] == 1): ?>
                    <small class="form-hint" style="color: var(--warning);">El Super Administrador principal no puede cambiar de rol</small>
                <?php endif; ?>
            </div>
            
            <!-- Estado (checkbox simple) -->
            <?php if ($usuario['id'] != 1): ?>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="activo" class="checkbox-input" 
                           <?php echo $usuario['activo'] ? 'checked' : ''; ?>>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Usuario activo</span>
                </label>
                <small class="form-hint">Si está inactivo, no podrá iniciar sesión</small>
            </div>
            <?php endif; ?>
            
            <!-- Campos obligatorios -->
            <p class="campos-obligatorios">
                <i class="fas fa-asterisk"></i>
                <span>Campos obligatorios</span>
            </p>
            
            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-submit" id="btnSubmit">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
                
                <a href="<?php echo url('dashboard/usuarios/index.php'); ?>" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
        
        <?php if ($usuario_id_actual == $usuario['id']): ?>
            <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200);">
                <a href="<?php echo url('dashboard/usuarios/cambiar_password.php'); ?>" class="btn-secondary">
                    <i class="fas fa-key"></i>
                    Cambiar mi contraseña
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Información adicional -->
<div class="info-box" style="margin-top: 1.5rem;">
    <i class="fas fa-info-circle"></i>
    <div class="info-box-content">
        <h4>Información importante</h4>
        <p>• El email debe ser único en el sistema<br>
        • Los cambios de rol afectan los permisos inmediatamente<br>
        • Un usuario inactivo no podrá iniciar sesión</p>
    </div>
</div>

<script>
// Validación de email simple
document.getElementById('email').addEventListener('input', function() {
    const email = this.value;
    if (email && !email.includes('@')) {
        this.style.borderColor = 'var(--danger)';
    } else {
        this.style.borderColor = '';
    }
});
</script>

<?php include '../footer.php'; ?>