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
    $contenido = trim($_POST['contenido_html'] ?? ''); // Recibimos el HTML generado por Quill
    
    // LÓGICA PARA CREAR NUEVA CATEGORÍA
    if (isset($_POST['categoria_id']) && $_POST['categoria_id'] === 'nueva' && !empty($_POST['nueva_categoria'])) {
        $nombre_cat = trim($_POST['nueva_categoria']);
        // Insertamos la categoría directamente en la DB
        $stmt_cat = $db->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, 'Categoría creada desde el editor')");
        if($stmt_cat->execute([$nombre_cat])) {
            $categoria_id = $db->lastInsertId(); // Tomamos el ID de la categoría recién creada
        } else {
            $error = "Error al registrar la nueva categoría.";
        }
    } else {
        $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
    }

    if(empty($error)) {
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
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/navbar-style.css"> 
    <link rel="stylesheet" href="../css/crear_publicacion_styles.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    
    <title><?= $es_edicion ? 'Editar' : 'Nueva' ?> Publicación - Red-novable</title>
</head>
<body>

    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <?php include 'navbar.php'; ?>

    <main class="container-crear">
        <header class="main-header" style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: var(--texto-titulos);"><?= $es_edicion ? 'Editar Publicación' : 'Crear Publicación' ?></h1>
            <p style="color: var(--texto-oscuro);"><?= $es_edicion ? 'Modifica los detalles de tu publicación.' : 'Escribe y comparte contenido con la comunidad.' ?></p>
        </header>

        <section class="form-container-card">
            <?php if ($mensaje): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?>
                </div>
                <script>setTimeout(() => window.location.href = "crear_publicacion.php", 2000);</script>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div id="custom-validation-alert" class="alert alert-error" style="display: none;"></div>
            
            <form method="POST" enctype="multipart/form-data" class="modern-grid-form" id="form-publicacion" novalidate>
                <?php if($es_edicion): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                <?php endif; ?>

                <div class="main-fields">
                    <div class="field" data-tooltip="Escribe un título llamativo para tu artículo">
                        <label>Título del artículo</label>
                        <input type="text" name="titulo" id="input-titulo" class="input-expand-glass" placeholder="Escribe el título aquí..." value="<?= htmlspecialchars($titulo) ?>">
                    </div>

                    <div class="inline-fields">
                        <div class="field" data-tooltip="Clasifica tu artículo">
                            <label>Categoría</label>
                            <select name="categoria_id" id="select-categoria" class="input-expand-glass" onchange="checkNuevaCategoria()">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="nueva" style="font-weight: bold; color: #10b981;">+ Crear nueva categoría</option>
                            </select>
                        </div>

                        <div class="field" id="div-nueva-cat" style="display: none;" data-tooltip="Nombra la nueva temática">
                            <label style="color: #10b981;">Nombre de la nueva categoría</label>
                            <input type="text" name="nueva_categoria" id="input-nueva-cat" class="input-expand-glass" placeholder="Ej. Biomasa">
                        </div>
                    </div>

                    <div class="field">
                        <label>Contenido detallado</label>
                        <div class="quill-wrapper input-expand-glass">
                            <div id="editor-container"><?= $contenido ?></div>
                        </div>
                        <input type="hidden" name="contenido_html" id="contenido-hidden">
                    </div>
                </div>

                <div class="side-fields">
                    <label style="margin-bottom: 25px;">Imagen de portada <?= $es_edicion ? '(Opcional)' : '' ?></label>
                    <div class="upload-zone" onclick="document.getElementById('imagen').click()" data-tooltip="Sube una imagen ilustrativa">
                        <input type="file" id="imagen" name="imagen" accept="image/jpeg, image/png, image/webp" onchange="previewImage(this)" hidden>
                        
                        <div id="preview" class="preview-content">
                            <?php if($es_edicion && $imagen_actual): ?>
                                <img src="../../assets/<?= htmlspecialchars($imagen_actual) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">
                            <?php else: ?>
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Cargar imagen</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn-publish" data-tooltip="Enviar al panel de revisión">
                        <i class="fas <?= $es_edicion ? 'fa-save' : 'fa-paper-plane' ?>"></i> <?= $es_edicion ? 'Guardar Cambios' : 'Publicar ahora' ?>
                    </button>
                    <?php if(!$es_edicion): ?>
                        <p style="font-size: 0.8rem; margin-top: 15px; color: var(--text-light); text-align: center;">
                            * Si eres autor, la publicación quedará pendiente de aprobación.
                        </p>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function checkNuevaCategoria() {
            const select = document.getElementById("select-categoria");
            const divNueva = document.getElementById("div-nueva-cat");
            const inputNueva = document.getElementById("input-nueva-cat");
            
            if (select.value === "nueva") {
                divNueva.style.display = "block";
            } else {
                divNueva.style.display = "none";
                inputNueva.value = "";
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // INICIALIZACIÓN DEL EDITOR QUILL CON MANEJADOR DE IMÁGENES 
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Desarrolla tu publicación enriquecida aquí...',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ],
                    handlers: {
                        image: imageHandler // Interceptamos el botón de imagen
                    }
                }
            }
        });

        // FUNCIÓN PARA SUBIR LA IMAGEN AL SERVIDOR Y NO A LA BASE DE DATOS 
        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async () => {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('imagen_quill', file);

                try {
                    // Enviamos la imagen al nuevo archivo PHP
                    const response = await fetch('../../backend/controllers/upload_quill.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    
                    if(data.success) {
                        // Insertamos la URL de la imagen física en el texto
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', data.url);
                        quill.setSelection(range.index + 1);
                    } else {
                        alert("Error al subir imagen: " + data.error);
                    }
                } catch(e) {
                    console.error(e);
                    alert("Error de conexión al subir la imagen.");
                }
            };
        }

        //  APLICAR TOOLTIPS A LOS BOTONES DE QUILL 
        document.addEventListener("DOMContentLoaded", function() {
            const quillTooltips = {
                '.ql-bold': 'Negrita',
                '.ql-italic': 'Cursiva',
                '.ql-underline': 'Subrayado',
                '.ql-strike': 'Tachado',
                '.ql-header': 'Tamaño de título',
                '.ql-color': 'Color del texto',
                '.ql-background': 'Fondo del texto',
                '.ql-list[value="ordered"]': 'Lista numerada',
                '.ql-list[value="bullet"]': 'Lista de viñetas',
                '.ql-align': 'Alineación',
                '.ql-link': 'Insertar enlace',
                '.ql-image': 'Insertar imagen',
                '.ql-video': 'Insertar video',
                '.ql-clean': 'Limpiar formato'
            };

            for (const [selector, text] of Object.entries(quillTooltips)) {
                const el = document.querySelector(selector);
                if (el) {
                    el.setAttribute('data-tooltip', text);
                }
            }
        });

        //  VALIDACIÓN MANUAL JS PARA EVITAR EL TOOLTIP NEGRO NATIVO 
        document.getElementById('form-publicacion').onsubmit = function(e) {
            const titulo = document.getElementById('input-titulo').value.trim();
            const categoria = document.getElementById('select-categoria').value;
            const inputNuevaCat = document.getElementById('input-nueva-cat').value.trim();
            
            const qlEditor = document.querySelector('#editor-container .ql-editor');
            const contenidoText = qlEditor.innerText.trim();
            const html = qlEditor.innerHTML;

            const isNuevaCatEmpty = (categoria === 'nueva' && inputNuevaCat === '');

            if (!titulo || !categoria || isNuevaCatEmpty || contenidoText === "") {
                e.preventDefault(); // Detenemos el envío nativo
                const alertBox = document.getElementById('custom-validation-alert');
                alertBox.style.display = 'block';
                alertBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> Por favor, completa todos los campos obligatorios para continuar.';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }

            // Si está todo correcto, pasamos el HTML y enviamos
            document.getElementById('contenido-hidden').value = html;
        };
    </script>
    
    <?php include 'mascota.php'; ?>
</body>
</html>