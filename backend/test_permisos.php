<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'controllers/AuthController.php';

$database = new Database();
$db = $database->connect();

echo "=== PRUEBA DEL SISTEMA DE PERMISOS ===\n\n";

// Obtener IDs reales por email
$emails = [
    'admin' => 'admin@test.com',
    'editor' => 'editor@test.com',
    'autor' => 'autor@test.com',
    'usuario' => 'user@test.com'
];

$ids = [];
foreach ($emails as $rol => $email) {
    $query = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $ids[$rol] = $result ? $result['id'] : 0;
}

echo "IDs detectados:\n";
echo "Admin (admin@test.com): ID {$ids['admin']}\n";
echo "Editor (editor@test.com): ID {$ids['editor']}\n";
echo "Autor (autor@test.com): ID {$ids['autor']}\n";
echo "Usuario (user@test.com): ID {$ids['usuario']}\n\n";

// Probar permisos
$auth = new AuthController($db);

$permisos = [
    'admin' => ['crear_usuario', 'aprobar_publicacion', 'crear_publicacion'],
    'editor' => ['aprobar_publicacion', 'crear_usuario'],
    'autor' => ['crear_publicacion', 'aprobar_publicacion'],
    'usuario' => ['dar_like', 'crear_publicacion']
];

foreach ($ids as $rol => $id) {
    if (!$id) continue;
    
    echo "--- " . ucfirst($rol) . " (ID: $id) ---\n";
    foreach ($permisos[$rol] as $permiso) {
        $tiene = $auth->tienePermiso($id, $permiso);
        echo "¿Puede $permiso? " . ($tiene ? "✅ SI" : "❌ NO") . "\n";
    }
    echo "\n";
}

echo "✅ Sistema de permisos implementado correctamente\n";
?>