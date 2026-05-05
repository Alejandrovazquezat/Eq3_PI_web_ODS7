
Copiar

<?php
session_start();
require_once __DIR__ . '/../../config/Conexion.php';
$db = (new Conexion())->getConexion();
$u = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$p = $db->query("SELECT COUNT(*) FROM publicaciones")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css_dash/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <title>Dashboard - RedRenovable</title>
</head>
<body>
    <nav class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RED-novable</div>
        </div>
        <div class="menu-groups">
            <a href="dashboard.php" class="nav-link active">📊 Dashboard</a>
            <a href="publicaciones.php" class="nav-link">📝 Publicaciones</a>
            <a href="usuarios.php" class="nav-link">👥 Usuarios</a>
            <a href="crear_publicacion.php" class="nav-link btn-special">+ Nueva publicación</a>
        </div>
    </nav>
    <main class="main">
        <header class="main-header">
            <h1>Panel de Control</h1>
            <p>Resumen general de la plataforma</p>
        </header>
        <section class="stats-grid">
            <div class="card stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <p>Usuarios Registrados</p>
                    <h2><?= number_format($u) ?></h2>
                </div>
            </div>
            <div class="card stat-card">
                <div class="stat-icon">🍃</div>
                <div class="stat-info">
                    <p>Publicaciones Totales</p>
                    <h2><?= number_format($p) ?></h2>
                </div>
            </div>
        </section>
    </main>
</body>
</html>