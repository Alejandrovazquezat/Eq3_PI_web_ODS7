<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/ComentarioController.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'redirect' => 'registro.php']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $publicacion_id = isset($_POST['publicacion_id']) ? intval($_POST['publicacion_id']) : 0;
    $contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';
    $usuario_id = $_SESSION['usuario_id'];
    $nombre_usuario = $_SESSION['nombre']; // Asumiendo que guardas el nombre en sesión

    if ($publicacion_id > 0 && !empty($contenido)) {
        $db = (new Conexion())->getConexion();
        $comentarioCtrl = new ComentarioController($db);
        
        $resultado = $comentarioCtrl->agregarComentario($contenido, $usuario_id, $publicacion_id);
        
        if ($resultado === "Comentario agregado") {
            // Devolvemos los datos para que JS los dibuje de inmediato sin recargar
            echo json_encode([
                'success' => true,
                'autor' => htmlspecialchars($nombre_usuario),
                'contenido' => htmlspecialchars($contenido)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => $resultado]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'El comentario no puede estar vacío.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>