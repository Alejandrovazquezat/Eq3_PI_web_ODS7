<?php
session_start();

$mensaje = "";
$registro_exitoso = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['user']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password)) {
        $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Todos los campos son obligatorios.</div>";
    } elseif (strlen($password) < 6) {
        $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>La contraseña debe tener al menos 6 caracteres.</div>";
    } else {
        try {
            $db = new PDO("mysql:host=localhost;dbname=plataforma_contenidos", "root", "");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verificar si el email ya existe
            $check = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
            $check->bindParam(':email', $email);
            $check->execute();
            
            if ($check->fetch()) {
                $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Este correo ya está registrado.</div>";
            } else {
                // Generar hash de la contraseña
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Verificar que el hash se generó correctamente
                if ($hashedPassword === false) {
                    $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Error al procesar la contraseña. Intenta de nuevo.</div>";
                } else {
                    $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol_id) VALUES (:nombre, :email, :password, 4)");
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hashedPassword);
                    
                    if ($stmt->execute()) {
                        // Obtener el ID del usuario recién creado
                        $usuario_id = $db->lastInsertId();
                        
                        // Iniciar sesión automáticamente
                        $_SESSION['logueado'] = true;
                        $_SESSION['nombre_usuario'] = $nombre;
                        $_SESSION['user_id'] = $usuario_id;
                        $_SESSION['rol_id'] = 4; // Usuario normal
                        
                        // Redirigir directamente al index
                        header("Location: index.php");
                        exit;
                    } else {
                        $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Error al guardar el usuario. Intenta de nuevo.</div>";
                    }
                }
            }
        } catch (Exception $e) {
            $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>Error al registrar: " . $e->getMessage() . "</div>";
        }
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