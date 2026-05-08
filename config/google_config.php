<?php
require_once __DIR__ . '/../vendor/autoload.php';

// 1. ID de Cliente
$clientID = '905944331269-67er5q98949p6mchva65981bn32h9ri0.apps.googleusercontent.com';

// 2. Pon aquí tu Secreto de Cliente
$clientSecret = 'Aqui va el clientSecret';

// 3. La URL de retorno (usando la IP de tu celular/XAMPP que estamos usando)
$redirectUri = 'http://localhost/Eq3_PI_web_ODS7/frontend/pages/google_callback.php';

// Crear el cliente de Google
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
?>