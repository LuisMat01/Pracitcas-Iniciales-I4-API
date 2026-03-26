<?php
session_start();
require_once "config/api.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registro = preg_replace('/[^0-9]/', '', $_POST['registro'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    $errores = [];
    if (strlen($registro) !== 9) $errores[] = 'Carnet debe tener 9 dígitos';
    if (strlen($nombres) < 2) $errores[] = 'Nombres requeridos';
    if (strlen($apellidos) < 2) $errores[] = 'Apellidos requeridos';
    if (!$email) $errores[] = 'Correo inválido';
    if (strlen($password) < 6) $errores[] = 'Contraseña mínimo 6 caracteres';

    if ($errores) {
        $_SESSION['error'] = implode('<br>', $errores);
        header('Location: ../PRACTICAS_INICIALES_AUTENTICACI-N/register.html');
        exit;
    }

    $resultado = api_request('/auth/register', 'POST', [
        'registroAcademico' => $registro,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'email' => $email,
        'password' => $password
    ], false);

    if ($resultado['success']) {
        // login automático
        $login = api_request('/auth/login', 'POST', [
            'email' => $email,
            'password' => $password
        ], false);

        if ($login['success'] && isset($login['data']['token'])) {
            $_SESSION['auth_token'] = $login['data']['token'];
            $_SESSION['user'] = $login['data']['user'] ?? null;
        }

        $_SESSION['exito'] = 'Registro exitoso';
        header('Location: ../index.php');
        exit;
    }

    $_SESSION['error'] = $resultado['error'] ?? 'Error en registro';
    header('Location: ../PRACTICAS_INICIALES_AUTENTICACI-N/register.html');
}