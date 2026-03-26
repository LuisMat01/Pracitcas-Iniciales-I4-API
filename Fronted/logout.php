<?php
include("config/api.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Llamar al endpoint de logout
apiRequest("POST", "/auth/logout");

// Limpiar cookies de sesión
unset($_SESSION['cookies']);
session_destroy();

// Redirigir al login
 header('Location: /Fronted/PRACTICAS_INICIALES_AUTENTICACI-N/login.html');
exit();
?>