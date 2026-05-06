<?php
// ==========================
// 1. Cargar dependencias (BACKEND DEL SEGUNDO CÓDIGO)
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php';

// ==========================
// 2. Conexión y sesión
// ==========================
$db = (new Conexion())->getConexion();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 3. Verificar autenticación y permiso
// ==========================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/inicioSesion.php");
    exit;
}

$auth = new AuthController($db);
$usuario_id = $_SESSION['usuario_id'];

if (!$auth->tienePermiso($usuario_id, 'crear_publicacion')) {
    header("Location: ../pages/index.php");
    exit;
}

// ==========================
// 4. Obtener categorías para el select (mediante CategoriesController)
// ==========================
$catController = new CategoriesController($db);
$categorias_stmt = $catController->obtenerTodas();
$categorias = is_object($categorias_stmt) ? $categorias_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// ==========================
// 5. Procesar formulario
// ==========================
$mensaje = "";
$error = "";
$titulo = $contenido = ""; // para mantener valores en caso de error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $categoria_id = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
    $imagen_binaria = null;
    
    if (empty($titulo) || empty($contenido)) {
        $error = "El título y el contenido son obligatorios";
    } else {
        // Procesar imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            
            if (in_array($extension, $allowed)) {
                $imagen_binaria = file_get_contents($_FILES['imagen']['tmp_name']);
            } else {
                $error = "Formato de imagen no permitido. Usa: jpg, jpeg, png, gif, webp";
            }
        }
        
        if (empty($error)) {
            $pubController = new PublicacionController($db);
            $resultado = $pubController->crear($titulo, $contenido, $imagen_binaria, $categoria_id, $usuario_id);
            
            if (strpos($resultado, 'correctamente') !== false) {
                $mensaje = $resultado;
                // Limpiar campos después de éxito
                $titulo = $contenido = "";
                $categoria_id = null;
            } else {
                $error = $resultado;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/crear_publicacion_styles.css">
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
            <a href="dashboard.php" class="nav-link">📊 Dashboard general</a>
            <a href="publicaciones.php" class="nav-link">📝 Publicaciones</a>
            <a href="revisar.php" class="nav-link">✅ Pendientes de revisión</a>
            <a href="usuarios.php" class="nav-link">👥 Usuarios</a>
            <a href="comentarios.php" class="nav-link">💬 Comentarios</a>
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
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="modern-grid-form">
                <div class="main-fields">
                    <div class="field">
                        <label>Título del artículo</label>
                        <input type="text" name="titulo" placeholder="Escribe el título aquí..." required value="<?= htmlspecialchars($titulo) ?>">
                    </div>

                    <div class="inline-fields">
                        <div class="field">
                            <label>Categoría</label>
                            <select name="categoria_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($categoria_id) && $categoria_id == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- El campo "Visibilidad" se elimina porque el controlador asigna el estado automáticamente según el rol -->
                    </div>

                    <div class="field">
                        <label>Contenido detallado</label>
                        <textarea name="contenido" placeholder="Desarrolla tu publicación..." required><?= htmlspecialchars($contenido) ?></textarea>
                    </div>
                </div>

                <div class="side-fields">
                    <label>Imagen de portada</label>
                    <div class="upload-zone" onclick="document.getElementById('imagen').click()">
                        <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)" hidden>
                        <div id="preview" class="preview-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Cargar imagen</span>
                        </div>
                    </div>
                    <button type="submit" class="btn-publish">
                        <i class="fas fa-paper-plane"></i> Publicar ahora
                    </button>
                    <p style="font-size: 0.8rem; margin-top: 10px; color: #64748b;">
                        * Si eres autor, la publicación quedará pendiente de aprobación.<br>
                        * Administradores y editores publican directamente.
                    </p>
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