<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/models/Like.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado', 'redirect' => 'registro.php']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$publicacion_id = isset($_POST['publicacion_id']) ? intval($_POST['publicacion_id']) : 0;

if ($publicacion_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$db = (new Conexion())->getConexion();
$like = new Like($db);
$like->usuario_id = $usuario_id;
$like->publicacion_id = $publicacion_id;

$yaDioLike = $like->yaDioLike();

if ($yaDioLike) {
    // Quitar like
    if ($like->quitarLike()) {
        $total = $like->contarLikes($publicacion_id);
        echo json_encode([
            'success' => true,
            'action' => 'unlike',
            'total_likes' => $total
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al quitar like']);
    }
} else {
    // Dar like
    if ($like->darLike()) {
        $total = $like->contarLikes($publicacion_id);
        echo json_encode([
            'success' => true,
            'action' => 'like',
            'total_likes' => $total
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al dar like']);
    }
}