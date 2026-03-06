<?php

require_once __DIR__ . "/../models/Publicacion.php";

class PublicacionController {

    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    // ==========================
    // Crear publicación
    // ==========================

    public function crear($titulo, $contenido, $imagen, $categoria_id, $usuario_id){

        $publicacion = new Publicacion($this->db);

        $publicacion->titulo = $titulo;
        $publicacion->contenido = $contenido;
        $publicacion->imagen = $imagen;
        $publicacion->categoria_id = $categoria_id;
        $publicacion->usuario_id = $usuario_id;

        // cuando se crea queda pendiente de revisión
        $publicacion->estado = "pendiente";

        if($publicacion->crear()){
            return "Publicacion creada correctamente";
        }

        return "Error al crear la publicación";
    }


    // ==========================
    // Obtener publicaciones publicadas
    // ==========================

    public function obtenerPublicadas(){

        $publicacion = new Publicacion($this->db);

        return $publicacion->obtenerPublicadas();
    }


    // ==========================
    // Obtener por categoria
    // ==========================

    public function obtenerPorCategoria($categoria_id){

        $publicacion = new Publicacion($this->db);

        return $publicacion->obtenerPorCategoria($categoria_id);
    }


    // ==========================
    // Obtener una publicación
    // ==========================

    public function obtenerPorId($id){

        $publicacion = new Publicacion($this->db);

        return $publicacion->obtenerPorId($id);
    }

}