<?php
session_start();

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['user'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $db = new PDO('sqlite:../../database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificar si el email ya existe
        $check = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check->bindParam(':email', $email);
        $check->execute();
        
        if ($check->fetch()) {
            $mensaje = "<div style='background: #fee2e2; color: #2f6a93; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Este correo ya está registrado.</div>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (nombre, email, password) VALUES (:nombre, :email, :password)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            
            if ($stmt->execute()) {
                $mensaje = "<div style='background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>¡Registro exitoso! <a href='inicioSesion.php' style='color: #15803d;'>Inicia sesión aquí</a></div>";
            }
        }
    } catch (Exception $e) {
        $mensaje = "<div style='background: #fee2e2; color: #2f6a93; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Error al registrar. Intenta de nuevo.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redrenovable - Registro</title>
    <link rel="stylesheet" href="../css/styleRegistro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="conteiner">

        <!-- PANEL DERECHO (FORMULARIO) -->
        <div class="right">
            <div style="text-align: center;">
                <h2>REGISTRO</h2>
                <h2> </h2>
            </div>

            <?php echo $mensaje; ?>

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
                <h1>Gracias por ingresar a<br>Redrenovable!!</h1>
                <p>Ingresa tus datos correspondientes en las casillas</p>
            </div>
            <div class="footer">2026 redrenovable.com all reserve to iso 994</div>
        </div>
    </div>
</body>
</html>