<?php

class Categories {
    
    private $conn;
    private $table = "categorias";
    
    public $id;
    public $nombre;
    public $descripcion;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // =============================
    // Obtener todas las categorías
    // =============================
    public function obtenerTodas() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // =============================
    // Obtener una categoría por ID
    // =============================
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }
    
    // =============================
    // Crear categoría (solo admin)
    // =============================
    public function crear() {
        $query = "INSERT INTO " . $this->table . " (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        
        return $stmt->execute();
    }
    
    // =============================
    // Actualizar categoría (solo admin)
    // =============================
    public function actualizar() {
        $query = "UPDATE " . $this->table . " SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    // =============================
    // Eliminar categoría (solo admin)
    // =============================
    public function eliminar() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}