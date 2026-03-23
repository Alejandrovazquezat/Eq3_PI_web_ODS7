<?php
session_start();
require_once 'Conexion.php';

// Verificar que el usuario es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['rol_id'] != 1) {
    header("Location: ../../pages/inicioSesion.php");
    exit;
}

$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nuevo_rol = isset($_GET['rol']) ? intval($_GET['rol']) : 0;

// Proteger al Alejandro Admin Supremo (ID 1)
if ($usuario_id == 1) {
    $_SESSION['mensaje_error'] = "¡No puedes modificar a Alejandro Admin Supremo!";
    header("Location: usuarios.php");
    exit;
}

if ($usuario_id > 0 && $nuevo_rol > 0) {
    // Verificar que el rol existe
    $rolesValidos = [1, 2, 3, 4];
    if (!in_array($nuevo_rol, $rolesValidos)) {
        $_SESSION['mensaje_error'] = "Rol no válido.";
        header("Location: usuarios.php");
        exit;
    }
    
    try {
        $db = (new Conexion())->getConexion();
        
        // Verificar si el usuario existe
        $check = $db->prepare("SELECT id FROM usuarios WHERE id = :id");
        $check->bindParam(':id', $usuario_id);
        $check->execute();
        
        if ($check->fetch()) {
            // Cambiar rol del usuario
            $stmt = $db->prepare("UPDATE usuarios SET rol_id = :rol_id WHERE id = :id");
            $stmt->bindParam(':rol_id', $nuevo_rol);
            $stmt->bindParam(':id', $usuario_id);
            $stmt->execute();
            $_SESSION['mensaje_exito'] = "Rol del usuario actualizado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Usuario no encontrado.";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error al cambiar rol: " . $e->getMessage();
    }
}

header("Location: usuarios.php");
exit;
?>