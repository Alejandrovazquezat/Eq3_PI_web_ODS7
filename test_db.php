<?php

require_once "backend/config/database.php";

$database = new Database();
$db = $database->connect();

if($db){
    echo "Conexion exitosa a la base de datos";
} else {
    echo "Error al conectar a la base de datos";
}