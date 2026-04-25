<?php
// ==========================
// Cerrar sesión
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir todas las variables de sesión
session_destroy();

// Redirigir al inicio
header("Location: index.php");
exit;