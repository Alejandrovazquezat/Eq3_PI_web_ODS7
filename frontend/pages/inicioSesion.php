<?php
// Rutas seguras para el hosting
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

$db = (new Conexion())->getConexion();
$auth = new AuthController($db);

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
    
    <!-- Script Anti-Parpadeo para Modo Oscuro -->
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <link rel="stylesheet" href="../css/mascota.css">
    <!-- FontAwesome para los iconos (Incluido el de Google) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* =========================================
           ESTILOS INCRUSTADOS (A PRUEBA DE HOSTING)
           ========================================= */
        :root {
            --color-primario: #3b82f6;
            --color-oscuro: #0f172a;
            --fondo-suave: #f1f5f9;
            --blanco: #ffffff;
            --texto-titulos: #1e293b;
            --texto-oscuro: #64748b;
            --borde-tarjeta: #cbd5e1;
        }

        .dark-mode {
            --color-primario: #58a6ff;
            --color-oscuro: #010409;
            --fondo-suave: #0d1117;
            --blanco: #161b22;
            --texto-titulos: #e6edf3;
            --texto-oscuro: #8b949e;
            --borde-tarjeta: #30363d;
        }

        body {
            margin: 0;
            height: 100vh;
            overflow: hidden;
            background-color: var(--fondo-suave);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s;
        }

        .main-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* PANEL IZQUIERDO */
        .left-panel {
            width: 40%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: var(--blanco);
            padding: 40px 35px;
            border-radius: 2rem;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 2px solid var(--color-oscuro);
            text-align: center;
            z-index: 20;
            transition: background 0.3s, border-color 0.3s;
        }

        .mini-logo { width: 80px; margin-bottom: 1rem; }
        .login-card h2 { color: var(--texto-titulos); font-size: 1.8rem; margin-bottom: 1.5rem; margin-top: 0; }

        /* MENSAJE ERROR */
        .error-msg {
            background: #fee2e2; color: #ef4444; padding: 10px; 
            border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; 
            border: 1px solid #fca5a5;
        }
        .dark-mode .error-msg { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.5); }

        /* INPUTS BRUTALISTAS */
        .input-group { margin-bottom: 1.2rem; text-align: left; }
        .input-group label {
            display: block; font-size: 0.85rem; font-weight: 700;
            margin-bottom: 0.4rem; color: var(--texto-titulos);
        }

        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i.fa-at, .input-wrapper i.fa-lock { position: absolute; left: 1rem; color: var(--texto-oscuro); }

        .input-wrapper input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            font-size: 0.95rem;
            background: var(--blanco);
            border: 2px solid var(--color-oscuro);
            border-radius: 0.8rem;
            color: var(--texto-titulos);
            box-shadow: 3px 3px 0 var(--color-oscuro);
            outline: none;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .input-wrapper input:focus {
            transform: translate(-2px, -2px);
            box-shadow: 5px 6px 0 var(--color-primario);
        }

        .toggle-password { position: absolute; right: 1rem; cursor: pointer; color: var(--texto-oscuro); }

        /* BOTÓN PRINCIPAL */
        .btn-submit {
            width: 100%; padding: 0.9rem;
            background: var(--color-oscuro); color: white;
            border: none; border-radius: 40px;
            font-weight: 700; font-size: 1rem;
            cursor: pointer; margin-top: 1rem;
            transition: 0.3s;
            display: flex; justify-content: center; align-items: center; gap: 8px;
        }
        .btn-submit:hover { background: var(--color-primario); transform: translateY(-2px); }
        .dark-mode .btn-submit { color: #e6edf3; }

        /* DIVISOR */
        .divider { text-align: center; margin: 1.5rem 0; position: relative; }
        .divider::before { content: ""; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: var(--borde-tarjeta); }
        .divider span { position: relative; background: var(--blanco); padding: 0 10px; color: var(--texto-oscuro); font-size: 0.8rem; }

        /* BOTÓN GOOGLE */
        .btn-google {
            width: 100%; padding: 0.8rem;
            background: var(--blanco);
            border: 2px solid var(--borde-tarjeta);
            border-radius: 40px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            font-weight: 600; cursor: pointer; transition: 0.3s; color: var(--texto-titulos);
            font-size: 0.95rem;
        }
        .btn-google i { font-size: 1.2rem; color: #ea4335; } /* Color original de Google */
        .btn-google:hover { background: var(--fondo-suave); }

        .footer-text { margin-top: 20px; font-size: 0.9rem; color: var(--texto-oscuro); }
        .footer-text a { color: var(--color-primario); text-decoration: none; font-weight: 700; }
        .footer-text a:hover { text-decoration: underline; }

        /* PANEL DERECHO: PAISAJE ABSTRACTO */
        .right-panel {
            width: 60%;
            background: linear-gradient(180deg, #bbdefb 0%, #e3f2fd 40%, #c8e6c9 100%);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            position: relative; color: #1e293b; overflow: hidden;
            transition: background 0.3s ease;
        }

        .dark-mode .right-panel {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 60%, #064e3b 100%);
            color: white;
        }

        .right-panel::before, .right-panel::after {
            content: ""; position: absolute; width: 200%; height: 100%;
            top: 65%; left: -50%; border-radius: 42%; z-index: 1;
        }
        .right-panel::before { background-color: rgba(34, 197, 94, 0.4); animation: wave 20s infinite linear; }
        .right-panel::after { background-color: rgba(59, 130, 246, 0.3); top: 70%; animation: wave 25s infinite linear; }

        @keyframes wave {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .sun-glow {
            position: absolute; top: 10%; right: 10%;
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(253, 224, 71, 0.6) 0%, rgba(253, 224, 71, 0) 70%);
            filter: blur(30px);
        }

        .welcome-content { position: relative; z-index: 10; text-align: center; }
        .welcome-content h1 { font-size: 4rem; margin: 0; font-weight: 800; line-height: 1.1; }
        .welcome-content p { font-size: 1.2rem; margin-top: 15px; opacity: 0.9; }

        @media (max-width: 900px) {
            .right-panel { display: none; }
            .left-panel { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="main-container">
    <div class="left-panel">
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

            <button class="btn-google" type="button">
                <i class="fab fa-google"></i> Google
            </button>

            <p class="footer-text">
                ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
            </p>
        </div>
    </div>

    <div class="right-panel">
        <div class="sun-glow"></div>
        <div class="welcome-content">
            <h1>Bienvenido a<br>Red-novable!!</h1>
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

        // Aplicar clase al body si el modo oscuro está activo
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        }
    </script>
    
    <?php include 'mascota.php'; ?>
    
</body>
</html>