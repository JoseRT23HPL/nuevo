<?php
require_once 'config.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . url('dashboard/index.php'));
    exit;
}

$error = '';
$username = '';

// Verificar si hay un mensaje de error guardado en sesión
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    $username = $_SESSION['login_username'] ?? '';
    // Limpiar el mensaje después de mostrarlo
    unset($_SESSION['login_error']);
    unset($_SESSION['login_username']);
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Usuario y contraseña son obligatorios';
        $_SESSION['login_username'] = $username;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    $conn = getDB();
    
    if ($conn) {
        // Buscar usuario por username
        $stmt = $conn->prepare("SELECT id, username, password, nombre, rol FROM usuarios WHERE username = ? AND activo = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);
                
                // Guardar datos en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol'];
                
                // Redirigir al dashboard
                header('Location: ' . url('dashboard/index.php'));
                exit;
            } else {
                $_SESSION['login_error'] = 'Contraseña incorrecta';
                $_SESSION['login_username'] = $username;
            }
        } else {
            $_SESSION['login_error'] = 'Usuario no encontrado';
            $_SESSION['login_username'] = $username;
        }
    } else {
        $_SESSION['login_error'] = 'Error de conexión a la base de datos';
        $_SESSION['login_username'] = $username;
    }
    
    // Redirigir a la misma página para evitar reenvío del formulario
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Iniciar Sesión</title>
    
    <!-- CSS Madre -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body class="login-body-moderno">
    <div class="login-moderno-container">
        <!-- Lado izquierdo - Carrusel -->
        <div class="login-carrusel">
            <div class="carrusel-overlay"></div>
            <div class="swiper loginSwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1581539250439-c96689b516dd?auto=format&fit=crop&w=2070&q=80');">
                        <div class="slide-content">
                            <i class="fas fa-tools"></i>
                            <h3>Gestión de Inventario</h3>
                            <p>Controla tu stock en tiempo real</p>
                        </div>
                    </div>
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=2070&q=80');">
                        <div class="slide-content">
                            <i class="fas fa-cash-register"></i>
                            <h3>Punto de Venta</h3>
                            <p>Ventas rápidas y eficientes</p>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
            <div class="carrusel-credito">
                <p>Programador Fantasma</p>
            </div>
        </div>
        
        <!-- Lado derecho - Formulario -->
        <div class="login-moderno-form">
            <div class="login-moderno-header">
                <div class="login-moderno-logo">
                    <i class="fas fa-store"></i>
                </div>
                <h1><?php echo APP_NAME; ?></h1>
                <p>Inicia sesión para continuar</p>
            </div>
            
            <!-- Mostrar mensaje de error si existe -->
            <?php if (!empty($error)): ?>
            <div class="alerta error">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="login-moderno-form-element">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <div class="input-wrapper">
                        <input type="text" name="usuario" class="form-input" 
                               placeholder="Ingresa tu usuario" 
                               value="<?php echo htmlspecialchars($username); ?>"
                               required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" class="form-input" 
                               placeholder="Ingresa tu contraseña" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="login-moderno-button">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="login-moderno-version">
                <p>Versión <?php echo APP_VERSION; ?></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    const swiper = new Swiper('.loginSwiper', {
        loop: true,
        autoplay: { delay: 5000 },
        pagination: { el: '.swiper-pagination', clickable: true },
        effect: 'fade',
    });
    
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>