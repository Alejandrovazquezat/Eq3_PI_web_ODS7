<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/UsuarioController.php';

// ==========================
// 2. Conexión y sesión
// ==========================
$db = (new Conexion())->getConexion();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 3. Verificar autenticación
// ==========================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/inicioSesion.php");
    exit;
}

$usuario_id_admin = $_SESSION['usuario_id'];
$usuario_id_destino = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nuevo_rol = isset($_GET['rol']) ? intval($_GET['rol']) : 0;

// Proteger al Admin Supremo (ID 1)
if ($usuario_id_destino == 1) {
    $_SESSION['mensaje_error'] = "¡No puedes modificar al Admin Supremo!";
    header("Location: usuarios.php");
    exit;
}

if ($usuario_id_destino > 0 && $nuevo_rol > 0) {
    $auth = new AuthController($db);
    
    // Verificar permiso de edición de usuarios
    if (!$auth->tienePermiso($usuario_id_admin, 'editar_usuario')) {
        $_SESSION['mensaje_error'] = "No tienes permiso para cambiar roles.";
        header("Location: usuarios.php");
        exit;
    }
    
    // Usar el controlador para cambiar el rol
    $usuarioController = new UsuarioController($db);
    $resultado = $usuarioController->cambiarRol($usuario_id_admin, $usuario_id_destino, $nuevo_rol);
    
    // El método devuelve string con el mensaje
    if (strpos($resultado, 'correctamente') !== false) {
        $_SESSION['mensaje_exito'] = $resultado;
    } else {
        $_SESSION['mensaje_error'] = $resultado;
    }
} else {
    $_SESSION['mensaje_error'] = "Datos incompletos para cambiar el rol.";
}

header("Location: usuarios.php");
exit;