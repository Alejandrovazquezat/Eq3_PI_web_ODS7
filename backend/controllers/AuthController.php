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
        
        // Validaciones básicas
        if (empty($nombre) || empty($email) || empty($password)) {
            return "Todos los campos son obligatorios";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email no válido";
        }
        
        if (strlen($password) < 6) {
            return "La contraseña debe tener al menos 6 caracteres";
        }

        $usuario = new Usuario($this->db);

        $usuario->nombre = $nombre;
        $usuario->email = $email;
        $usuario->password = $password;

        // Obtener el ID del rol 'usuario' (rol normal)
        $query = "SELECT id FROM roles WHERE nombre = 'usuario'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario->rol_id = $rol['id'] ?? 4; // Si no encuentra, usa 4 como fallback

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
        
        if (empty($email) || empty($password)) {
            return "Email y contraseña son obligatorios";
        }

        $usuario = new Usuario($this->db);
        $usuario->email = $email;

        $stmt = $usuario->buscarPorEmail();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            return "Usuario no encontrado";
        }

        if(password_verify($password, $row['password'])){

            // --- INICIO: Unificación de sesión ---
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Claves canónicas
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['rol_id'] = $row['rol_id'];
            $_SESSION['logueado'] = true; // para chequeos rápidos
            
            // Obtener nombre del rol para facilitar
            $query = "SELECT nombre FROM roles WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $row['rol_id']);
            $stmt->execute();
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['rol_nombre'] = $rol['nombre'] ?? 'usuario';
            // --- FIN unificación ---

            return "Login correcto";

        }else{
            return "Contraseña incorrecta";
        }
    }

    // ==========================
    // LOGOUT
    // ==========================
    public function logout(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        return "Sesion cerrada";
    }

    // ==========================
    // VERIFICAR PERMISOS POR ROL
    // ==========================
    public function tienePermiso($usuario_id, $accion_requerida) {
        
        if (!$usuario_id) return false;
        
        // Obtener el rol del usuario
        $query = "SELECT r.nombre as rol_nombre, r.id as rol_id 
                  FROM usuarios u 
                  JOIN roles r ON u.rol_id = r.id 
                  WHERE u.id = :usuario_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) return false;
        
        $rol = $usuario['rol_nombre']; // 'admin', 'editor', 'autor', 'usuario'
        
        // Definir permisos por rol
        
        $permisos = [
            'admin' => [
            // Acciones propias de admin
            'crear_usuario', 'editar_usuario', 'eliminar_usuario',
            'gestionar_categorias', 'ver_estadisticas',
            // Acciones de editor
            'ver_publicaciones_pendientes', 'aprobar_publicacion', 'rechazar_publicacion',
            'editar_cualquier_publicacion', 'publicar_publicacion',
            'moderar_comentarios', 'publicar_directo', 'ver_todas_publicaciones',
            // Acciones de autor
            'crear_publicacion', 'editar_mis_publicaciones', 'eliminar_mis_publicaciones',
            'ver_mis_publicaciones', 'subir_imagenes',
            // Acciones de usuario básico
            'ver_publicaciones_publicadas', 'comentar', 'dar_like'
            ],

            'editor' => [
            'ver_publicaciones_pendientes', 'aprobar_publicacion', 'rechazar_publicacion',
            'editar_cualquier_publicacion', 'publicar_publicacion',
            'ver_estadisticas', 'moderar_comentarios',
            'publicar_directo', 'ver_todas_publicaciones'
                ],
            'autor' => [
            'crear_publicacion', 'editar_mis_publicaciones', 'eliminar_mis_publicaciones',
            'ver_mis_publicaciones', 'subir_imagenes'
            ],
            'usuario' => [
            'ver_publicaciones_publicadas', 'comentar', 'dar_like'
            ]
        ];
        
        return in_array($accion_requerida, $permisos[$rol] ?? []);
    }
    
    // ==========================
    // OBTENER ROL DEL USUARIO ACTUAL
    // ==========================
    public function obtenerRolActual() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario_id'])) {
            return null;
        }
        
        return [
            'id' => $_SESSION['rol_id'],
            'nombre' => $_SESSION['rol_nombre']
        ];
    }
}