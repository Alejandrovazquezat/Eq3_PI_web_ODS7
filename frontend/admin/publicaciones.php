<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';

// ==========================
// 2. Conexión y sesión
// ==========================
$db = (new Conexion())->getConexion();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 3. Verificar autenticación y permiso
// ==========================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/inicioSesion.php");
    exit;
}

$auth = new AuthController($db);
$usuario_id = $_SESSION['usuario_id'];

// Solo admin y editor pueden ver todas las publicaciones
if (!$auth->tienePermiso($usuario_id, 'ver_todas_publicaciones')) {
    // Si no tiene permiso, redirigir a la página principal
    header("Location: ../pages/index.php");
    exit;
}

// ==========================
// 4. Obtener publicaciones usando el controlador
// ==========================
$pubController = new PublicacionController($db);
$pubs_stmt = $pubController->obtenerTodas($usuario_id);

// Si es un string, es un mensaje de error (no debería ocurrir porque ya validamos)
if (is_string($pubs_stmt)) {
    $error = $pubs_stmt;
    $pubs = [];
} else {
    $pubs = $pubs_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../css/publicaciones_styles.css">
    <title>Publicaciones - Red-novable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">Red-novable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link active">Publicaciones</a>
        <a href="usuarios.php" class="nav-link">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Todas las publicaciones</h1>
        
        <?php if (isset($error)): ?>
            <div class="mensaje-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="table-container">
                <table class="publicaciones-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pubs) > 0): ?>
                            <?php foreach($pubs as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td>
                                    <?php if($item['imagen']): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($item['imagen']) ?>" class="imagen-preview" alt="Imagen">
                                    <?php else: ?>
                                        <span class="sin-imagen">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($item['titulo']) ?></strong></td>
                                <td><?= htmlspecialchars($item['autor'] ?? 'Desconocido') ?></td>
                                <td><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?></td>
                                <td>
                                    <span class="badge-estado <?= $item['estado'] ?>">
                                        <?= ucfirst($item['estado']) ?>
                                    </span>
                                </td>
                                <td><?= $item['fecha_creacion'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                                    No hay publicaciones para mostrar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>