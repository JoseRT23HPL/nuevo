<?php
require_once 'config.php';

$id = 2; // Cambia por el ID de tu producto
$conn = getDB();
$result = $conn->query("SELECT imagen_url FROM productos WHERE id = $id");
$row = $result->fetch_assoc();

echo "Ruta en BD: " . $row['imagen_url'] . "<br>";

$carpeta = BASE_PATH . '/assets/images/productos/';
$archivos = scandir($carpeta);

echo "Archivos en la carpeta:<br>";
foreach ($archivos as $archivo) {
    if (strpos($archivo, 'producto_1772568157') !== false) {
        echo "✅ ENCONTRADO: " . $archivo . "<br>";
        echo "Extensión: " . pathinfo($archivo, PATHINFO_EXTENSION) . "<br>";
    }
}
?>