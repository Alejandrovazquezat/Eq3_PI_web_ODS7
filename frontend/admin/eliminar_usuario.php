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
    
    try {
        $db = (new Conexion())->getConexion();
        
        $check = $db->prepare("SELECT id FROM usuarios WHERE id = :id");
        $check->bindParam(':id', $usuario_id);
        $check->execute();
        
        if ($check->fetch()) {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $usuario_id);
            $stmt->execute();
            $_SESSION['mensaje_exito'] = "Usuario eliminado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Usuario no encontrado.";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error al eliminar usuario: " . $e->getMessage();
    }
}

header("Location: usuarios.php");
exit;