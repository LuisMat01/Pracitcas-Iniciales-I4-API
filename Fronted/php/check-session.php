<?php
require_once dirname(__DIR__) . '/php/config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (usuario_autenticado()) {
    echo json_encode([
        'autenticado' => true,
        'usuario' => obtener_usuario_actual()
    ]);
} else {
    echo json_encode(['autenticado' => false]);
}
?>