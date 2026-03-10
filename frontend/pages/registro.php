<?php
session_start();
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $db = new PDO('sqlite:../../database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO users (nombre, email, password) VALUES (:nombre, :email, :password)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        
        if ($stmt->execute()) {
            $mensaje = "<div style='background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;'><i class='fas fa-check-circle'></i> ¡Registro exitoso! Ya puedes iniciar sesión.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;'><i class='fas fa-exclamation-circle'></i> Ese correo ya está registrado o hubo un error.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Plataforma ODS7</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f5f9;">
    <?php include 'navbar.php'; ?>

    <main class="main-content" style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 0;">
        
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 400px; width: 100%; text-align: center; border: 1px solid #e2e8f0;">
            <div style="width: 60px; height: 60px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-user-plus" style="font-size: 2rem; color: #10b981;"></i>
            </div>
            <h2 style="color: #0f172a; margin-bottom: 5px;">Únete a ODS7</h2>
            <p style="color: #64748b; margin-bottom: 25px; font-size: 0.95rem;">Crea tu cuenta y empieza a publicar</p>

            <?php echo $mensaje; ?>
            
            <form method="POST" action="registro.php" style="text-align: left;">
                <label style="color: #475569; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; display: block;">Nombre Completo:</label>
                <input type="text" name="nombre" required placeholder="Ej. Fernando Juárez" style="width: 100%; padding: 12px 15px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">

                <label style="color: #475569; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; display: block;">Correo Electrónico:</label>
                <input type="email" name="email" required placeholder="tu@correo.com" style="width: 100%; padding: 12px 15px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
                
                <label style="color: #475569; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; display: block;">Contraseña:</label>
                <input type="password" name="password" required placeholder="Crea una contraseña segura" style="width: 100%; padding: 12px 15px; margin-bottom: 25px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
                
                <button type="submit" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: bold; cursor: pointer; background: linear-gradient(135deg, #059669, #10b981); color: white; border: none; border-radius: 10px; transition: 0.3s; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(16, 185, 129, 0.3)'">Registrarme</button>
            </form>
            <p style="margin-top: 25px; font-size: 0.9rem; color: #64748b;">¿Ya tienes cuenta? <a href="login.php" style="color: #10b981; text-decoration: none; font-weight: bold;">Inicia sesión</a></p>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>