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
// 3. Sesión: si ya está logueado, redirigir al inicio
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// ==========================
// 4. Procesar registro
// ==========================
$mensaje = "";
$tipo_mensaje = ""; // 'error' o 'exito'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['user'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "error";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } else {
        // Intentar registrar
        $resultado_registro = $auth->registrar($nombre, $email, $password);

        if ($resultado_registro === "Registro exitoso") {
            // Iniciar sesión automáticamente
            $login = $auth->login($email, $password);
            if ($login === "Login correcto") {
                header("Location: index.php");
                exit;
            } else {
                // Error inesperado al iniciar sesión después del registro
                $mensaje = "Registro exitoso, pero no se pudo iniciar sesión. Intenta iniciar sesión manualmente.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = $resultado_registro; // Ya contiene el mensaje de error
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red-novable - Registro</title>
    <link rel="stylesheet" href="../css/styleRegistro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="conteiner">

        <!-- PANEL DERECHO (FORMULARIO) -->
        <div class="right">
            <div style="text-align: center;">
                <h2>REGISTRO</h2>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div style="<?= $tipo_mensaje === 'error' ? 'background: #fee2e2; color: #ef4444;' : 'background: #dcfce7; color: #166534;' ?> padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label for="user">Nombre de usuario:</label>
                <input type="text" id="user" name="user" required>

                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" placeholder="ejemplo@email.com" required>

                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" minlength="6" required>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit">Registrarse</button>
                </div>
            </form>
        </div>

        <!-- PANEL IZQUIERDO (con imagen de fondo) -->
        <div class="left" style="background-image: url('../image/Fondo.png'); background-size: cover; background-position: center;">
            <div class="welcome">
                <h1>Gracias por ingresar a<br>Red-novable!!</h1>
                <p>Ingresa tus datos correspondientes en las casillas</p>
            </div>
            <div class="footer">2026 red-novable.com all reserve to iso 994</div>
        </div>
    </div>
</body>
</html>