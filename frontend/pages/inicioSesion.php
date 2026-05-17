<?php
// Rutas seguras para el hosting
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../config/google_config.php';

$db = (new Conexion())->getConexion();
$auth = new AuthController($db);
// Generar la URL segura de Google
$login_url_google = $client->createAuthUrl();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $resultado = $auth->login($email, $password);
    
    if ($resultado === "Login correcto") {
        header("Location: index.php");
        exit;
    } elseif (strpos($resultado, "no_verificado|") === 0) {
        $partes = explode("|", $resultado);
        $email_registrado = $partes[1];
        header("Location: verificar.php?email=" . urlencode($email_registrado));
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Red-novable</title>
    
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <link rel="stylesheet" href="../css/mascota.css">
    <link rel="stylesheet" href="../css/login-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="main-container">
        <div class="left-panel">
            <div class="bg-glow-1"></div>
            <div class="bg-glow-2"></div>

            <div class="login-card">
                <img src="../image/LogotipoSinfondo.png" class="mini-logo" alt="Logo">
                <h2>Iniciar Sesión</h2>
                
                <?php if (!empty($mensaje)): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
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
                            <input type="password" name="password" id="login-pass" placeholder="Tu contraseña" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePass('login-pass', this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        Iniciar Sesión <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="divider"><span>O continúa con</span></div>

                <a href="<?= filter_var($login_url_google, FILTER_SANITIZE_URL) ?>" class="btn-google">
                    <i class="fab fa-google"></i> Google
                </a>

                <p class="footer-text">
                    ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
                </p>
            </div>
        </div>

        <div class="right-panel">
            <div class="sun-glow"></div>
            <div class="welcome-content">
                <h1>Red-novable<br> te da la bienvenida!!</h1>
                <p>Impulsando el cambio hacia energías limpias.</p>
            </div>
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