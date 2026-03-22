<?php
session_start();

// Si ya está logueado, redirigir al inicio
if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Cambiar a MySQL
        $db = new PDO("mysql:host=localhost;dbname=plataforma_contenidos", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logueado'] = true;
            $_SESSION['nombre_usuario'] = $user['nombre'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['rol_id'] = $user['rol_id']; // Guardar rol para después
            header("Location: index.php");
            exit;
        } else {
            $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Correo o contraseña incorrectos.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Error al conectar con la base de datos: " . $e->getMessage() . "</div>";
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

        <?php echo $mensaje; ?>

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
            <h1>Bienvenido a<br>Redrenovable!!</h1>
            <p>Ingresa tus datos correspondientes en las casillas</p>
        </div>
        
        <div class="footer" style="position: absolute; bottom: 20px; left: 0; right: 0; text-align: center; z-index: 2; color: white; font-size: 0.8rem;">
            2026 redrenovable.com todos los derechos en ISO 994
        </div>
    </div>
</div>

</body>
</html>