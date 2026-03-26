<?php
session_start();
require_once "config/api.php";

// Si ya hay token → redirigir
if (isset($_SESSION['auth_token'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || strlen($password) < 6) {
        $_SESSION['error'] = 'Correo o contraseña inválidos';
        header('Location: ../PRACTICAS_INICIALES_AUTENTICACI-N/login.html');
        exit;
    }

    $resultado = api_request('/auth/login', 'POST', [
        'email' => $email,
        'password' => $password
    ], false);

    if ($resultado['success'] && isset($resultado['data']['token'])) {
        $_SESSION['auth_token'] = $resultado['data']['token'];
        $_SESSION['user'] = $resultado['data']['user'] ?? null;
        $_SESSION['exito'] = '¡Bienvenido!';
        header('Location: ../index.php');
        exit;
    }

    $_SESSION['error'] = $resultado['error'] ?? 'Correo o contraseña incorrectos';
    header('Location: ../PRACTICAS_INICIALES_AUTENTICACI-N/login.html');
}