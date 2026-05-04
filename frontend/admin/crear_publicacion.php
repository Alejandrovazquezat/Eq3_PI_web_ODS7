<?php
session_start();
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();

$mensaje = "";
$error = "";

$categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = $_POST['categoria_id'] ?: null;
    $estado = $_POST['estado'];
    $imagen_binaria = null;
    
    if (empty($titulo) || empty($contenido)) {
        $error = "El título y el contenido son obligatorios.";
    } else {
        // Validación y lectura de imagen para guardado binario
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            // Se abre el archivo en modo binario 'rb' para enviarlo a la DB
            $imagen_binaria = fopen($_FILES['imagen']['tmp_name'], 'rb');
        }
        
        try {
            $usuario_id = $_SESSION['user_id'] ?? 1;
            $query = "INSERT INTO publicaciones (titulo, contenido, imagen, categoria_id, estado, usuario_id) 
                      VALUES (:titulo, :contenido, :imagen, :categoria_id, :estado, :usuario_id)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':contenido', $contenido);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':usuario_id', $usuario_id);
            
            // Se vincula como PARAM_LOB para que la base de datos acepte el flujo binario
            $stmt->bindParam(':imagen', $imagen_binaria, PDO::PARAM_LOB);
            
            if ($stmt->execute()) {
                $mensaje = "¡Publicación creada con éxito!";
                $titulo = $contenido = "";
            } else {
                $error = "No se pudo guardar la publicación.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css_dash/style.css"> <link rel="stylesheet" href="../css_dash/crear_publicacion_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Nueva Publicación - RED-novable</title>
</head>
<body>

    <nav class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RED-novable</div>
        </div>
        <div class="menu-groups">
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="publicaciones.php" class="nav-link">📝 Publicaciones</a>
            <a href="usuarios.php" class="nav-link">👥 Usuarios</a>
            <a href="crear_publicacion.php" class="nav-link btn-special active">+ Nueva publicación</a>
        </div>
    </nav>

    <main class="main">
        <header class="main-header">
            <h1>Crear Publicación</h1>
            <p>Completa el formulario para publicar nuevo contenido.</p>
        </header>

        <section class="form-container-card">
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $mensaje ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="modern-grid-form">
                <div class="main-fields">
                    <div class="field">
                        <label>Título del artículo</label>
                        <input type="text" name="titulo" placeholder="Escribe el título aquí..." required value="<?= htmlspecialchars($titulo ?? '') ?>">
                    </div>

                    <div class="inline-fields">
                        <div class="field">
                            <label>Categoría</label>
                            <select name="categoria_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Visibilidad</label>
                            <select name="estado">
                                <option value="publicado">Publicado</option>
                                <option value="borrador">Borrador</option>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label>Contenido detallado</label>
                        <textarea name="contenido" placeholder="Desarrolla tu publicación..." required><?= htmlspecialchars($contenido ?? '') ?></textarea>
                    </div>
                </div>

                <div class="side-fields">
                    <label>Imagen de portada</label>
                    <div class="upload-zone" onclick="document.getElementById('imagen').click()">
                        <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)" hidden>
                        <div id="preview" class="preview-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Cargar imagen</span> </div>
                    </div>
                    <button type="submit" class="btn-publish">
                        <i class="fas fa-paper-plane"></i> Publicar ahora
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:15px;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>