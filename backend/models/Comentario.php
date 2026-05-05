<?php
class Comentario {
    private $conn;
    private $table_name = "comentarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Insertar un nuevo comentario
    public function crear($contenido, $usuario_id, $publicacion_id) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (contenido, usuario_id, publicacion_id) 
                  VALUES (:contenido, :usuario_id, :publicacion_id)";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $contenido = htmlspecialchars(strip_tags($contenido));
        $usuario_id = htmlspecialchars(strip_tags($usuario_id));
        $publicacion_id = htmlspecialchars(strip_tags($publicacion_id));

        // Vincular parámetros
        $stmt->bindParam(":contenido", $contenido);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":publicacion_id", $publicacion_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtener los comentarios de una publicación específica
    public function obtenerPorPublicacion($publicacion_id) {
        // Hacemos JOIN con usuarios para traer el nombre del autor del comentario
        $query = "SELECT c.*, u.nombre as autor_nombre 
                  FROM " . $this->table_name . " c
                  LEFT JOIN usuarios u ON c.usuario_id = u.id
                  WHERE c.publicacion_id = :publicacion_id 
                  ORDER BY c.fecha_creacion ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":publicacion_id", $publicacion_id);
        $stmt->execute();

        return $stmt;
    }
}
?>