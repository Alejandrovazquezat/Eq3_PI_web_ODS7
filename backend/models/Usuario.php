<?php

class Usuario {

    private $conn;
    private $table = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $rol_id;

    public function __construct($db){
        $this->conn = $db;
    }

    // =============================
    // Registrar usuario
    // =============================
    public function registrar(){

        $query = "INSERT INTO " . $this->table . "
                  (nombre, email, password, rol_id)
                  VALUES (:nombre, :email, :password, :rol_id)";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":rol_id", $this->rol_id);

        return $stmt->execute();
    }


    // =============================
    // Buscar usuario por email
    // =============================
    public function buscarPorEmail(){

        $query = "SELECT * FROM " . $this->table . "
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email", $this->email);

        $stmt->execute();

        return $stmt;
    }

    // =============================
    // Obtener usuarios por rol
    // =============================
    public function obtenerPorRol($rol_id) {
        $query = "SELECT id, nombre, email, fecha_creacion 
                  FROM " . $this->table . " 
                  WHERE rol_id = :rol_id 
                  ORDER BY fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rol_id", $rol_id);
        $stmt->execute();
        
        return $stmt;
    }

    // =============================
    // Obtener todos los usuarios con su rol
    // =============================
    public function obtenerTodos() {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM " . $this->table . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  ORDER BY u.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

}   