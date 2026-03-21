<?php
session_start();
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();

$mensaje = "";
$error = "";

// Obtener categorías para el select
$categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = $_POST['categoria_id'] ?: null;
    $estado = $_POST['estado'];
    $imagen_binaria = null;
    
    // Validar campos obligatorios
    if (empty($titulo) || empty($contenido)) {
        $error = "El título y el contenido son obligatorios";
    } else {
        // Procesar imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            
            if (in_array($extension, $allowed)) {
                // Leer la imagen como binario
                $imagen_binaria = file_get_contents($_FILES['imagen']['tmp_name']);
            } else {
                $error = "Formato de imagen no permitido. Usa: jpg, jpeg, png, gif, webp";
            }
        }
        
        if (empty($error)) {
            try {
                $usuario_id = $_SESSION['user_id'] ?? 1;
                
                $query = "INSERT INTO publicaciones (titulo, contenido, imagen, categoria_id, estado, usuario_id) 
                          VALUES (:titulo, :contenido, :imagen, :categoria_id, :estado, :usuario_id)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':contenido', $contenido);
                $stmt->bindParam(':imagen', $imagen_binaria, PDO::PARAM_LOB);
                $stmt->bindParam(':categoria_id', $categoria_id);
                $stmt->bindParam(':estado', $estado);
                $stmt->bindParam(':usuario_id', $usuario_id);
                
                if ($stmt->execute()) {
                    $mensaje = "¡Publicación creada con éxito!";
                    // Limpiar formulario
                    $titulo = $contenido = "";
                } else {
                    $error = "Error al guardar la publicación";
                }
            } catch (PDOException $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../css/crear_publicacion_styles.css">
    <title>Crear Publicación - RedRenovable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RedRenovable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link">Publicaciones</a>
        <a href="usuarios.php" class="nav-link">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link active">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Crear Nueva Publicación</h1>
        
        <div class="form-container">
            <?php if ($mensaje): ?>
                <div class="mensaje-exito"><?= $mensaje ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="mensaje-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título *</label>
                    <input type="text" id="titulo" name="titulo" required value="<?= htmlspecialchars($titulo ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="categoria_id">Categoría *</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <option value="">-- Seleccionar categoría --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="borrador">Borrador</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="publicado" selected>Publicado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen (opcional)</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)">
                    <div id="preview" class="preview-imagen"></div>
                </div>
                
                <div class="form-group">
                    <label for="contenido">Contenido *</label>
                    <textarea id="contenido" name="contenido" required><?= htmlspecialchars($contenido ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit">Publicar</button>
            </form>
        </div>
    </main>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>