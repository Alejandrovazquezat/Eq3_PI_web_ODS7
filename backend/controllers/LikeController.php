<?php

require_once __DIR__ . "/../models/Like.php";
require_once __DIR__ . "/AuthController.php";

class LikeController {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // ==========================
    // Dar like a una publicación
    // ==========================
    public function darLike($usuario_id, $publicacion_id) {
        
        // Verificar permisos (solo usuarios registrados pueden dar like)
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($usuario_id, 'dar_like')) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para dar likes'
            ];
        }
        
        $like = new Like($this->db);
        $like->usuario_id = $usuario_id;
        $like->publicacion_id = $publicacion_id;
        
        if ($like->darLike()) {
            $total = $like->contarLikes($publicacion_id);
            return [
                'success' => true,
                'message' => 'Like agregado',
                'total_likes' => $total,
                'action' => 'like'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ya diste like a esta publicación'
            ];
        }
    }
    
    // ==========================
    // Quitar like
    // ==========================
    public function quitarLike($usuario_id, $publicacion_id) {
        
        $like = new Like($this->db);
        $like->usuario_id = $usuario_id;
        $like->publicacion_id = $publicacion_id;
        
        if ($like->quitarLike()) {
            $total = $like->contarLikes($publicacion_id);
            return [
                'success' => true,
                'message' => 'Like quitado',
                'total_likes' => $total,
                'action' => 'unlike'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al quitar like'
            ];
        }
    }
    
    // ==========================
    // Verificar si el usuario ya dio like
    // ==========================
    public function yaDioLike($usuario_id, $publicacion_id) {
        $like = new Like($this->db);
        $like->usuario_id = $usuario_id;
        $like->publicacion_id = $publicacion_id;
        
        return $like->yaDioLike();
    }
    
    // ==========================
    // Obtener total de likes de una publicación
    // ==========================
    public function totalLikes($publicacion_id) {
        $like = new Like($this->db);
        return $like->contarLikes($publicacion_id);
    }
    
    // ==========================
    // Obtener usuarios que dieron like
    // ==========================
    public function obtenerUsuariosLike($publicacion_id, $admin_id = null) {
        
        // Solo admin/editor pueden ver quién dio like
        if ($admin_id) {
            $auth = new AuthController($this->db);
            if (!$auth->tienePermiso($admin_id, 'ver_estadisticas')) {
                return [
                    'success' => false,
                    'message' => 'No tienes permiso para ver esta información'
                ];
            }
        }
        
        $like = new Like($this->db);
        $result = $like->obtenerUsuariosLike($publicacion_id);
        
        return [
            'success' => true,
            'usuarios' => $result->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
    
    // ==========================
    // Toggle like (si ya dio like, lo quita; si no, lo pone)
    // ==========================
    public function toggleLike($usuario_id, $publicacion_id) {
        $like = new Like($this->db);
        $like->usuario_id = $usuario_id;
        $like->publicacion_id = $publicacion_id;
        
        if ($like->yaDioLike()) {
            return $this->quitarLike($usuario_id, $publicacion_id);
        } else {
            return $this->darLike($usuario_id, $publicacion_id);
        }
    }
}