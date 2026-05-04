<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/UsuarioController.php';
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

// Solo admin y editor pueden ver estadísticas generales
if (!$auth->tienePermiso($usuario_id, 'ver_estadisticas')) {
    // Redirigir a la página principal si no tiene permisos
    header("Location: ../pages/index.php");
    exit;
}

// ==========================
// 4. Obtener estadísticas usando el controlador
// ==========================
$usuarioController = new UsuarioController($db);
$estadisticas = $usuarioController->obtenerEstadisticas($usuario_id);

// Si hubo error (aunque no debería porque ya verificamos permisos)
if (is_string($estadisticas)) {
    $error_stats = $estadisticas;
    $total_usuarios = 0;
    $total_publicaciones = 0;
} else {
    $total_usuarios = $estadisticas['total_usuarios'];
    $total_publicaciones = $estadisticas['total_publicaciones'];
}

// Opcional: obtener conteo de publicaciones pendientes
$pubController = new PublicacionController($db);
$pendientes = $pubController->obtenerPendientes($usuario_id);
$num_pendientes = is_object($pendientes) ? $pendientes->rowCount() : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Dashboard - Red-novable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">Red-novable</div>
        </div>
        <a href="dashboard.php" class="nav-link active">Dashboard</a>
        <a href="publicaciones.php" class="nav-link">Publicaciones</a>
        <a href="usuarios.php" class="nav-link">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Dashboard General</h1>
        
        <?php if (isset($error_stats)): ?>
            <div class="mensaje-error"><?= $error_stats ?></div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 25px;">
            <div class="card">
                <p style="color:var(--text-light)">Usuarios Registrados</p>
                <h2 style="font-size: 3rem; margin:0"><?= $total_usuarios ?></h2>
            </div>
            <div class="card">
                <p style="color:var(--text-light)">Publicaciones</p>
                <h2 style="font-size: 3rem; margin:0"><?= $total_publicaciones ?></h2>
            </div>
            <div class="card">
                <p style="color:var(--text-light)">Pendientes de revisión</p>
                <h2 style="font-size: 3rem; margin:0"><?= $num_pendientes ?></h2>
            </div>
        </div>
    </main>
</body>
</html>