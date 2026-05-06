<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php?error=nologin");
    exit;
}

require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/models/Like.php'; // Para pintar los corazones rojos
$db = (new Conexion())->getConexion();

$usuario_id = $_SESSION['usuario_id'];

// ==========================================
// ELIMINAR COMENTARIO PROPIO
// ==========================================
if (isset($_GET['eliminar_com'])) {
    $id_com = intval($_GET['eliminar_com']);
    // Validamos que el comentario sea realmente del usuario logueado por seguridad
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
                    c.nombre as categoria_nombre,
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

// Necesitamos los IDs de los likes para pintar el corazón rojo en las tarjetas
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
    <link rel="stylesheet" href="../css/categoria-styles.css"> <!-- CARGAMOS ESTILOS DE LAS TARJETAS -->
    <link rel="stylesheet" href="../css/perfil-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<!-- LE QUITAMOS EL STYLE EN LÍNEA -->
<body>
    
    <?php include 'navbar.php'; ?>

    <main class="perfil-container">
        
        <!-- HEADER DEL PERFIL -->
        <header class="perfil-header">
            <div class="perfil-banner"></div>
            <div class="perfil-info-wrapper">
                <div class="foto-perfil-container">
                    <?php if ($userData['foto_perfil']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($userData['foto_perfil']) ?>" alt="Foto" class="foto-perfil">
                    <?php else: ?>
                        <div class="foto-placeholder"><i class="fas fa-user"></i></div>
                    <?php endif; ?>
                </div>

                <div class="user-details">
                    <h1 class="user-name"><?= htmlspecialchars($userData['nombre']) ?></h1>
                    <span class="user-rol"><?= htmlspecialchars($userData['rol_nombre']) ?></span>
                    <p class="user-joined"><i class="far fa-calendar-alt"></i> Se unió en <?= date('M Y', strtotime($userData['fecha_creacion'])) ?></p>
                </div>

                <button class="btn-edit-perfil"><i class="fas fa-edit"></i> Editar perfil</button>
            </div>
        </header>

        <!-- NAVEGACIÓN DE PESTAÑAS TIPO X -->
        <div class="profile-nav">
            <button class="tab-btn active" onclick="openTab(event, 'Posts')">Posts (<?= count($misPublicaciones) ?>)</button>
            <button class="tab-btn" onclick="openTab(event, 'Likes')">Me gusta (<?= count($misLikes) ?>)</button>
            <button class="tab-btn" onclick="openTab(event, 'Comentarios')">Comentarios (<?= count($misComentarios) ?>)</button>
        </div>

        <!-- =========================================
             PESTAÑA 1: MIS POSTS
             ========================================= -->
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
                                    <img src="data:image/jpeg;base64,<?= base64_encode($pub['imagen']) ?>" alt="Imagen de publicación">
                                </div>
                            <?php endif; ?>
                            
                            <!-- Botón editar rápido (solo en Mis Posts) -->
                            <div style="text-align: right; margin-bottom: 10px;">
                                <a href="../admin/crear_publicacion.php?edit_id=<?= $pub['id'] ?>" class="btn-p-edit">
                                    <i class="fas fa-pencil-alt"></i> Editar publicación
                                </a>
                            </div>

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

        <!-- =========================================
             PESTAÑA 2: ME GUSTA
             ========================================= -->
        <div id="Likes" class="tab-content">
            <div class="publicaciones-container" style="padding:0; margin:0;">
                <?php if (count($misLikes) > 0): ?>
                    <?php foreach($misLikes as $pub): 
                        $likesCount = $likeModel->contarLikes($pub['id']);
                        $yaLiked = true; 
                    ?>
                        <div class="publicacion-card" data-pub-id="<?= $pub['id'] ?>">
                            <?php if($pub['imagen']): ?>
                                <div class="publicacion-imagen">
                                    <img src="data:image/jpeg;base64,<?= base64_encode($pub['imagen']) ?>" alt="Imagen de publicación">
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

        <!-- =========================================
             PESTAÑA 3: MIS COMENTARIOS
             ========================================= -->
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

    <script>
        const usuarioLogueado = true; // Ya sabemos que está logueado por PHP al inicio

        // SCRIPT PARA LAS PESTAÑAS
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            
            // Ocultar todo el contenido
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            
            // Quitar la clase "active" de todos los botones
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            
            // Mostrar la pestaña actual y añadir "active" al botón
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
    
    <!-- Cargar la lógica de likes para que funcionen los botones en las pestañas -->
    <script src="../js/like-logic.js"></script>
</body>
</html>