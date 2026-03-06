<?php

class Publicacion {

    private $conn;
    private $table = "publicaciones";

    public $id;
    public $titulo;
    public $contenido;
    public $imagen;
    public $estado;
    public $usuario_id;
    public $categoria_id;

    public function __construct($db){
        $this->conn = $db;
    }

    // =============================
    // Crear publicación
    // =============================

    public function crear(){

        $query = "INSERT INTO " . $this->table . "
                  (titulo, contenido, imagen, estado, usuario_id, categoria_id)
                  VALUES (:titulo, :contenido, :imagen, :estado, :usuario_id, :categoria_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":contenido", $this->contenido);
        $stmt->bindParam(":imagen", $this->imagen);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":categoria_id", $this->categoria_id);

        return $stmt->execute();
    }


    // =============================
    // Obtener publicaciones
    // =============================

    public function obtenerTodas(){

        $query = "SELECT 
                    publicaciones.*,
                    usuarios.nombre as autor,
                    categorias.nombre as categoria
                  FROM publicaciones
                  LEFT JOIN usuarios ON publicaciones.usuario_id = usuarios.id
                  LEFT JOIN categorias ON publicaciones.categoria_id = categorias.id
                  ORDER BY fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }


    // =============================
    // Obtener solo publicadas
    // =============================

    public function obtenerPublicadas(){

        $query = "SELECT 
                    publicaciones.*,
                    usuarios.nombre as autor,
                    categorias.nombre as categoria
                  FROM publicaciones
                  LEFT JOIN usuarios ON publicaciones.usuario_id = usuarios.id
                  LEFT JOIN categorias ON publicaciones.categoria_id = categorias.id
                  WHERE estado = 'publicado'
                  ORDER BY fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }


    // =============================
    // Obtener por categoria
    // =============================

    public function obtenerPorCategoria($categoria_id){

        $query = "SELECT * FROM publicaciones
                  WHERE categoria_id = :categoria_id
                  AND estado = 'publicado'
                  ORDER BY fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":categoria_id", $categoria_id);

        $stmt->execute();

        return $stmt;
    }


    // =============================
    // Obtener una publicación
    // =============================

    public function obtenerPorId($id){

        $query = "SELECT * FROM publicaciones
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        return $stmt;
    }

}