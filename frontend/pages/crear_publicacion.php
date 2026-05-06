<?php
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php';

$db = (new Conexion())->getConexion();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: inicioSesion.php");
    exit;
}

$auth = new AuthController($db);
$usuario_id = $_SESSION['usuario_id'];

// SI NO ES ADMIN, EDITOR O AUTOR, LO SACAMOS DE AQUÍ
if (!$auth->tienePermiso($usuario_id, 'crear_publicacion')) {
    header("Location: index.php");
    exit;
}

// Obtener categorías
$catController = new CategoriesController($db);
$categorias_stmt = $catController->obtenerTodas();
$categorias = is_object($categorias_stmt) ? $categorias_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

$mensaje = "";
$error = "";
$titulo = $contenido = "";
$categoria_id = null;
$imagen_actual = null;
$es_edicion = false;
$edit_id = $_GET['edit_id'] ?? ($_POST['edit_id'] ?? null);

$pubController = new PublicacionController($db);

// --- SI ES MODO EDICIÓN, CARGAR DATOS ---
if ($edit_id) {
    $es_edicion = true;
    $post_a_editar = $pubController->obtenerPorId($edit_id, $usuario_id);
    if ($post_a_editar) {
        $titulo = $post_a_editar['titulo'];
        $contenido = $post_a_editar['contenido'];
        $categoria_id = $post_a_editar['categoria_id'];
        $imagen_actual = $post_a_editar['imagen'];
    } else {
        $error = "No tienes permiso para editar esta publicación o no existe.";
        $es_edicion = false;
    }
}

// --- PROCESAR FORMULARIO (CREAR O EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($error) || $es_edicion)) {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
    
    $imagen_archivo = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen_archivo = $_FILES['imagen'];
    }
    
    if ($es_edicion) {
        $resultado = $pubController->editar($edit_id, $titulo, $contenido, $categoria_id, $usuario_id, $imagen_archivo);
    } else {
        $resultado = $pubController->crear($titulo, $contenido, $imagen_archivo, $categoria_id, $usuario_id);
    }
    
    if (strpos($resultado, 'correctamente') !== false) {
        $mensaje = $resultado;
        if (!$es_edicion) {
            $titulo = $contenido = "";
            $categoria_id = null;
        }
    } else {
        $error = $resultado;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/navbar-style.css"> 
    <link rel="stylesheet" href="../css_dash/crear_publicacion_styles.css"> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title><?= $es_edicion ? 'Editar' : 'Nueva' ?> Publicación - RED-novable</title>
    <style>
        /* Ajuste rápido para centrar sin la sidebar */
        .container-crear { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="container-crear">
        <header class="main-header" style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: var(--texto-titulos);"><?= $es_edicion ? 'Editar Publicación' : 'Crear Publicación' ?></h1>
            <p style="color: var(--texto-oscuro);"><?= $es_edicion ? 'Modifica los detalles de tu publicación.' : 'Escribe y comparte contenido con la comunidad.' ?></p>
        </header>

        <section class="form-container-card" style="margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
            <?php if ($mensaje): ?>
                <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="modern-grid-form">
                <?php if($es_edicion): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                <?php endif; ?>

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
                                    <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label>Contenido detallado</label>
                        <textarea name="contenido" placeholder="Desarrolla tu publicación..." required style="min-height: 200px;"><?= htmlspecialchars($contenido) ?></textarea>
                    </div>
                </div>

                <div class="side-fields">
                    <label>Imagen de portada <?= $es_edicion ? '(Opcional)' : '' ?></label>
                    <div class="upload-zone" onclick="document.getElementById('imagen').click()">
                        <input type="file" id="imagen" name="imagen" accept="image/jpeg, image/png, image/webp" onchange="previewImage(this)" hidden>
                        
                        <div id="preview" class="preview-content">
                            <?php if($es_edicion && $imagen_actual): ?>
                                <img src="../../assets/<?= htmlspecialchars($imagen_actual) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:15px;">
                            <?php else: ?>
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Cargar imagen</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn-publish" style="background: var(--color-primario); width: 100%; padding: 15px; border-radius: 8px; color: white; font-weight: bold; border: none; cursor: pointer; margin-top: 15px;">
                        <i class="fas <?= $es_edicion ? 'fa-save' : 'fa-paper-plane' ?>"></i> <?= $es_edicion ? 'Guardar Cambios' : 'Publicar ahora' ?>
                    </button>
                    <?php if(!$es_edicion): ?>
                        <p style="font-size: 0.8rem; margin-top: 10px; color: #64748b; text-align: center;">
                            * Si eres autor, la publicación quedará pendiente de aprobación.
                        </p>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </main>

    <?php include 'footer.php'; ?>

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