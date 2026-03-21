<?php
session_start();
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();

$pubs = $db->query("SELECT p.*, u.nombre as autor, c.nombre as categoria 
                    FROM publicaciones p 
                    JOIN usuarios u ON p.usuario_id = u.id 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../css/publicaciones_styles.css">
    <title>Publicaciones - RedRenovable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RedRenovable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link active">Publicaciones</a>
        <a href="usuarios.php" class="nav-link">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Todas las publicaciones</h1>
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
                        <?php foreach($pubs as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td>
                                <?php if($item['imagen']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($item['imagen']) ?>" class="imagen-preview">
                                <?php else: ?>
                                    <span class="sin-imagen">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($item['titulo']) ?></strong></td>
                            <td><?= htmlspecialchars($item['autor']) ?></td>
                            <td><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?></td>
                            <td>
                                <span class="badge-estado <?= $item['estado'] ?>">
                                    <?= ucfirst($item['estado']) ?>
                                </span>
                            </td>
                            <td><?= $item['fecha_creacion'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>