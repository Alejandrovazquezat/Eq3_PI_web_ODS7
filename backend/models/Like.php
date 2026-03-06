<?php

class Like {
    
    private $conn;
    private $table = "likes";
    
    public $id;
    public $usuario_id;
    public $publicacion_id;
    public $fecha;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // =============================
    // Dar like a una publicación
    // =============================
    public function darLike() {
        // Verificar si ya dio like
        if ($this->yaDioLike()) {
            return false; // Ya existe el like
        }
        
        $query = "INSERT INTO " . $this->table . " (usuario_id, publicacion_id) VALUES (:usuario_id, :publicacion_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":publicacion_id", $this->publicacion_id);
        
        return $stmt->execute();
    }
    
    // =============================
    // Quitar like (dislike)
    // =============================
    public function quitarLike() {
        $query = "DELETE FROM " . $this->table . " WHERE usuario_id = :usuario_id AND publicacion_id = :publicacion_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":publicacion_id", $this->publicacion_id);
        
        return $stmt->execute();
    }
    
    // =============================
    // Verificar si ya dio like
    // =============================
    public function yaDioLike() {
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id AND publicacion_id = :publicacion_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":publicacion_id", $this->publicacion_id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    // =============================
    // Contar likes de una publicación
    // =============================
    public function contarLikes($publicacion_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE publicacion_id = :publicacion_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":publicacion_id", $publicacion_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    // =============================
    // Obtener usuarios que dieron like a una publicación
    // =============================
    public function obtenerUsuariosLike($publicacion_id) {
        $query = "SELECT u.id, u.nombre, u.email, l.fecha 
                  FROM " . $this->table . " l
                  JOIN usuarios u ON l.usuario_id = u.id
                  WHERE l.publicacion_id = :publicacion_id
                  ORDER BY l.fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":publicacion_id", $publicacion_id);
        $stmt->execute();
        
        return $stmt;
    }
}