<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ventas - Iniciar Sesión</title>
    
    <!-- CSS Madre -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/login.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Swiper CSS para el carrusel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body class="login-body-moderno">
    <div class="login-moderno-container">
        <!-- Lado izquierdo - Carrusel de imágenes -->
        <div class="login-carrusel">
            <div class="carrusel-overlay"></div>
            <div class="swiper loginSwiper">
                <div class="swiper-wrapper">
                    <!-- Slide 1 - Ferretería -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1581539250439-c96689b516dd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                        <div class="slide-content">
                            <i class="fas fa-tools"></i>
                            <h3>Gestión de Inventario</h3>
                            <p>Controla tu stock en tiempo real</p>
                        </div>
                    </div>
                    <!-- Slide 2 - Ventas -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                        <div class="slide-content">
                            <i class="fas fa-cash-register"></i>
                            <h3>Punto de Venta</h3>
                            <p>Ventas rápidas y eficientes</p>
                        </div>
                    </div>
                    <!-- Slide 3 - Clientes -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1556740714-a8395b3bf30f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                        <div class="slide-content">
                            <i class="fas fa-users"></i>
                            <h3>Gestión de Clientes</h3>
                            <p>Administra tu cartera de clientes</p>
                        </div>
                    </div>
                    <!-- Slide 4 - Reportes -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                        <div class="slide-content">
                            <i class="fas fa-chart-line"></i>
                            <h3>Reportes y Estadísticas</h3>
                            <p>Toma decisiones basadas en datos</p>
                        </div>
                    </div>
                </div>
                <!-- Paginación del carrusel -->
                <div class="swiper-pagination"></div>
            </div>
            <!-- Texto de crédito -->
            <div class="carrusel-credito">
                <p>Programador Fantasma</p>
            </div>
        </div>
        
        <!-- Lado derecho - Formulario de login -->
        <div class="login-moderno-form">
            <div class="login-moderno-header">
                <div class="login-moderno-logo">
                    <i class="fas fa-store"></i>
                </div>
                <h1>Sistema de Ventas</h1>
                <p>Inicia sesión para continuar</p>
            </div>
            
            <!-- Mensajes de alerta (ejemplos comentados) -->
            <!-- 
            <div class="alerta error">
                <i class="fas fa-exclamation-circle"></i>
                <p>Usuario o contraseña incorrectos</p>
            </div>
            
            <div class="alerta success">
                <i class="fas fa-check-circle"></i>
                <p>Sesión cerrada correctamente</p>
            </div>
            -->
            
            <form method="POST" class="login-moderno-form-element">
                <div class="form-group">
                    <label for="usuario" class="form-label">
                        <i class="fas fa-user"></i>
                        Usuario
                    </label>
                    <div class="input-wrapper">
                        <input type="text" id="usuario" name="usuario" class="form-input" 
                               placeholder="Ingresa tu usuario" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Ingresa tu contraseña" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="recordar" class="checkbox-input">
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">Recordarme</span>
                    </label>
                    <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="login-moderno-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
                
                <div class="login-moderno-footer">
                    <p>¿No tienes una cuenta? <a href="#">Contacta al administrador</a></p>
                </div>
            </form>
            
            <div class="login-moderno-version">
                <p>Versión 1.0.0</p>
            </div>
        </div>
    </div>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
    // Inicializar carrusel
    const swiper = new Swiper('.loginSwiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
    });
    
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    
    // Animación de entrada para los inputs
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    </script>
</body>
</html>