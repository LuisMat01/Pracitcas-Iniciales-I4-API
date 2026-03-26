<?php
require_once dirname(__DIR__) . '/php/config.php';

function usuario_autenticado() {
    // Verificar token en sesión
    if (isset($_SESSION['auth_token']) && isset($_SESSION['expiracion'])) {
        if (time() < $_SESSION['expiracion']) {
            return true;
        }
    }
    
    // Verificar datos de usuario en sesión
    if (isset($_SESSION['usuario_id'])) {
        return true;
    }
    
    return verificar_con_api();
}

function verificar_con_api() {
    // Endpoint para obtener perfil (ajusta según lo que use el backend)
    $resultado = api_request('/users/me', 'GET', null, true);
    
    if ($resultado['success'] && isset($resultado['data'])) {
        $usuario = $resultado['data'];
        
        // Adaptar según la estructura que devuelva el backend de tus compañeros
        $_SESSION['usuario_id'] = $usuario['id'] ?? $usuario['userId'] ?? null;
        $_SESSION['usuario_nombre'] = ($usuario['nombres'] ?? $usuario['firstName'] ?? '') . ' ' . ($usuario['apellidos'] ?? $usuario['lastName'] ?? '');
        $_SESSION['usuario_email'] = $usuario['email'] ?? null;
        $_SESSION['expiracion'] = time() + 3600;
        
        return true;
    }
    
    return false;
}

function obtener_usuario_actual() {
    if (usuario_autenticado()) {
        return [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? ''
        ];
    }
    return null;
}

function proteger_pagina() {
    if (!usuario_autenticado()) {
        $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página';
        header('Location: ' . url('/login.html'));
        exit;
    }
}

function redirigir_si_autenticado() {
    if (usuario_autenticado()) {
        header('Location: ' . url('/dashboard.php'));
        exit;
    }
}

function guardar_sesion($token, $usuario) {
    $_SESSION['auth_token'] = $token;
    $_SESSION['usuario_id'] = $usuario['id'] ?? $usuario['userId'] ?? null;
    $_SESSION['usuario_nombre'] = ($usuario['nombres'] ?? $usuario['firstName'] ?? '') . ' ' . ($usuario['apellidos'] ?? $usuario['lastName'] ?? '');
    $_SESSION['usuario_email'] = $usuario['email'] ?? null;
    $_SESSION['expiracion'] = time() + 3600;
}
?>