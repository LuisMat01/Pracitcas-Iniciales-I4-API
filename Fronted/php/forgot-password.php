<?php

require_once dirname(__DIR__) . '/php/config.php';
require_once 'auth.php';

redirigir_si_autenticado();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registro = preg_replace('/[^0-9]/', '', $_POST['registro'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $nueva_password = $_POST['nueva_password'] ?? '';
    
    $errores = [];
    if (strlen($registro) !== 9) $errores[] = 'El carnet debe tener 9 dígitos';
    if (!$email) $errores[] = 'Correo electrónico inválido';
    if (strlen($nueva_password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    
    if (!empty($errores)) {
        $_SESSION['error'] = implode('<br>', $errores);
        header('Location: ' . url('/recuperar.html'));
        exit;
    }
    
    // ============================================
    // PETICIÓN AL BACKEND REAL EN PUERTO 3000
    // ============================================
    
    $resultado = api_request('/auth/reset-password', 'POST', [
        'registroAcademico' => $registro,
        'email' => $email,
        'newPassword' => $nueva_password
    ], false);
    
    if ($resultado['success']) {
        $_SESSION['exito'] = 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.';
       header('Location: /Fronted/PRACTICAS_INICIALES_AUTENTICACI-N/login.html');
        exit;
    }
    
    $_SESSION['error'] = $resultado['error'] ?? 'Error al recuperar contraseña';
   header('Location: /Fronted/PRACTICAS_INICIALES_AUTENTICACI-N/recuperar.html');
    exit;
}
?>