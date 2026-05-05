<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';
require_once __DIR__ . '/../../backend/models/Like.php';
// Agregamos el controlador de comentarios
require_once __DIR__ . '/../../backend/controllers/ComentarioController.php';

// ==========================
// 2. Conexión e instancias
// ==========================
$db = (new Conexion())->getConexion();
$catController = new CategoriesController($db);
$pubController = new PublicacionController($db);
// Instanciamos el controlador de comentarios
$comentarioController = new ComentarioController($db);

// ==========================
// 3. Obtener ID de categoría
// ==========================
$categoria_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($categoria_id <= 0) {
    header("Location: categorias.php");
    exit;
}

// ==========================
// 4. Obtener información de la categoría
// ==========================
$categoria_stmt = $catController->obtenerPorId($categoria_id);
$categoria = null;

if (!is_string($categoria_stmt)) {
    $categoria = $categoria_stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$categoria) {
    $error = "Categoría no encontrada";
} else {
    // ==========================
    // 5. Obtener publicaciones de esta categoría
    // ==========================
    $pub_stmt = $pubController->obtenerPorCategoria($categoria_id);
    $publicaciones = is_string($pub_stmt) ? [] : $pub_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==========================
// 6. Iniciar sesión y obtener likes del usuario
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioLogueado = isset($_SESSION['usuario_id']);
$likedPorUsuario = [];

if ($usuarioLogueado && !empty($publicaciones)) {
    $likeModel = new Like($db);
    $likedPorUsuario = $likeModel->obtenerIdsPublicacionesLikedPorUsuario($_SESSION['usuario_id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($categoria) ? htmlspecialchars($categoria['nombre']) : 'Categoría' ?> - Red-novable</title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/categoria-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f5f9;">
    <?php include 'navbar.php'; ?>

    <main class="publicaciones-container">
        
        <a href="categorias.php" class="volver">
            <i class="fas fa-arrow-left"></i> Volver a categorías
        </a>

        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 20px; border-radius: 10px; text-align: center;">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>

            <div class="titulo-categoria">
                <i class="fas fa-tag"></i> <?= htmlspecialchars($categoria['nombre']) ?>
                <?php if (!empty($categoria['descripcion'])): ?>
                    <p style="font-size: 1rem; color: #64748b; margin-top: 10px;"><?= htmlspecialchars($categoria['descripcion']) ?></p>
                <?php endif; ?>
            </div>

            <?php if (count($publicaciones) > 0): ?>
                <?php foreach($publicaciones as $pub): 
                    $likeModelTemp = new Like($db);
                    $likesCount = $likeModelTemp->contarLikes($pub['id']);
                    $yaLiked = $usuarioLogueado && in_array($pub['id'], $likedPorUsuario);
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
                        <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?>
                    </div>

                    <div class="publicacion-contenido">
                        <?= nl2br(htmlspecialchars($pub['contenido'])) ?>
                    </div>

                    <!-- Contenedor de Acciones (Like y Comentar) -->
                    <div class="post-actions">
                        <!-- Botón Like -->
                        <button class="like-btn <?= $yaLiked ? 'liked' : '' ?>" data-pubid="<?= $pub['id'] ?>">
                            <svg class="like-icon" viewBox="0 0 24 24" width="24" height="24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                            <span class="like-text">Me gusta</span>
                            <span class="like-count"><?= $likesCount ?></span>
                        </button>

                        <!-- Botón Desplegar Comentarios -->
                        <button class="comment-trigger-btn" data-pubid="<?= $pub['id'] ?>">
                            <i class="fas fa-comment"></i>
                            <span>Comentarios</span>
                        </button>
                    </div>

                    <!-- Sección de Comentarios Colapsable -->
                    <div class="comments-section" id="comments-<?= $pub['id'] ?>">
                        <div class="comments-list">
                            <?php 
                            // Aquí está la magia: consultamos la BD para esta publicación específica
                            $comentarios_pub = $comentarioController->obtenerComentariosPorPublicacion($pub['id']);
                            
                            if (count($comentarios_pub) > 0): 
                                foreach($comentarios_pub as $comentario): 
                            ?>
                                    <div class="comment-item">
                                        <!-- Mostrar el nombre del autor real -->
                                        <div class="comment-user"><?= htmlspecialchars($comentario['autor_nombre'] ?? 'Usuario') ?></div>
                                        <p><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
                                    </div>
                            <?php 
                                endforeach;
                            else: 
                            ?>
                                <p style="font-size: 0.85rem; color: #94a3b8; text-align: center; margin-bottom: 10px;" class="no-comments-msg">Aún no hay comentarios. ¡Sé el primero en opinar!</p>
                            <?php endif; ?>
                        </div>

                        <!-- Input de Uiverse para comentar -->
                        <form class="comment-form" data-pubid="<?= $pub['id'] ?>">
                            <div class="form-control">
                                <input class="input input-alt" placeholder="Escribe tu opinión sobre el ODS 7..." required="" type="text" name="comentario">
                                <span class="input-border input-border-alt"></span>
                            </div>
                            <button type="submit" style="display:none"></button>
                        </form>
                    </div>

                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sin-publicaciones">
                    <i class="fas fa-folder-open"></i>
                    <h3>No hay publicaciones en esta categoría</h3>
                    <p>Sé el primero en compartir contenido sobre <?= htmlspecialchars($categoria['nombre']) ?></p>
                    <?php if ($usuarioLogueado): ?>
                        <a href="../admin/crear_publicacion.php" style="display: inline-block; margin-top: 20px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 10px 24px; border-radius: 8px; text-decoration: none;">
                            <i class="fas fa-plus"></i> Crear publicación
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <?php include 'footer.php'; ?>

    <!-- Variables globales para JS -->
    <script>
        const usuarioLogueado = <?= json_encode($usuarioLogueado) ?>;
    </script>
    
    <!-- Scripts -->
    <script src="../js/like-logic.js"></script>
    <script src="../js/comentarios.js"></script>
</body>
</html>