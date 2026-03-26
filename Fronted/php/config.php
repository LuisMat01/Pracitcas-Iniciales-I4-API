<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// CONFIGURACIÓN PARA EL BACKEND REAL (PUERTO 3000)
// ============================================

// Definir la URL del backend real de tus compañeros
define('API_URL', 'http://localhost:3000');

// URL base de tu frontend
define('BASE_URL', '/red-social-usac/persona1-autenticacion');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para obtener URL completa del frontend
function url($path = '') {
    return BASE_URL . $path;
}

// Función para obtener URL de la API
function api_url($path = '') {
    return API_URL . $path;
}

// Función para redireccionar
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

// Función para hacer peticiones a la API (adaptada para el backend en puerto 3000)
function api_request($endpoint, $method = 'GET', $data = null, $authenticated = false) {
    $ch = curl_init(api_url($endpoint));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = ['Content-Type: application/json'];
    
    // Si la petición necesita autenticación y tenemos token
    if ($authenticated && isset($_SESSION['auth_token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['auth_token'];
    }
    
    // Enviar cookies guardadas (para el backend de NestJS)
    if (isset($_SESSION['cookies']) && !empty($_SESSION['cookies'])) {
        $cookieString = '';
        foreach ($_SESSION['cookies'] as $name => $value) {
            $cookieString .= "$name=$value; ";
        }
        $headers[] = 'Cookie: ' . rtrim($cookieString, '; ');
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    // Guardar cookies que vienen del servidor
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) return $len;
        
        if (strtolower(trim($header[0])) === 'set-cookie') {
            if (preg_match('/([^=]+)=([^;]+)/', $header[1], $matches)) {
                $_SESSION['cookies'][$matches[1]] = $matches[2];
            }
        }
        return $len;
    });
    
    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => 'Error de conexión con el backend: ' . $error];
    }
    
    $data = json_decode($respuesta, true);
    
    // Si no hay datos, devolver un array vacío
    if ($data === null) {
        $data = [];
    }
    
    return [
        'success' => $codigo >= 200 && $codigo < 300,
        'status_code' => $codigo,
        'data' => $data,
        'error' => $data['message'] ?? ($data['error'] ?? 'Error en la petición')
    ];
}
?>