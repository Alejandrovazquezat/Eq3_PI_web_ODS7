<?php
require_once __DIR__ . '/../models/Comentario.php';

class ComentarioController {
    private $db;
    private $comentarioModel;

    public function __construct($db) {
        $this->db = $db;
        $this->comentarioModel = new Comentario($db);
    }

    public function agregarComentario($contenido, $usuario_id, $publicacion_id) {
        if (empty($contenido) || empty($usuario_id) || empty($publicacion_id)) {
            return "Error: Datos incompletos.";
        }
        
        if ($this->comentarioModel->crear($contenido, $usuario_id, $publicacion_id)) {
            return "Comentario agregado";
        } else {
            return "Error al guardar el comentario.";
        }
    }

    public function obtenerComentariosPorPublicacion($publicacion_id) {
        $stmt = $this->comentarioModel->obtenerPorPublicacion($publicacion_id);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>