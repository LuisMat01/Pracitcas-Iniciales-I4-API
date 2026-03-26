<?php
session_start();
require_once "php/config/api.php";

// Validar sesión
$token = $_SESSION['auth_token'] ?? null;
if (!$token) {
    header('Location: PRACTICAS_INICIALES_AUTENTICACI-N/login.html');
    exit;
}

// Manejo del POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'mensaje' => $_POST['mensaje'] ?? ''
    ];

    if (!empty($_POST['courseId'])) $data['course_id'] = $_POST['courseId'];
    if (!empty($_POST['professorId'])) $data['professor_id'] = $_POST['professorId'];

    $response = api_request("/posts", "POST", $data, $token);

    if (!is_array($response) || !($response['success'] ?? false)) {
        $_SESSION['error'] = $response['error'] ?? 'Error al crear publicación';
        header('Location: create.php');
        exit;
    }

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Publicación - Red Social USAC</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <h1>Crear Publicación</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alerta-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form">
        <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>

        <select name="courseId">
            <option value="">Seleccionar Curso</option>
            <option value="1">IPC2</option>
            <option value="2">Matemática</option>
        </select>

        <select name="professorId">
            <option value="">Seleccionar Catedrático</option>
            <option value="1">Ing. Pérez</option>
            <option value="2">Lic. Gómez</option>
        </select>

        <button type="submit">Publicar</button>
    </form>

    <a href="index.php" class="btn">Volver</a>
</div>
</body>
</html>