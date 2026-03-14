<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

$error = '';
$success = '';
$cliente_id = null;

// Valores del formulario (para persistencia)
$nombre = '';
$apellidos = '';
$tipo_documento = 'INE';
$documento = '';
$email = '';
$telefono = '';
$direccion = '';
$fecha_nacimiento = '';
$notas = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $tipo_documento = $_POST['tipo_documento'] ?? 'INE';
    $documento = trim($_POST['documento'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $notas = trim($_POST['notas'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre es obligatorio';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email no válido';
    } elseif (!empty($telefono) && !preg_match('/^[0-9-]+$/', $telefono)) {
        $error = 'Teléfono no válido (solo números y guiones)';
    } else {
        // Verificar si ya existe un cliente con ese documento (si se proporcionó)
        if (!empty($documento)) {
            $stmt = $conn->prepare("SELECT id FROM clientes WHERE documento = ?");
            $stmt->bind_param("s", $documento);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Ya existe un cliente con ese documento';
            }
        }
        
        // Verificar si ya existe un cliente con ese email (si se proporcionó)
        if (empty($error) && !empty($email)) {
            $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Ya existe un cliente con ese email';
            }
        }
        
        if (empty($error)) {
            // Insertar nuevo cliente
            $stmt = $conn->prepare("
                INSERT INTO clientes (
                    tipo_documento, documento, nombre, apellidos, email, 
                    telefono, direccion, fecha_nacimiento, notas
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param(
                "sssssssss",
                $tipo_documento,
                $documento,
                $nombre,
                $apellidos,
                $email,
                $telefono,
                $direccion,
                $fecha_nacimiento,
                $notas
            );
            
            if ($stmt->execute()) {
                $cliente_id = $conn->insert_id;
                $_SESSION['success'] = "Cliente registrado correctamente";
                $_SESSION['cliente_id'] = $cliente_id;
                header('Location: ' . url('dashboard/clientes/index.php'));
                exit;
            } else {
                $error = 'Error al registrar el cliente: ' . $conn->error;
            }
        }
    }
}

// Mostrar mensajes de sesión
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
    $cliente_id = $_SESSION['cliente_id'] ?? null;
    unset($_SESSION['cliente_id']);
}

include '../header.php';
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
        <a href="<?php echo url('dashboard/clientes/index.php'); ?>" class="btn-header" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i>
            Volver a Clientes
        </a>
    </div>
</div>

<!-- Mensajes de alerta -->
<?php if ($error): ?>
    <div class="alerta error">
        <i class="fas fa-exclamation-circle"></i>
        <p><?php echo h($error); ?></p>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alerta success">
        <i class="fas fa-check-circle"></i>
        <div class="alerta-content">
            <p><?php echo $success; ?></p>
            <div class="alerta-acciones">
                <a href="<?php echo url('dashboard/clientes/nuevo.php'); ?>" class="btn-primary small">
                    <i class="fas fa-plus"></i>
                    Otro Cliente
                </a>
                <a href="<?php echo url('dashboard/clientes/index.php'); ?>" class="btn-secondary small">
                    <i class="fas fa-list"></i>
                    Ver Listado
                </a>
                <?php if ($cliente_id): ?>
                    <a href="<?php echo url('dashboard/clientes/ver.php?id=' . $cliente_id); ?>" class="btn-tertiary small">
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
                    <!-- Tipo y número de documento -->
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-id-card"></i>
                                Tipo de documento
                            </label>
                            <select name="tipo_documento" class="form-select">
                                <option value="INE" <?php echo $tipo_documento == 'INE' ? 'selected' : ''; ?>>🇲🇽 INE</option>
                                <option value="CURP" <?php echo $tipo_documento == 'CURP' ? 'selected' : ''; ?>>📄 CURP</option>
                                <option value="RFC" <?php echo $tipo_documento == 'RFC' ? 'selected' : ''; ?>>🏢 RFC</option>
                                <option value="PASAPORTE" <?php echo $tipo_documento == 'PASAPORTE' ? 'selected' : ''; ?>>🌎 Pasaporte</option>
                            </select>
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                Número de documento
                            </label>
                            <input type="text" name="documento" class="form-input" 
                                   placeholder="Ej: ABCD123456" 
                                   value="<?php echo h($documento); ?>">
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
                                   value="<?php echo h($nombre); ?>">
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                Apellidos
                            </label>
                            <input type="text" name="apellidos" class="form-input" 
                                   placeholder="Ej: Pérez García"
                                   value="<?php echo h($apellidos); ?>">
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
                                   value="<?php echo h($email); ?>">
                        </div>
                        
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-phone-alt"></i>
                                Teléfono
                            </label>
                            <input type="tel" name="telefono" class="form-input" 
                                   placeholder="Ej: 55-1234-5678"
                                   value="<?php echo h($telefono); ?>">
                        </div>
                    </div>
                    
                    <!-- Dirección -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Dirección
                        </label>
                        <textarea name="direccion" class="form-textarea" rows="2" 
                                  placeholder="Calle, número, colonia, ciudad, código postal..."><?php echo h($direccion); ?></textarea>
                    </div>
                    
                    <!-- Fecha de nacimiento -->
                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha de nacimiento
                            </label>
                            <input type="date" name="fecha_nacimiento" class="form-input" 
                                   value="<?php echo h($fecha_nacimiento); ?>">
                        </div>
                    </div>
                    
                    <!-- Notas -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i>
                            Notas adicionales
                        </label>
                        <textarea name="notas" class="form-textarea" rows="3" 
                                  placeholder="Información relevante sobre el cliente..."><?php echo h($notas); ?></textarea>
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
                    
                    <a href="<?php echo url('dashboard/clientes/index.php'); ?>" class="btn-cancel">
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
// Validación de email en tiempo real
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

// Prevenir envío si hay errores
document.querySelector('.cliente-form')?.addEventListener('submit', function(e) {
    const nombre = document.querySelector('input[name="nombre"]').value.trim();
    
    if (!nombre) {
        e.preventDefault();
        alert('❌ El nombre es obligatorio');
        return;
    }
});
</script>

<?php include '../footer.php'; ?>