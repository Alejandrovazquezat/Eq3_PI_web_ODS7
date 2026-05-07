<?php
// Rutas seguras para el hosting
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

$db = (new Conexion())->getConexion();
$auth = new AuthController($db);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya hay sesión, redirigir
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['user'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "error";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } else {
        $resultado_registro = $auth->registrar($nombre, $email, $password);

        if ($resultado_registro === "Registro exitoso") {
            $login = $auth->login($email, $password);
            if ($login === "Login correcto") {
                header("Location: index.php");
                exit;
            } else {
                $mensaje = "Registro exitoso. Por favor, inicia sesión.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = $resultado_registro;
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
    <title>Crear Cuenta - Red-novable</title>
    
    <!-- Script Anti-Parpadeo para Modo Oscuro -->
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

    <!-- FontAwesome y Hoja de Estilos Externa -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styleRegistro.css">
    <link rel="stylesheet" href="../css/mascota.css">
</head>
<body>

    <div class="main-container">
    
    <!-- PANEL IZQUIERDO: PAISAJE ABSTRACTO -->
    <div class="left-panel">
        <div class="sun-glow"></div>
        <div class="welcome-content">
            <h1>Crea tu cuenta en<br>Red-novable</h1>
            <p>Únete a nuestra comunidad y aprende sobre energía limpia.</p>
        </div>
    </div>

    <!-- PANEL DERECHO: FORMULARIO -->
    <div class="right-panel">
        <div class="login-card">
            <img src="../image/LogotipoSinfondo.png" class="mini-logo" alt="Logo">
            <h2>Registro</h2>
            
            <?php if (!empty($mensaje)): ?>
                <div class="msg <?= $tipo_mensaje ?>">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Nombre de usuario:</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="user" placeholder="Ej. Alejandro" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Correo Electrónico:</label>
                    <div class="input-wrapper">
                        <i class="fas fa-at"></i>
                        <input type="email" name="email" placeholder="ejemplo@correo.com" required>
                    </div>
                </div>
            
                <div class="input-group">
                    <label>Contraseña:</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="reg-pass" placeholder="Mínimo 6 caracteres" minlength="6" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePass('reg-pass', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Registrarse <i class="fas fa-user-plus"></i>
                </button>
            </form>

            <div class="divider"><span>O regístrate con</span></div>

            <!-- BOTÓN DE GOOGLE -->
            <a href="google_auth.php" class="btn-google">
                <i class="fab fa-google"></i> Google
            </a>

            <p class="footer-text">
                ¿Ya tienes cuenta? <a href="inicioSesion.php">Inicia sesión</a>
            </p>
        </div>
    </div>



    <script>
        function togglePass(id, el) {
          const input = document.getElementById(id);
          if (input.type === "password") {
             input.type = "text";
             el.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
               input.type = "password";
               el.classList.replace('fa-eye-slash', 'fa-eye');
           }
        }

        if (localStorage.getItem('darkMode') === 'enabled') {
           document.body.classList.add('dark-mode');
        }
    </script>
    <?php include 'mascota.php'; ?>
</body>
</html>