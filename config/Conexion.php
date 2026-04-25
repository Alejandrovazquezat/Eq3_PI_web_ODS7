<?php

class Conexion {
    private $host = "localhost";
    private $db_name = "plataforma_contenidos";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConexion() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("set names utf8");
            } catch (PDOException $exception) {
                // En producción, no muestres el error detallado
                die("Error de conexión a la base de datos.");
            }
        }
        return $this->conn;
    }
}