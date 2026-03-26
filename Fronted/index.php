<?php
session_start();
require_once "php/config/api.php";

// Si no está autenticado → redirigir al login
$token = $_SESSION['auth_token'] ?? null;
if (!$token) {
    header('Location: PRACTICAS_INICIALES_AUTENTICACI-N/login.html');
    exit;
}

// Obtener posts
$response = api_request("/posts", "GET", null, $token);

if (!is_array($response)) {
    echo "<pre>Error al obtener posts:\n";
    var_dump($response);
    echo "</pre>";
    exit;
}

$posts = $response['data'] ?? $response;

// Filtrado por GET
$cursoFiltro = $_GET['curso'] ?? '';
$profesorFiltro = $_GET['profesor'] ?? '';

if ($cursoFiltro) {
    $posts = array_filter($posts, fn($p) => isset($p['course']['nombre']) && stripos($p['course']['nombre'], $cursoFiltro) !== false);
}

if ($profesorFiltro) {
    $posts = array_filter($posts, fn($p) => isset($p['professor']['nombres']) && stripos($p['professor']['nombres'], $profesorFiltro) !== false);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Red Social USAC</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <h1>Publicaciones</h1>

    <div class="filters">
        <form method="GET">
            <input type="text" name="curso" placeholder="Buscar por curso" value="<?= htmlspecialchars($cursoFiltro) ?>">
            <input type="text" name="profesor" placeholder="Buscar por catedrático" value="<?= htmlspecialchars($profesorFiltro) ?>">
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <a href="create.php" class="btn">+ Crear Publicación</a>

    <div class="posts">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card">
                    <p class="user"><?= htmlspecialchars($post["user"]["nombres"] ?? "Usuario desconocido") ?></p>
                    <p class="message"><?= htmlspecialchars($post["mensaje"] ?? "Sin mensaje") ?></p>
                    <p class="meta">
                        <?= htmlspecialchars($post["course"]["nombre"] ?? $post["professor"]["nombres"] ?? "Sin referencia") ?>
                    </p>
                    <p class="date"><?= htmlspecialchars($post["createdAt"] ?? "Sin fecha") ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay publicaciones disponibles.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>