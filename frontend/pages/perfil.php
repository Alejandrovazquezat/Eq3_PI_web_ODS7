<?php
session_start();

// Verificación de seguridad...
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php?error=nologin");
    exit;
}

require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/models/Like.php';
require_once __DIR__ . '/../../backend/models/Usuario.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php'; 
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php'; 

$db = (new Conexion())->getConexion();
$usuario_id = $_SESSION['usuario_id'];
$mensaje_perfil = "";
$error_perfil = "";

// ==========================================
// 1. LÓGICA DE ACTUALIZACIÓN DE PERFIL
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'actualizar_perfil') {
    $nuevo_nombre = trim($_POST['nombre']);
    
    $stmtActual = $db->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmtActual->execute([$usuario_id]);
    $usuarioActual = $stmtActual->fetch(PDO::FETCH_ASSOC);
    $ruta_foto_final = $usuarioActual['foto_perfil'];

    $pubController = new PublicacionController($db);

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $resultado_subida = $pubController->subirImagenFisica($_FILES['foto_perfil'], 'uploads/perfiles/');
        
        if (strpos($resultado_subida, 'Error') === 0) {
            $error_perfil = $resultado_subida;
        } else {
            $ruta_foto_final = $resultado_subida;
        }
    }

    if (isset($_POST['quitar_foto'])) {
        $ruta_foto_final = null; 
    }

    if (empty($error_perfil)) {
        if (Usuario::actualizarPerfil($db, $usuario_id, $nuevo_nombre, $ruta_foto_final)) {
            $_SESSION['nombre'] = $nuevo_nombre;
            $mensaje_perfil = "Tu perfil ha sido actualizado correctamente.";
            header("Location: perfil.php?msg=perfil_actualizado");
            exit;
        } else {
            $error_perfil = "Error al actualizar el perfil en la base de datos.";
        }
    }
}

// ==========================================
// 2. LÓGICA DE EDICIÓN DE POSTS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $pubController = new PublicacionController($db);
    
    $id_editar = intval($_POST['pub_id']);
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = intval($_POST['categoria']);
    
    $imagen_archivo = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen_archivo = $_FILES['imagen'];
    }
    
    $resultado = $pubController->editar($id_editar, $titulo, $contenido, $categoria_id, $usuario_id, $imagen_archivo);
    
    header("Location: perfil.php?msg=editado");
    exit;
}

// ==========================================
// 3. OBTENER CATEGORÍAS
// ==========================================
$catController = new CategoriesController($db);
$categorias_stmt = $catController->obtenerTodas();
$categorias = is_object($categorias_stmt) ? $categorias_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// ==========================================
// ELIMINAR COMENTARIO PROPIO
// ==========================================
if (isset($_GET['eliminar_com'])) {
    $id_com = intval($_GET['eliminar_com']);
    $stmtDel = $db->prepare("DELETE FROM comentarios WHERE id = ? AND usuario_id = ?");
    $stmtDel->execute([$id_com, $usuario_id]);
    header("Location: perfil.php");
    exit;
}

