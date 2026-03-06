<?php

require_once __DIR__ . "/../models/Usuario.php";

class AuthController {

    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    // ==========================
    // REGISTRO DE USUARIO
    // ==========================

    public function registrar($nombre, $email, $password){

        $usuario = new Usuario($this->db);

        $usuario->nombre = $nombre;
        $usuario->email = $email;
        $usuario->password = $password;

        // rol usuario normal
        $usuario->rol_id = 4;

        // verificar si el email ya existe
        $stmt = $usuario->buscarPorEmail();

        if($stmt->rowCount() > 0){
            return "El email ya está registrado";
        }

        if($usuario->registrar()){
            return "Registro exitoso";
        }

        return "Error al registrar usuario";
    }


    // ==========================
    // LOGIN
    // ==========================

    public function login($email, $password){

        $usuario = new Usuario($this->db);
        $usuario->email = $email;

        $stmt = $usuario->buscarPorEmail();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            return "Usuario no encontrado";
        }

        if(password_verify($password, $row['password'])){

            session_start();

            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['rol'] = $row['rol_id'];

            return "Login correcto";

        }else{
            return "Contraseña incorrecta";
        }
    }


    // ==========================
    // LOGOUT
    // ==========================

    public function logout(){

        session_start();
        session_destroy();

        return "Sesion cerrada";
    }

}