<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { $_SESSION['usuario_id'] = 1; }
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imgBinaria = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imgBinaria = file_get_contents($_FILES['imagen']['tmp_name']);
    }

    try {
        $sql = "INSERT INTO publicaciones (titulo, contenido, imagen, estado, usuario_id, categoria_id) VALUES (?, ?, ?, 'pendiente', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(1, $_POST['titulo']);
        $stmt->bindParam(2, $_POST['contenido']);
        $stmt->bindParam(3, $imgBinaria, PDO::PARAM_LOB);
        $stmt->bindParam(4, $_SESSION['usuario_id']);
        $stmt->bindParam(5, $_POST['categoria_id']);
        $stmt->execute();
        header("Location: publicaciones.php");
        exit();
    } catch (Exception $e) { echo "Error: " . $e->getMessage(); }
}
$cats = $db->query("SELECT * FROM categorias")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Nueva Iniciativa - RedRenovable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box">
            <img src="logo para la pagina.jpeg" alt="Logo">
            <div class="logo-name">RedRenovable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link">Proyectos</a>
        <a href="crear_publicacion.php" class="nav-link active">+ Nueva Iniciativa</a>
    </div>

    <main class="main">
        <div class="card" style="max-width: 650px; margin: auto;">
            <h2>Publicar nueva iniciativa</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>TÍTULO</label>
                <input type="text" name="titulo" class="form-input" required>
                
                <label>CATEGORÍA</label>
                <select name="categoria_id" class="form-input">
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label>IMAGEN DE REFERENCIA</label>
                <input type="file" name="imagen" class="form-input" accept="image/*">
                
                <label>DESCRIPCIÓN DEL PROYECTO</label>
                <textarea name="contenido" class="form-input" rows="5" required></textarea>
                
                <button type="submit" class="btn-blue">PUBLICAR PROYECTO</button>
            </form>
        </div>
    </main>
</body>
</html>