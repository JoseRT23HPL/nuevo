<?php
// ===== CONFIGURACIÓN DE RUTAS Y BASE DE DATOS =====

// Detectar entorno
$is_localhost = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1');

// Definir URL base
define('BASE_URL', $is_localhost ? 'http://localhost/nuevo' : 'https://tudominio.com');
define('BASE_PATH', dirname(__FILE__));

// Rutas importantes
define('ASSETS_URL', BASE_URL . '/assets');
define('DASHBOARD_URL', BASE_URL . '/dashboard');
define('LOGIN_URL', BASE_URL . '/index.php');

// Configuración de la app
define('APP_NAME', 'Mi Empresa');
define('APP_VERSION', '1.0.0');

// ===== CONFIGURACIÓN DE BASE DE DATOS =====
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ferreteria_db');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ===== FUNCIONES DE CONEXIÓN A BD =====

/**
 * Obtiene una conexión a la base de datos
 * @return mysqli|null
 */
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Error de conexión: " . $conn->connect_error);
            return null;
        }
        
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirecciona al login si no está autenticado
 */
function requiereAuth() {
    if (!isAuthenticated()) {
        redirect('index.php');
        exit;
    }
}

/**
 * Obtiene el usuario actual
 * @return array|null
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    $conn = getDB();
    if (!$conn) return null;
    
    $stmt = $conn->prepare("SELECT id, username, nombre, email, rol FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param string|array $roles
 * @return bool
 */
function hasRole($roles) {
    if (!isAuthenticated()) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['rol'], $roles);
}

// ===== FUNCIONES DE AYUDA EXISTENTES =====

// Para URLs
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Para assets (css, js, imágenes)
function asset($path) {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

// Para imágenes con fallback
function image($path, $fallback = 'images/no-image.png') {
    $fullPath = ASSETS_URL . '/' . ltrim($path, '/');
    return file_exists(BASE_PATH . '/assets/' . $path) ? $fullPath : asset($fallback);
}

// Para redireccionar
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

// ===== FUNCIÓN MEJORADA PARA DETECTAR PÁGINA ACTIVA =====
function isActive($patterns) {
    $current = $_SERVER['REQUEST_URI'];
    
    if (is_string($patterns)) {
        $patterns = [$patterns];
    }
    
    foreach ($patterns as $pattern) {
        if (strpos($current, $pattern) !== false) {
            $current_parts = explode('/', $current);
            $pattern_parts = explode('/', $pattern);
            
            if (count($pattern_parts) > 1) {
                return true;
            }
            
            $last_part = end($current_parts);
            if (strpos($last_part, $pattern) !== false) {
                return true;
            }
        }
    }
    return false;
}

// Función helper para obtener la clase active
function activeClass($patterns) {
    return isActive($patterns) ? 'active' : '';
}

/**
 * Escapa datos para HTML
 * @param string $data
 * @return string
 */
function h($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Formatea un precio
 * @param float $precio
 * @return string
 */
function formatoPrecio($precio) {
    return '$' . number_format($precio, 2);
}

/**
 * Genera un folio único para ventas
 * @return string
 */
function generarFolio() {
    return 'V-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Para depuración
function debug($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}
?>