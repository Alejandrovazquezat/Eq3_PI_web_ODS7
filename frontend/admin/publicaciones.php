<?php
session_start();
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();
$pubs = $db->query("SELECT p.*, u.nombre as autor FROM publicaciones p JOIN usuarios u ON p.usuario_id = u.id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Proyectos - RedRenovable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box">
            <img src="logo para la pagina.jpeg" alt="Logo">
            <div class="logo-name">RedRenovable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link active">Proyectos</a>
        <a href="crear_publicacion.php" class="nav-link">+ Nueva Iniciativa</a>
    </div>

    <main class="main">
        <h1>Catálogo de Proyectos</h1>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>PREVIEW</th>
                        <th>NOMBRE DEL PROYECTO</th>
                        <th>AUTOR</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pubs as $item): ?>
                    <tr>
                        <td>
                            <?php if($item['imagen']): ?>
                                <img src="data:image/jpeg;base64,<?= base64_encode($item['imagen']) ?>" style="width:50px; height:50px; border-radius:8px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--primary-blue); font-weight: 700;"><?= htmlspecialchars($item['titulo']) ?></td>
                        <td><?= htmlspecialchars($item['autor']) ?></td>
                        <td><span style="color:var(--green)">●</span> <?= strtoupper($item['estado']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>