<?php
session_start();
require_once __DIR__ . '/../../config/Conexion.php';

// Verificar que el usuario es admin
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['rol_id'] != 1) {
    header("Location: ../../pages/inicioSesion.php");
    exit;
}

$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($usuario_id > 0) {
    if ($usuario_id == ($_SESSION['usuario_id'] ?? 0)) {
        $_SESSION['mensaje_error'] = "No puedes eliminar tu propio usuario.";
        header("Location: usuarios.php");
        exit;
    }
    
    // El ID 1 suele ser el Super Admin irrevocable, lo protegemos por seguridad
    if ($usuario_id == 1) {
        $_SESSION['mensaje_error'] = "No se puede eliminar al Administrador principal.";
        header("Location: usuarios.php");
        exit;
    }
    
    try {
        $db = (new Conexion())->getConexion();
        
        $check = $db->prepare("SELECT id FROM usuarios WHERE id = :id");
        $check->bindParam(':id', $usuario_id);
        $check->execute();
        
        if ($check->fetch()) {
            
            // 1. Iniciamos la transacción (Todo o nada)
            $db->beginTransaction();
            
            // 2. Eliminar los LIKES que dio el usuario
            $stmtLikes = $db->prepare("DELETE FROM likes WHERE usuario_id = :id");
            $stmtLikes->execute([':id' => $usuario_id]);
            
            // 3. Eliminar los COMENTARIOS que hizo el usuario
            $stmtComentarios = $db->prepare("DELETE FROM comentarios WHERE usuario_id = :id");
            $stmtComentarios->execute([':id' => $usuario_id]);
            
            // 4. Conservar PUBLICACIONES: Transferir la autoría al Admin (ID 1)
            $stmtPubs = $db->prepare("UPDATE publicaciones SET usuario_id = 1 WHERE usuario_id = :id");
            $stmtPubs->execute([':id' => $usuario_id]);
            
            // 5. Eliminar finalmente al USUARIO
            $stmtUser = $db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmtUser->execute([':id' => $usuario_id]);
            
            // 6. Si todo salió bien, confirmamos los cambios en la base de datos
            $db->commit();
            
            $_SESSION['mensaje_exito'] = "Usuario eliminado. Sus publicaciones fueron transferidas al Administrador.";
        } else {
            $_SESSION['mensaje_error'] = "Usuario no encontrado.";
        }
    } catch (Exception $e) {
        // Si hay un error, revertimos todos los cambios para no dañar la base de datos
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        $_SESSION['mensaje_error'] = "Error crítico al eliminar usuario: " . $e->getMessage();
    }
} else {
    $_SESSION['mensaje_error'] = "ID de usuario inválido.";
}

// Redirigir de vuelta al panel de usuarios
header("Location: usuarios.php");
exit;