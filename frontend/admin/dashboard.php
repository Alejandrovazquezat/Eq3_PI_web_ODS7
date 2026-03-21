<?php
session_start();
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();

$u = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$p = $db->query("SELECT COUNT(*) FROM publicaciones")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Dashboard - RedRenovable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RedRenovable</div>
        </div>
        <a href="dashboard.php" class="nav-link active">Dashboard</a>
        <a href="publicaciones.php" class="nav-link">Publicaciones</a>
        <a href="usuarios.php" class="nav-link">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Dashboard General</h1>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
            <div class="card">
                <p style="color:var(--text-light)">Usuarios Registrados</p>
                <h2 style="font-size: 3rem; margin:0"><?= $u ?></h2>
            </div>
            <div class="card">
                <p style="color:var(--text-light)">Publicaciones</p>
                <h2 style="font-size: 3rem; margin:0"><?= $p ?></h2>
            </div>
        </div>
    </main>
</body>
</html>