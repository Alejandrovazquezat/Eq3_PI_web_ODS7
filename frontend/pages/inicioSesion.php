<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

// ==========================
// 2. Conexión e instancia del controlador
// ==========================
$db = (new Conexion())->getConexion();
$auth = new AuthController($db);

// ==========================
// 3. Sesión: si ya está logueado, redirigir
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// ==========================
// 4. Procesar login
// ==========================
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $resultado = $auth->login($email, $password);

    if ($resultado === "Login correcto") {
        // Redirigir al index con la sesión ya iniciada por AuthController
        header("Location: index.php");
        exit;
    } else {
        $mensaje = $resultado;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Redrenovable - Iniciar Sesión</title>
    <link rel="stylesheet" href="../css/login-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="container">

    <!-- PANEL IZQUIERDO -->
    <div class="left">
        <div style="text-align: center;">
            <h2>Iniciar Sesión</h2>
        </div>
        
        <img src="../image/LogotipoSinfondo.png" class="logotipo" alt="Logotipo">

        <?php if (!empty($mensaje)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Correo Electrónico:</label>
            <input type="email" name="email" required>
        
            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit">Iniciar Sesión</button>
            </div>
        </form>

        <div class="crearCuenta" style="margin-top: 20px; text-align: center;">
            <a href="registro.php">Crear cuenta</a>
        </div>
    </div>

    <!-- PANEL DERECHO -->
    <div class="right" style="background-image: url('../image/EquipodetrabajoPaneles.png'); background-size: cover; background-position: center; position: relative;">
        <div class="welcome" style="position: relative; z-index: 2; text-align: center; color: white; padding: 20px;">
            <h1>Bienvenido a<br>Red-novable!!</h1>
            <p>Ingresa tus datos correspondientes en las casillas</p>
        </div>
        
        <div class="footer" style="position: absolute; bottom: 20px; left: 0; right: 0; text-align: center; z-index: 2; color: white; font-size: 0.8rem;">
            2026 red-novable.com todos los derechos en ISO 994
        </div>
    </div>
</div>

</body>
</html>