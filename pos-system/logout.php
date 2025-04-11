<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();
$result = $auth->logout();

// Redirigir al login
header('Location: /login.php?success=' . urlencode('Sesión cerrada correctamente'));
exit;
?>
