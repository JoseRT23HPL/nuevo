<?php
// dashboard/ajax/soporte.php
require_once '../../config.php';
require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

requiereAuth();
header('Content-Type: application/json');

// Validar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar campos
$id_usuario = $_POST['id_usuario'] ?? 0;
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$tipo = $_POST['tipo'] ?? '';
$mensaje = trim($_POST['mensaje'] ?? '');

if (empty($nombre) || empty($email) || empty($tipo) || empty($mensaje)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

// Cargar configuración de email
$config = include '../../config/email.php';

try {
    $mail = new PHPMailer(true);
    
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host       = $config['smtp']['host'];
    $mail->SMTPAuth   = $config['smtp']['auth'];
    $mail->Username   = $config['smtp']['username'];
    $mail->Password   = $config['smtp']['password'];
    $mail->SMTPSecure = $config['smtp']['secure'];
    $mail->Port       = $config['smtp']['port'];
    $mail->CharSet    = 'UTF-8';
    
    // Remitente (el usuario que envió)
    $mail->setFrom($email, $nombre);
    
    // Destinatario (tu cuenta de soporte)
    $mail->addAddress($config['to']['email'], $config['to']['name']);
    
    // Responder a (el usuario)
    $mail->addReplyTo($email, $nombre);
    
    // Contenido
    $mail->isHTML(false);
    $mail->Subject = "Nuevo mensaje de soporte - $tipo";
    
    // Cuerpo del mensaje
    $cuerpo = "📋 TIPO: $tipo\n";
    $cuerpo .= "👤 NOMBRE: $nombre\n";
    $cuerpo .= "📧 EMAIL: $email\n";
    $cuerpo .= "🆔 ID USUARIO: $id_usuario\n";
    $cuerpo .= "📝 MENSAJE:\n$mensaje\n\n";
    $cuerpo .= "---\n";
    $cuerpo .= "Enviado: " . date('d/m/Y H:i:s');
    
    $mail->Body = $cuerpo;
    
    $mail->send();
    
    // Guardar en base de datos
    $conn = getDB();
    if ($conn) {
        $stmt = $conn->prepare("
            INSERT INTO soporte_mensajes (id_usuario, nombre, email, tipo, mensaje, fecha) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("issss", $id_usuario, $nombre, $email, $tipo, $mensaje);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => '✅ Mensaje enviado correctamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '❌ Error: ' . $mail->ErrorInfo]);
}