// ==========================================
// 1. DATOS DEL USUARIO
// ==========================================
$stmtUser = $db->prepare("SELECT u.nombre, u.email, u.fecha_creacion, u.foto_perfil, r.nombre as rol_nombre 
                        FROM usuarios u 
                        JOIN roles r ON u.rol_id = r.id 
                        WHERE u.id = ?");
$stmtUser->execute([$usuario_id]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// ==========================================
// 2. MIS PUBLICACIONES
// ==========================================
$queryMisPubs = "SELECT p.id, p.titulo, p.fecha_creacion, p.estado, p.imagen, p.contenido,
                    c.nombre as categoria_nombre, p.categoria_id,
                    (SELECT COUNT(*) FROM likes l WHERE l.publicacion_id = p.id) as total_likes,
                    (SELECT COUNT(*) FROM comentarios com WHERE com.publicacion_id = p.id) as total_comentarios
              FROM publicaciones p 
              LEFT JOIN categorias c ON p.categoria_id = c.id 
              WHERE p.usuario_id = ? 
              ORDER BY p.fecha_creacion DESC";

$stmtMisPubs = $db->prepare($queryMisPubs);
$stmtMisPubs->execute([$usuario_id]);
$misPublicaciones = $stmtMisPubs->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// 3. PUBLICACIONES QUE ME GUSTAN
// ==========================================
$queryLikes = "SELECT p.*, u.nombre as autor, c.nombre as categoria_nombre 
               FROM publicaciones p 
               JOIN likes l ON p.id = l.publicacion_id 
               LEFT JOIN usuarios u ON p.usuario_id = u.id 
               LEFT JOIN categorias c ON p.categoria_id = c.id 
               WHERE l.usuario_id = ? 
               ORDER BY l.fecha DESC";
               
$stmtLikes = $db->prepare($queryLikes);
$stmtLikes->execute([$usuario_id]);
$misLikes = $stmtLikes->fetchAll(PDO::FETCH_ASSOC);

$likeModel = new Like($db);
$likedPorUsuario = $likeModel->obtenerIdsPublicacionesLikedPorUsuario($usuario_id);

// ==========================================
// 4. MIS COMENTARIOS
// ==========================================
$queryComentarios = "SELECT c.*, p.titulo as pub_titulo 
                     FROM comentarios c 
                     JOIN publicaciones p ON c.publicacion_id = p.id 
                     WHERE c.usuario_id = ? 
                     ORDER BY c.fecha_creacion DESC";
$stmtComentarios = $db->prepare($queryComentarios);
$stmtComentarios->execute([$usuario_id]);
$misComentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Red-novable</title>
    <link rel="stylesheet" href="../css/navbar-style.css"> 
    <link rel="stylesheet" href="../css/categoria-styles.css">
    <link rel="stylesheet" href="../css/perfil-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
    
    <?php include 'navbar.php'; ?>

    <main class="perfil-container">
        
        <header class="perfil-header">
            <div class="perfil-banner"></div>
            <div class="perfil-info-wrapper">
                <div class="foto-perfil-container">
                    <?php if ($userData['foto_perfil']): ?>
                        <img src="../../assets/<?= htmlspecialchars($userData['foto_perfil']) ?>" alt="Foto" class="foto-perfil">
                    <?php else: ?>
                        <div class="foto-placeholder"><i class="fas fa-user"></i></div>
                    <?php endif; ?>
                </div>

                <div class="user-details">
                    <h1 class="user-name"><?= htmlspecialchars($userData['nombre']) ?></h1>
                    <span class="user-rol"><?= htmlspecialchars($userData['rol_nombre']) ?></span>
                    <p class="user-joined"><i class="far fa-calendar-alt"></i> Se unió en <?= date('M Y', strtotime($userData['fecha_creacion'])) ?></p>
                </div>

                <button class="btn-p-edit" onclick="abrirModalPerfil()">
                    <i class="fas fa-edit"></i> Editar Perfil
                </button>
            </div>
        </header>

        <div class="profile-nav">
            <button class="tab-btn active" onclick="openTab(event, 'Posts')">Posts (<?= count($misPublicaciones) ?>)</button>
            <button class="tab-btn" onclick="openTab(event, 'Likes')">Me gusta (<?= count($misLikes) ?>)</button>
            <button class="tab-btn" onclick="openTab(event, 'Comentarios')">Comentarios (<?= count($misComentarios) ?>)</button>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'perfil_actualizado'): ?>
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">
                <i class="fas fa-check-circle"></i> Tu perfil ha sido actualizado correctamente.
            </div>
        <?php endif; ?>
        <?php if(!empty($error_perfil)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_perfil) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'editado'): ?>
            <div style="background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin: 20px; text-align: center;">
                <i class="fas fa-check-circle"></i> Tu publicación ha sido actualizada correctamente.
            </div>
        <?php endif; ?>

        <div id="Posts" class="tab-content" style="display: block;">
            <div class="publicaciones-container" style="padding:0; margin:0;">
                <?php if (count($misPublicaciones) > 0): ?>
                    <?php foreach($misPublicaciones as $pub): 
                        $likesCount = $likeModel->contarLikes($pub['id']);
                        $yaLiked = in_array($pub['id'], $likedPorUsuario);
                    ?>
                        <div class="publicacion-card" data-pub-id="<?= $pub['id'] ?>">
                            <?php if($pub['imagen']): ?>
                                <div class="publicacion-imagen">
                                    <img src="../../assets/<?= htmlspecialchars($pub['imagen']) ?>" alt="Imagen de publicación">
                                </div>
                            <?php endif; ?>
                            
                            <div style="text-align: right; margin-bottom: 10px;">
                                <button onclick="abrirModalEditarPost(<?= $pub['id'] ?>)" class="btn-p-edit">
                                    <i class="fas fa-pencil-alt"></i> Editar publicación
                                </button>
                            </div>

                            <div id="data-titulo-<?= $pub['id'] ?>" style="display:none;"><?= htmlspecialchars($pub['titulo']) ?></div>
                            <div id="data-contenido-<?= $pub['id'] ?>" style="display:none;"><?= htmlspecialchars($pub['contenido']) ?></div>
                            <div id="data-cat-id-<?= $pub['id'] ?>" style="display:none;"><?= $pub['categoria_id'] ?></div>

                            <h2 class="publicacion-titulo"><?= htmlspecialchars($pub['titulo']) ?></h2>
                            <div class="publicacion-meta">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($userData['nombre']) ?> &nbsp;|&nbsp; 
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?> &nbsp;|&nbsp;
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($pub['categoria_nombre'] ?? 'General') ?>
                            </div>
                            <div class="publicacion-contenido">
                                <?= nl2br(htmlspecialchars($pub['contenido'])) ?>
                            </div>

                            <div class="post-actions">
                                <button class="like-btn <?= $yaLiked ? 'liked' : '' ?>" data-pubid="<?= $pub['id'] ?>">
                                    <svg class="like-icon" viewBox="0 0 24 24" width="24" height="24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    <span class="like-text">Me gusta</span>
                                    <span class="like-count"><?= $likesCount ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-pubs">
                        <i class="fas fa-folder-open"></i>
                        <h3>No has publicado nada aún.</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="Likes" class="tab-content">
            <div class="publicaciones-container" style="padding:0; margin:0;">
                <?php if (count($misLikes) > 0): ?>
                    <?php foreach($misLikes as $pub): 
                        $likesCount = $likeModel->contarLikes($pub['id']);
                    ?>
                        <div class="publicacion-card" data-pub-id="<?= $pub['id'] ?>">
                            <?php if($pub['imagen']): ?>
                                <div class="publicacion-imagen">
                                    <img src="../../assets/<?= htmlspecialchars($pub['imagen']) ?>" alt="Imagen de publicación">
                                </div>
                            <?php endif; ?>

                            <h2 class="publicacion-titulo"><?= htmlspecialchars($pub['titulo']) ?></h2>
                            <div class="publicacion-meta">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($pub['autor'] ?? 'Desconocido') ?> &nbsp;|&nbsp; 
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?> &nbsp;|&nbsp;
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($pub['categoria_nombre'] ?? 'General') ?>
                            </div>
                            <div class="publicacion-contenido">
                                <?= nl2br(htmlspecialchars($pub['contenido'])) ?>
                            </div>

                            <div class="post-actions">
                                <button class="like-btn liked" data-pubid="<?= $pub['id'] ?>">
                                    <svg class="like-icon" viewBox="0 0 24 24" width="24" height="24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    <span class="like-text">Me gusta</span>
                                    <span class="like-count"><?= $likesCount ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-pubs">
                        <i class="fas fa-heart-broken"></i>
                        <h3>Aún no has dado 'Me gusta' a ninguna publicación.</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="Comentarios" class="tab-content">
            <?php if (count($misComentarios) > 0): ?>
                <?php foreach($misComentarios as $com): ?>
                    <div class="mi-comentario-item">
                        <div class="comentario-header">
                            <div class="comentario-meta">
                                Comentaste en: <strong><?= htmlspecialchars($com['pub_titulo']) ?></strong> 
                                <br><small><?= date('d M Y, H:i', strtotime($com['fecha_creacion'])) ?></small>
                            </div>
                            
                            <a href="perfil.php?eliminar_com=<?= $com['id'] ?>" class="btn-delete-com" onclick="return confirm('¿Seguro que deseas eliminar este comentario?');">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </a>
                        </div>
                        <div class="comentario-texto">
                            "<?= nl2br(htmlspecialchars($com['contenido'])) ?>"
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-pubs">
                    <i class="fas fa-comment-slash"></i>
                    <h3>No has escrito ningún comentario.</h3>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'footer.php'; ?>

    <div id="modal-perfil" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; color: var(--color-accion);"><i class="fas fa-edit"></i> Editar Perfil</h2>
                <button onclick="cerrarModal('modal-perfil')" style="background:none; border:none; font-size: 1.8rem; cursor:pointer; color: #94a3b8; line-height: 1;">&times;</button>
            </div>

            <form action="perfil.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="actualizar_perfil">

                <div class="form-group" style="text-align: center; margin-bottom: 20px;">
                    <div class="foto-perfil-container" style="margin: 0 auto; box-shadow: 6px 6px 0 var(--sombra-neubrutal-oscura);">
                        <?php if ($userData['foto_perfil']): ?>
                            <img src="../../assets/<?= htmlspecialchars($userData['foto_perfil']) ?>" alt="Foto" id="perfil-preview" class="foto-perfil">
                        <?php else: ?>
                            <div class="foto-placeholder" id="perfil-placeholder"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Actualizar Foto (Opcional):</label>
                    <input type="file" name="foto_perfil" accept="image/jpeg, image/png, image/webp" class="form-control-modal" onchange="previewImagenPerfil(this)">
                </div>

                <?php if ($userData['foto_perfil']): ?>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <input type="checkbox" name="quitar_foto" id="quitar_foto">
                        <label for="quitar_foto" style="display: inline; font-weight: normal; margin-left: 5px;">Quitar foto actual</label>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($userData['nombre']) ?>" class="form-control-modal" required>
                </div>

                <div class="form-group" style="color: #64748b; font-size: 0.9rem; margin-top: 20px; border-top: 1px solid var(--borde-tarjeta); padding-top: 10px;">
                    <p>Por ahora, solo se puede cambiar el nombre y la foto. El correo y la contraseña se actualizarán más adelante.</p>
                </div>

                <div class="modal-buttons" style="margin-top: 25px;">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-perfil')">Cancelar</button>
                    <button type="submit" class="btn-p-action">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-editar-post" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; color: #f59e0b;"><i class="fas fa-pencil-alt"></i> Editar Publicación</h2>
                <button onclick="cerrarModal('modal-editar-post')" style="background:none; border:none; font-size: 1.8rem; cursor:pointer; color: #94a3b8; line-height: 1;">&times;</button>
            </div>

            <form action="perfil.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="pub_id" id="edit-id-post">

                <div class="form-group">
                    <label>Título:</label>
                    <input type="text" name="titulo" id="edit-titulo-post" class="form-control-modal" required>
                </div>

                <div class="form-group">
                    <label>Categoría:</label>
                    <select name="categoria" id="edit-cat-post" class="form-control-modal" required>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Actualizar Imagen (Opcional):</label>
                    <input type="file" name="imagen" class="form-control-modal" accept="image/jpeg, image/png, image/webp">
                </div>

                <div class="form-group">
                    <label>Contenido:</label>
                    <textarea name="contenido" id="edit-contenido-post" class="form-control-modal" required style="min-height: 150px; resize: vertical;"></textarea>
                </div>

                <div class="modal-buttons" style="margin-top: 25px;">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-editar-post')">Cancelar</button>
                    <button type="submit" class="btn-p-edit" style="color: var(--blanco); background: #f59e0b;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const usuarioLogueado = true;

        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        function abrirModalPerfil() {
            document.getElementById('modal-perfil').classList.add('active');
        }

        function abrirModalEditarPost(id) {
            document.getElementById('edit-id-post').value = id;
            document.getElementById('edit-titulo-post').value = document.getElementById('data-titulo-' + id).textContent;
            document.getElementById('edit-contenido-post').value = document.getElementById('data-contenido-' + id).textContent;
            
            const catId = document.getElementById('data-cat-id-' + id).textContent;
            document.getElementById('edit-cat-post').value = catId;

            document.getElementById('modal-editar-post').classList.add('active');
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function previewImagenPerfil(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgPreview = document.getElementById('perfil-preview');
                    const placeholder = document.getElementById('perfil-placeholder');
                    
                    if (imgPreview) {
                        imgPreview.src = e.target.result;
                    } else if (placeholder) {
                        placeholder.innerHTML = `<img src="${e.target.result}" alt="Foto" class="foto-perfil">`;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                cerrarModal(e.target.id);
            }
        });
    </script>
    
    <script src="../js/like-logic.js"></script>
</body>
</html>