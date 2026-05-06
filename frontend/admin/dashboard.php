<?php
session_start();

// Validar permisos: Solo Admin (1) y Editor (2)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 2])) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once __DIR__ . '/../../config/Conexion.php';
$db = (new Conexion())->getConexion();
$u = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$p = $db->query("SELECT COUNT(*) FROM publicaciones")->fetchColumn();
$total_comentarios = $db->query("SELECT COUNT(*) FROM comentarios")->fetchColumn();
$total_likes = $db->query("SELECT COUNT(*) FROM likes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css_dash/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <title>Dashboard - Red-novable</title>
</head>
<body>

    <?php include 'sidebar.php'; ?>

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
            <div class="card stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-info">
                    <p>Comentarios Totales</p>
                    <h2><?= number_format($total_comentarios) ?></h2>
                </div>
            </div>
            <div class="card stat-card">
                <div class="stat-icon">❤️</div>
                <div class="stat-info">
                    <p>Likes Totales</p>
                    <h2><?= number_format($total_likes) ?></h2>
                </div>
            </div>
        </section>
    </main>
</body>
</html>