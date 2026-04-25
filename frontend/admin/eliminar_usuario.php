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
$usuario_id_eliminar = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($usuario_id_eliminar > 0) {
    // No permitir eliminarse a sí mismo
    if ($usuario_id_eliminar == $usuario_id_admin) {
        $_SESSION['mensaje_error'] = "No puedes eliminar tu propio usuario.";
        header("Location: usuarios.php");
        exit;
    }
    
    $auth = new AuthController($db);
    
    // Verificar permiso de eliminación
    if (!$auth->tienePermiso($usuario_id_admin, 'eliminar_usuario')) {
        $_SESSION['mensaje_error'] = "No tienes permiso para eliminar usuarios.";
        header("Location: usuarios.php");
        exit;
    }
    
    // Usar el controlador para eliminar
    $usuarioController = new UsuarioController($db);
    $resultado = $usuarioController->eliminar($usuario_id_admin, $usuario_id_eliminar);
    
    // El método devuelve string con el mensaje
    if (strpos($resultado, 'correctamente') !== false) {
        $_SESSION['mensaje_exito'] = $resultado;
    } else {
        $_SESSION['mensaje_error'] = $resultado;
    }
} else {
    $_SESSION['mensaje_error'] = "ID de usuario no válido.";
}

header("Location: usuarios.php");
exit;