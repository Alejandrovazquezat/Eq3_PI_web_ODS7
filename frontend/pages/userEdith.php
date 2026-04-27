<?php
session_start();

$user_id = $_SESSION['user_id'];
$mensaje = "";
$error = "";

try {
    $db = new PDO("mysql:host=localhost;dbname=plataforma_contenidos", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT nombre, email FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$email) {
            $error = "El formato del correo no es válido.";
        } 
        elseif (!empty($password) && strlen($password) < 6) {
            $error = "La nueva contraseña debe tener al menos 6 caracteres.";
        } 
        else {
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, password = :pass WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':pass', $passwordHash);
            } else {
                $sql = "UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id";
                $stmt = $db->prepare($sql);
            }

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $user_id);

            if ($stmt->execute()) {
                $_SESSION['nombre_usuario'] = $nombre; 
                $mensaje = "Datos actualizados correctamente.";
                $user['nombre'] = $nombre;
                $user['email'] = $email;
            }
        }
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - Redrenovable</title>
    <link rel="stylesheet" href="../css/editarInfoUser.css"> 
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="perfil-container">
        <h2><i class="fas fa-user-edit"></i> Mi Perfil</h2>
        
        <?php if($mensaje): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Nueva contraseña</label>
                <input type="password" id="password" name="password" minlength="6" placeholder="Hola atanacio, te estoy viendo">
                <p class="password-hint">Mínimo 6 caracteres para mayor seguridad.</p>
            </div>

            <button type="submit" class="btn-update">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </form>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>