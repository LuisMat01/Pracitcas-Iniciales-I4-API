<?php

session_start();
require_once dirname(__DIR__) . '/php/config.php';

// Cerrar sesión en el backend real
if (isset($_SESSION['auth_token'])) {
    api_request('/auth/logout', 'POST', null, true);
}

// Limpiar cookies de sesión
unset($_SESSION['cookies']);
unset($_SESSION['auth_token']);

// Destruir sesión
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header('Location: /Fronted/PRACTICAS_INICIALES_AUTENTICACI-N/login.html');
exit;
?>