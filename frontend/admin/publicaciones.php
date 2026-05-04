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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/publicaciones_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <title>Publicaciones - RedRenovable</title>
</head>
<body>
    <nav class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RED-novable</div>
        </div>
        <div class="menu-groups">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="publicaciones.php" class="nav-link active">📝 Publicaciones</a>
            <a href="usuarios.php" class="nav-link">👥 Usuarios</a>
            <a href="crear_publicacion.php" class="nav-link btn-special">+ Nueva publicación</a>
        </div>
    </nav>

    <main class="main">
        <header class="main-header">
            <h1>Gestión de Contenido</h1>
            <p>Administra y supervisa las publicaciones de la red.</p>
        </header>

        <div class="card-table-container">
            <div class="table-header">
                <h3>Listado de Publicaciones</h3>
            </div>
            <div class="table-responsive">
                <table class="publicaciones-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título y Autor</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pubs as $item): ?>
                        <tr>
                            <td>
                                <div class="img-wrapper">
                                    <?php if($item['imagen']): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($item['imagen']) ?>" class="imagen-preview">
                                    <?php else: ?>
                                        <div class="sin-imagen">🍃</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="titulo-meta">
                                    <span class="p-titulo"><?= htmlspecialchars($item['titulo']) ?></span>
                                    <span class="p-autor">Por: <?= htmlspecialchars($item['autor']) ?></span>
                                </div>
                            </td>
                            <td><span class="cat-tag"><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?></span></td>
                            <td>
                                <span class="badge-estado <?= strtolower($item['estado']) ?>">
                                    <?= ucfirst($item['estado']) ?>
                                </span>
                            </td>
                            <td class="fecha-col"><?= date('d M, Y', strtotime($item['fecha_creacion'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>