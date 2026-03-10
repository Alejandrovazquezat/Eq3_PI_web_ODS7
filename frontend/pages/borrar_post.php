<?php
session_start();

// Si no está logueado, a volar
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id']; // El ID del usuario actual

    try {
        $db = new PDO('sqlite:../../database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Seguridad nivel: Solo borra el post si el ID del creador coincide con tu ID
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :post_id AND user_id = :user_id");
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

    } catch (Exception $e) {
        // Si hay error, lo ognoramos
    }
}

// regresamos a la página principal
header("Location: index.php");
exit;
?>