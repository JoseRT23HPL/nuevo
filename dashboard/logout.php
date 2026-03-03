<?php
// dashboard/logout.php
require_once '../config.php';

// Destruir la sesión
session_destroy();

// Redireccionar al login
redirect('index.php');
?>