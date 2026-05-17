<?php
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

$db = (new Conexion())->getConexion();
$auth = new AuthController($db);

$email = $_GET['email'] ?? '';
$mensaje = "";
$tipo_mensaje = "";

if (empty($email)) {
    header("Location: inicioSesion.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Unir los 6 inputs
    $codigo = $_POST['num1'] . $_POST['num2'] . $_POST['num3'] . $_POST['num4'] . $_POST['num5'] . $_POST['num6'];
    
    if ($auth->verificarCodigo($email, $codigo)) {
        // Código correcto, vamos a intentar iniciar sesión forzada (buscando al usuario)
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['rol_id'] = $row['rol_id'];
            $_SESSION['logueado'] = true; 
            
            $stmt_rol = $db->prepare("SELECT nombre FROM roles WHERE id = :id");
            $stmt_rol->bindParam(":id", $row['rol_id']);
            $stmt_rol->execute();
            $rol = $stmt_rol->fetch(PDO::FETCH_ASSOC);
            $_SESSION['rol_nombre'] = $rol['nombre'] ?? 'usuario';

            header("Location: index.php");
            exit;
        }
    } else {
        $mensaje = "Código incorrecto o cuenta ya verificada.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - Red-novable</title>
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/login-style.css">
</head>
<body>
    <div class="main-container">
        <div class="left-panel">
            <div class="sun-glow"></div>
            <div class="welcome-content">
                <h1>Casi listo...</h1>
                <p>Protegiendo la comunidad de bots.</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="bg-glow-1"></div>
            <div class="bg-glow-2"></div>

            <div class="login-card" style="padding-top: 30px;">
                <div class="icon-circle">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h2 style="font-size: 1.5rem; margin-bottom: 5px;">Verifica tu correo</h2>
                <p style="color: var(--texto-oscuro); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.4;">
                    Hemos enviado un código de 6 dígitos a <br><strong style="color: var(--texto-titulos);"><?= htmlspecialchars($email) ?></strong>
                </p>
                
                <?php if (!empty($mensaje)): ?>
                    <div class="msg <?= $tipo_mensaje ?>">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="code-container">
                        <input type="text" class="code-input" name="num1" maxlength="1" required autocomplete="off">
                        <input type="text" class="code-input" name="num2" maxlength="1" required autocomplete="off">
                        <input type="text" class="code-input" name="num3" maxlength="1" required autocomplete="off">
                        <input type="text" class="code-input" name="num4" maxlength="1" required autocomplete="off">
                        <input type="text" class="code-input" name="num5" maxlength="1" required autocomplete="off">
                        <input type="text" class="code-input" name="num6" maxlength="1" required autocomplete="off">
                    </div>

                    <button type="submit" class="btn-submit">
                        Verificar Código <i class="fas fa-check-circle"></i>
                    </button>
                </form>

                <p class="footer-text">
                    ¿No te llegó? <a href="registro.php">Intenta registrarte de nuevo</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Script para que el foco salte automáticamente al siguiente input
        const inputs = document.querySelectorAll('.code-input');
        inputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1) {
                    // Solo permite números
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value !== '' && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>