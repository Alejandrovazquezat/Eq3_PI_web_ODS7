<?php

require_once __DIR__ . "/../models/Publicacion.php";
require_once __DIR__ . "/AuthController.php";

class PublicacionController {

    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    // ==========================
    // Subir Imagen Física
    // ==========================
    public function subirImagenFisica($archivo) {
        if (isset($archivo) && $archivo['error'] === UPLOAD_ERR_OK) {
            $directorio_destino = __DIR__ . '/../../assets/uploads/';
            
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($extension, $permitidas)) {
                return "Error: Formato de imagen no permitido.";
            }

            $nombre_unico = uniqid('post_') . '.' . $extension;
            $ruta_absoluta = $directorio_destino . $nombre_unico;

            if (move_uploaded_file($archivo['tmp_name'], $ruta_absoluta)) {
                return 'uploads/' . $nombre_unico;
            } else {
                return "Error: No se pudo guardar la imagen físicamente.";
            }
        }
        return null;
    }

    // ==========================
    // Crear publicación
    // ==========================
    public function crear($titulo, $contenido, $archivo_imagen, $categoria_id, $usuario_id){
        if (empty($titulo) || empty($contenido)) {
            return "Título y contenido son obligatorios";
        }
        
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($usuario_id, 'crear_publicacion')) {
            return "Error: No tienes permiso para crear publicaciones";
        }
        
        $query = "SELECT rol_id FROM usuarios WHERE id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $estado = ($usuario['rol_id'] <= 2) ? 'publicado' : 'pendiente';
        
        $ruta_imagen = null;
        if ($archivo_imagen && isset($archivo_imagen['size']) && $archivo_imagen['size'] > 0) {
            $resultado_subida = $this->subirImagenFisica($archivo_imagen);
            if (strpos($resultado_subida, 'Error') === 0) {
                return $resultado_subida;
            }
            $ruta_imagen = $resultado_subida;
        }

        $publicacion = new Publicacion($this->db);
        $publicacion->titulo = $titulo;
        $publicacion->contenido = $contenido;
        $publicacion->imagen = $ruta_imagen;
        $publicacion->categoria_id = $categoria_id;
        $publicacion->usuario_id = $usuario_id;
        $publicacion->estado = $estado;
        
        if($publicacion->crear()){
            return "Publicación creada correctamente. Estado: " . $estado;
        }
        return "Error al crear la publicación";
    }

    // ==========================
    // Editar publicación
    // ==========================
    public function editar($id, $titulo, $contenido, $categoria_id, $usuario_id, $archivo_imagen = null) {
        if (empty($titulo) || empty($contenido)) {
            return "Título y contenido son obligatorios";
        }
        
        $auth = new AuthController($this->db);
        $puede_editar = false;

        if ($auth->tienePermiso($usuario_id, 'editar_cualquier_publicacion')) {
            $puede_editar = true;
        } elseif ($auth->tienePermiso($usuario_id, 'editar_mis_publicaciones')) {
            $query = "SELECT id FROM publicaciones WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $puede_editar = true;
            }
        }
        
        if (!$puede_editar) {
            return "Error: No tienes permiso para editar";
        }

        $ruta_imagen = null;
        if ($archivo_imagen && isset($archivo_imagen['size']) && $archivo_imagen['size'] > 0) {
            $resultado_subida = $this->subirImagenFisica($archivo_imagen);
            if (strpos($resultado_subida, 'Error') === 0) {
                return $resultado_subida;
            }
            $ruta_imagen = $resultado_subida;
        }

        return $this->ejecutarEdicion($id, $titulo, $contenido, $categoria_id, $ruta_imagen);
    }

    // ==========================
    // Ejecutar edición (Maneja si hay nueva imagen o no)
    // ==========================
    private function ejecutarEdicion($id, $titulo, $contenido, $categoria_id, $ruta_imagen) {
        if ($ruta_imagen) {
            // Actualiza también la imagen
            $query = "UPDATE publicaciones SET titulo = :titulo, contenido = :contenido, imagen = :imagen, categoria_id = :categoria_id WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":imagen", $ruta_imagen);
        } else {
            // Conserva la imagen que ya tenía
            $query = "UPDATE publicaciones SET titulo = :titulo, contenido = :contenido, categoria_id = :categoria_id WHERE id = :id";
            $stmt = $this->db->prepare($query);
        }
        
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":contenido", $contenido);
        $stmt->bindParam(":categoria_id", $categoria_id);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute() ? "Publicación actualizada correctamente" : "Error al actualizar";
    }

    // ... (Mantén aquí tus otros métodos: aprobar, rechazar, obtenerPendientes, obtenerPublicadas, obtenerTodas, obtenerPorCategoria, obtenerPorId, eliminar) ...
    
    public function aprobar($publicacion_id, $admin_id) {
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'aprobar_publicacion')) return "Error de permisos";
        $stmt = $this->db->prepare("UPDATE publicaciones SET estado = 'publicado' WHERE id = :id");
        $stmt->bindParam(":id", $publicacion_id);
        return $stmt->execute() ? "Publicación aprobada" : "Error al aprobar";
    }

    public function rechazar($publicacion_id, $admin_id) {
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'rechazar_publicacion')) return "Error de permisos";
        $stmt = $this->db->prepare("UPDATE publicaciones SET estado = 'rechazado' WHERE id = :id");
        $stmt->bindParam(":id", $publicacion_id);
        return $stmt->execute() ? "Publicación rechazada" : "Error al rechazar";
    }

    public function obtenerPendientes($usuario_id) {
        $stmt = $this->db->prepare("SELECT p.*, u.nombre as autor, c.nombre as categoria FROM publicaciones p LEFT JOIN usuarios u ON p.usuario_id = u.id LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.estado = 'pendiente' ORDER BY p.fecha_creacion DESC");
        $stmt->execute();
        return $stmt;
    }

    public function obtenerPublicadas(){
        $publicacion = new Publicacion($this->db);
        return $publicacion->obtenerPublicadas();
    }

    public function obtenerTodas($usuario_id) {
        $publicacion = new Publicacion($this->db);
        return $publicacion->obtenerTodas();
    }

    public function obtenerPorCategoria($categoria_id){
        $publicacion = new Publicacion($this->db);
        return $publicacion->obtenerPorCategoria($categoria_id);
    }

    public function obtenerPorId($id, $usuario_id = null){
        $publicacion = new Publicacion($this->db);
        $stmt = $publicacion->obtenerPorId($id);
        $pub = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pub) return null;
        if ($pub['estado'] == 'publicado') return $pub;
        if ($usuario_id) {
            $auth = new AuthController($this->db);
            if ($auth->tienePermiso($usuario_id, 'ver_todas_publicaciones') || $pub['usuario_id'] == $usuario_id) {
                return $pub;
            }
        }
        return null;
    }

    public function eliminar($id, $usuario_id) {
        $auth = new AuthController($this->db);
        if ($auth->tienePermiso($usuario_id, 'eliminar_cualquier_publicacion')) {
            $stmt = $this->db->prepare("DELETE FROM publicaciones WHERE id = :id");
            $stmt->bindParam(":id", $id);
            return $stmt->execute() ? "Publicación eliminada" : "Error";
        }
        if ($auth->tienePermiso($usuario_id, 'eliminar_mis_publicaciones')) {
            $stmt = $this->db->prepare("DELETE FROM publicaciones WHERE id = :id AND usuario_id = :usuario_id");
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            if ($stmt->execute() && $stmt->rowCount() > 0) return "Publicación eliminada";
        }
        return "Error: No tienes permiso para eliminar";
    }
}