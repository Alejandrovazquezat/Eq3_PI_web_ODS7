<?php
session_start();
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $db = new PDO('sqlite:../../database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logueado'] = true;
            $_SESSION['nombre_usuario'] = $user['nombre'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;'><i class='fas fa-exclamation-circle'></i> Correo o contraseña incorrectos, bro.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;'>Error al conectar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Plataforma ODS7</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f5f9;">
    <?php include 'navbar.php'; ?>

    <main class="main-content" style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
        
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 400px; width: 100%; text-align: center; border: 1px solid #e2e8f0;">
            <div style="width: 60px; height: 60px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-user-circle" style="font-size: 2rem; color: #3b82f6;"></i>
            </div>
            <h2 style="color: #0f172a; margin-bottom: 5px;">¡Qué bueno verte!</h2>
            <p style="color: #64748b; margin-bottom: 25px; font-size: 0.95rem;">Inicia sesión para continuar en ODS7</p>

            <?php echo $mensaje; ?>
            
            <form method="POST" action="login.php" style="text-align: left;">
                <label style="color: #475569; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; display: block;">Correo Electrónico:</label>
                <input type="email" name="email" required placeholder="tu@correo.com" style="width: 100%; padding: 12px 15px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
                
                <label style="color: #475569; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; display: block;">Contraseña:</label>
                <input type="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 12px 15px; margin-bottom: 25px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
                
                <button type="submit" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: bold; cursor: pointer; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; border: none; border-radius: 10px; transition: 0.3s; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(59, 130, 246, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(59, 130, 246, 0.3)'">Entrar al sistema</button>
            </form>
            <p style="margin-top: 25px; font-size: 0.9rem; color: #64748b;">¿No tienes cuenta? <a href="registro.php" style="color: #3b82f6; text-decoration: none; font-weight: bold;">Regístrate aquí</a></p>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>