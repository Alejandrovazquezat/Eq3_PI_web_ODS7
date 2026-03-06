<?php

require_once __DIR__ . "/../models/Categories.php";
require_once __DIR__ . "/AuthController.php";

class CategoriesController {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // ==========================
    // Obtener todas las categorías (público)
    // ==========================
    public function obtenerTodas() {
        $categorias = new Categories($this->db);
        return $categorias->obtenerTodas();
    }
    
    // ==========================
    // Obtener categoría por ID (público)
    // ==========================
    public function obtenerPorId($id) {
        $categorias = new Categories($this->db);
        return $categorias->obtenerPorId($id);
    }
    
    // ==========================
    // Crear categoría (solo admin)
    // ==========================
    public function crear($nombre, $descripcion, $admin_id) {
        
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'gestionar_categorias')) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para crear categorías'
            ];
        }
        
        if (empty($nombre)) {
            return [
                'success' => false,
                'message' => 'El nombre de la categoría es obligatorio'
            ];
        }
        
        $categoria = new Categories($this->db);
        $categoria->nombre = $nombre;
        $categoria->descripcion = $descripcion;
        
        if ($categoria->crear()) {
            return [
                'success' => true,
                'message' => 'Categoría creada correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear la categoría'
            ];
        }
    }
    
    // ==========================
    // Actualizar categoría (solo admin)
    // ==========================
    public function actualizar($id, $nombre, $descripcion, $admin_id) {
        
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'gestionar_categorias')) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para actualizar categorías'
            ];
        }
        
        $categoria = new Categories($this->db);
        $categoria->id = $id;
        $categoria->nombre = $nombre;
        $categoria->descripcion = $descripcion;
        
        if ($categoria->actualizar()) {
            return [
                'success' => true,
                'message' => 'Categoría actualizada correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar la categoría'
            ];
        }
    }
    
    // ==========================
    // Eliminar categoría (solo admin)
    // ==========================
    public function eliminar($id, $admin_id) {
        
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'gestionar_categorias')) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para eliminar categorías'
            ];
        }
        
        // Verificar si hay publicaciones usando esta categoría
        $query = "SELECT COUNT(*) as total FROM publicaciones WHERE categoria_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['total'] > 0) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene publicaciones asociadas'
            ];
        }
        
        $categoria = new Categories($this->db);
        $categoria->id = $id;
        
        if ($categoria->eliminar()) {
            return [
                'success' => true,
                'message' => 'Categoría eliminada correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar la categoría'
            ];
        }
    }
    
    // ==========================
    // Obtener categorías con conteo de publicaciones
    // ==========================
    public function obtenerConConteo() {
        $query = "SELECT c.*, COUNT(p.id) as total_publicaciones 
                  FROM categorias c
                  LEFT JOIN publicaciones p ON c.id = p.categoria_id AND p.estado = 'publicado'
                  GROUP BY c.id
                  ORDER BY c.nombre";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}