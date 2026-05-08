<?php
session_start();
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../config/google_config.php';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // Obtener datos del perfil de Google
        $google_oauth = new Google\Service\Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $nombre = $google_account_info->name;

        $db = (new Conexion())->getConexion();

        // Verificar si el usuario ya existe
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Ya existe: Iniciar sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            header("Location: index.php");
            exit;
        } else {
            // Es nuevo: Registrar como Usuario normal (Rol 4)
            $rol_por_defecto = 4; 
            // Se le asigna una contraseña encriptada aleatoria ya que accederá por Google
            $password_segura = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
            
            $stmt_insert = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol_id) VALUES (?, ?, ?, ?)");
            
            if ($stmt_insert->execute([$nombre, $email, $password_segura, $rol_por_defecto])) {
                $nuevo_id = $db->lastInsertId();
                
                // Iniciar sesión tras registro exitoso
                $_SESSION['usuario_id'] = $nuevo_id;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['rol_id'] = $rol_por_defecto;
                header("Location: index.php");
                exit;
            } else {
                die("Hubo un error al registrar la cuenta.");
            }
        }
    } else {
        die("Error de autenticación con Google.");
    }
} else {
    header("Location: inicioSesion.php");
    exit;
}
?>