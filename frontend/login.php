<?php

$email = $_POST['email'];
$password = $_POST['password'];

function validarUsuario($email,$password){

    $user = "carlos.pro@redrenovable.com";
    $pass = "123456";

    if($email == $user && $password == $pass){
        return true;
    }else{
        return false;
    }

}

if(validarUsuario($email,$password)){

    echo "Login correcto";

}else{

    echo "Usuario o contraseña incorrectos";

}

?>