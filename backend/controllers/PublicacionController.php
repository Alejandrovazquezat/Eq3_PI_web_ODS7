<?php

require_once __DIR__ . "/../models/Publicacion.php";
require_once __DIR__ . "/AuthController.php";

class PublicacionController {

    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    // ==========================
    // Crear publicación
    // ==========================
    public function crear($titulo, $contenido, $imagen, $categoria_id, $usuario_id){
        
        // Validaciones básicas
        if (empty($titulo) || empty($contenido)) {
            return "Título y contenido son obligatorios";
        }
        
        // Primero verificar si puede crear publicaciones
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($usuario_id, 'crear_publicacion')) {
            return "Error: No tienes permiso para crear publicaciones";
        }
        
        // Si es autor, queda pendiente; si es editor/admin, puede publicar directo
        $query = "SELECT rol_id FROM usuarios WHERE id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // admin(1) y editor(2) pueden publicar directo, autor(3) queda pendiente
        $estado = ($usuario['rol_id'] <= 2) ? 'publicado' : 'pendiente';
        
        $publicacion = new Publicacion($this->db);
        $publicacion->titulo = $titulo;
        $publicacion->contenido = $contenido;
        $publicacion->imagen = $imagen;
        $publicacion->categoria_id = $categoria_id;
        $publicacion->usuario_id = $usuario_id;
        $publicacion->estado = $estado;
        
        if($publicacion->crear()){
            return "Publicación creada correctamente. Estado: " . $estado;
        }
        return "Error al crear la publicación";
    }

    // ==========================
    // Aprobar publicación (solo admin/editor)
    // ==========================
    public function aprobar($publicacion_id, $admin_id) {
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'aprobar_publicacion')) {
            return "Error: No tienes permiso para aprobar publicaciones";
        }
        
        $query = "UPDATE publicaciones SET estado = 'publicado' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $publicacion_id);
        
        return $stmt->execute() ? "Publicación aprobada" : "Error al aprobar";
    }

    // ==========================
    // Rechazar publicación (solo admin/editor)
    // ==========================
    public function rechazar($publicacion_id, $admin_id, $motivo = null) {
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'rechazar_publicacion')) {
            return "Error: No tienes permiso para rechazar publicaciones";
        }
        
        $query = "UPDATE publicaciones SET estado = 'rechazado' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $publicacion_id);
        
        // Aquí podrías guardar el motivo en otra tabla si quisieras
        
        return $stmt->execute() ? "Publicación rechazada" : "Error al rechazar";
    }

    // ==========================
    // Obtener publicaciones pendientes (solo admin/editor)
    // ==========================
    public function obtenerPendientes($usuario_id) {
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($usuario_id, 'ver_publicaciones_pendientes')) {
            return "Error: No tienes permiso para ver publicaciones pendientes";
        }
        
        $query = "SELECT p.*, u.nombre as autor, c.nombre as categoria 
                  FROM publicaciones p
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.estado = 'pendiente'
                  ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // ==========================
    // Obtener publicaciones publicadas (todos pueden ver)
    // ==========================
    public function obtenerPublicadas(){
        $publicacion = new Publicacion($this->db);
        return $publicacion->obtenerPublicadas();
    }

    // ==========================
    // Obtener TODAS las publicaciones (solo admin/editor)
    // ==========================
    public function obtenerTodas($usuario_id) {
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($usuario_id, 'ver_todas_publicaciones')) {
            return "Error: No tienes permiso para ver todas las publicaciones";
        }
        
        $publicacion = new Publicacion($this->db);
        return $publicacion->obtenerTodas();
    }

    // ==========================
    // Obtener por categoria (público)
    // ==========================
    public function obtenerPorCategoria($categoria_id){
        $publicacion = new Publicacion($this->db);
        return $publicacion->obtenerPorCategoria($categoria_id);
    }

    // ==========================
    // Obtener una publicación por ID (con control de permisos)
    // ==========================
    public function obtenerPorId($id, $usuario_id = null){
        $publicacion = new Publicacion($this->db);
        $stmt = $publicacion->obtenerPorId($id);
        $pub = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pub) return null;
        
        // Si es pública, cualquiera puede verla
        if ($pub['estado'] == 'publicado') {
            return $pub;
        }
        
        // Si no está publicada, verificar permisos
        if ($usuario_id) {
            $auth = new AuthController($this->db);
            
            // Admin/editor pueden ver cualquier publicación
            if ($auth->tienePermiso($usuario_id, 'ver_todas_publicaciones')) {
                return $pub;
            }
            
            // Autores pueden ver sus propias publicaciones sin importar el estado
            if ($pub['usuario_id'] == $usuario_id) {
                return $pub;
            }
        }
        
        return null; // No tiene permiso
    }

    // ==========================
    // Editar publicación (con control de permisos)
    // ==========================
    public function editar($id, $datos, $usuario_id) {
        if (empty($datos['titulo']) || empty($datos['contenido'])) {
            return "Título y contenido son obligatorios";
        }
        
        $auth = new AuthController($this->db);
        
        // Verificar si es admin/editor (pueden editar cualquier publicación)
        if ($auth->tienePermiso($usuario_id, 'editar_cualquier_publicacion')) {
            return $this->ejecutarEdicion($id, $datos);
        }
        
        // Verificar si es autor (solo puede editar sus propias publicaciones)
        if ($auth->tienePermiso($usuario_id, 'editar_mis_publicaciones')) {
            // Verificar que sea su publicación
            $query = "SELECT id FROM publicaciones WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $this->ejecutarEdicion($id, $datos);
            } else {
                return "Error: Solo puedes editar tus propias publicaciones";
            }
        }
        
        return "Error: No tienes permiso para editar";
    }

    // ==========================
    // Ejecutar edición (método privado)
    // ==========================
    private function ejecutarEdicion($id, $datos) {
        $query = "UPDATE publicaciones SET 
                  titulo = :titulo, 
                  contenido = :contenido, 
                  imagen = :imagen, 
                  categoria_id = :categoria_id 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":titulo", $datos['titulo']);
        $stmt->bindParam(":contenido", $datos['contenido']);
        $stmt->bindParam(":imagen", $datos['imagen']);
        $stmt->bindParam(":categoria_id", $datos['categoria_id']);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute() ? "Publicación actualizada" : "Error al actualizar";
    }

    // ==========================
    // Eliminar publicación (con control de permisos)
    // ==========================
    public function eliminar($id, $usuario_id) {
        $auth = new AuthController($this->db);
        
        // Admin puede eliminar cualquier publicación
        if ($auth->tienePermiso($usuario_id, 'eliminar_cualquier_publicacion')) {
            $query = "DELETE FROM publicaciones WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute() ? "Publicación eliminada" : "Error al eliminar";
        }
        
        // Autor puede eliminar sus propias publicaciones
        if ($auth->tienePermiso($usuario_id, 'eliminar_mis_publicaciones')) {
            $query = "DELETE FROM publicaciones WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return "Publicación eliminada";
            } else {
                return "Error: No puedes eliminar esta publicación o no existe";
            }
        }
        
        return "Error: No tienes permiso para eliminar";
    }
}