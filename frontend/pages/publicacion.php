<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/models/Like.php';
require_once __DIR__ . '/../../backend/controllers/ComentarioController.php';

// ==========================
// 2. Conexión e instancias
// ==========================
$db = (new Conexion())->getConexion();
$comentarioController = new ComentarioController($db);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioLogueado = isset($_SESSION['usuario_id']);

// ==========================
// 3. Obtener ID de la publicación
// ==========================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// ==========================
// 4. Consultar Publicación en la BD
// ==========================
$query = "SELECT p.*, u.nombre as autor_nombre, c.nombre as categoria_nombre, c.id as cat_id 
          FROM publicaciones p 
          LEFT JOIN usuarios u ON p.usuario_id = u.id 
          LEFT JOIN categorias c ON p.categoria_id = c.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$pub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pub) {
    $error = "La publicación que buscas no existe o ha sido eliminada.";
} else {
    // Obtener Likes si existe
    $likeModel = new Like($db);
    $likesCount = $likeModel->contarLikes($pub['id']);
    $yaLiked = false;
    
    if ($usuarioLogueado) {
        $likedPorUsuario = $likeModel->obtenerIdsPublicacionesLikedPorUsuario($_SESSION['usuario_id']);
        $yaLiked = in_array($pub['id'], $likedPorUsuario);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pub) ? htmlspecialchars($pub['titulo']) : 'Publicación no encontrada' ?> - Red-novable</title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* =========================================
           ESTILOS GENERALES DEL POST
           ========================================= */
        .post-container {
            max-width: 900px;
            margin: 40px auto 80px auto;
            padding: 0 20px;
            font-family: 'Inter', sans-serif;
        }

        .volver {
            display: inline-block;
            margin-bottom: 25px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: 0.3s;
        }
        .volver:hover { text-decoration: underline; }

        .post-header { margin-bottom: 30px; text-align: center; }
        
        .post-category {
            display: inline-block;
            background: #cfd0d0; color: #1e3a8a;
            padding: 6px 15px; border-radius: 20px;
            font-weight: 700; font-size: 0.85rem;
            margin-bottom: 15px; text-decoration: none;
        }
        body.dark-mode .post-category { background: rgba(0, 102, 255, 0.2); color: #4ade80; }

        .post-title { font-size: 3rem; color: var(--texto-titulos); margin: 0 0 20px 0; line-height: 1.2; font-weight: 900; }
        
        .post-meta { color: #64748b; font-size: 1rem; display: flex; justify-content: center; gap: 20px; }
        body.dark-mode .post-meta { color: #9ca3af; }

        .post-image-container { width: 100%; border-radius: 20px; overflow: hidden; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .post-image-container img { width: 100%; max-height: 550px; object-fit: cover; display: block; }

        .post-content {
            font-size: 1.15rem;
            color: var(--texto-titulos);
            line-height: 1.8;
            margin-bottom: 50px;
        }

        .interaction-bar { display: flex; align-items: center; gap: 15px; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-bottom: 40px; }
        body.dark-mode .interaction-bar { border-color: #30363d; }

        /* =========================================
           BOTONES NEUMÓRFICOS UIVERSE (Sombras Grises)
           ========================================= */
        .btn-uiverse {
            --hover-shadows: 8px 8px 16px #a3a3a3, -8px -8px 16px #ffffff;
            --accent: #3b82f6; 
            font-weight: bold;
            letter-spacing: 0.05em;
            border: none;
            border-radius: 1.1em;
            background-color: #e2e8f0; 
            cursor: pointer;
            color: #475569;
            padding: 12px 24px;
            transition: box-shadow ease-in-out 0.3s, background-color ease-in-out 0.1s,
                        letter-spacing ease-in-out 0.1s, transform ease-in-out 0.1s, color ease-in-out 0.1s;
            box-shadow: 5px 5px 10px #b8c1cc, -5px -5px 10px #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
        }

        .btn-uiverse:hover { box-shadow: var(--hover-shadows); }

        .btn-uiverse:active {
            box-shadow: var(--hover-shadows), var(--accent) 0px 0px 20px 2px;
            background-color: var(--accent);
            color: white;
            transform: scale(0.95);
        }

        body.dark-mode .btn-uiverse {
            background-color: #1e293b;
            color: #cbd5e1;
            --hover-shadows: 8px 8px 16px #0f172a, -8px -8px 16px #2d3b52;
            box-shadow: 5px 5px 10px #131a26, -5px -5px 10px #293850;
        }

        body.dark-mode .btn-uiverse:active { background-color: var(--accent); color: white; }

        .btn-uiverse.like-btn .like-icon { fill: #94a3b8; transition: 0.3s; width: 22px; height: 22px; }
        body.dark-mode .btn-uiverse.like-btn .like-icon { fill: #64748b; }
        .btn-uiverse.like-btn .like-count { background: rgba(0,0,0,0.1); padding: 3px 10px; border-radius: 8px; font-weight: bold; }
        body.dark-mode .btn-uiverse.like-btn .like-count { background: rgba(255,255,255,0.1); }

        .btn-uiverse.like-btn.liked { --accent: #ef4444; color: #ef4444; }
        
        .btn-uiverse.like-btn.liked .like-icon,
        body.dark-mode .btn-uiverse.like-btn.liked .like-icon {
            fill: #ef4444;
            filter: drop-shadow(0 0 5px rgba(239, 68, 68, 0.5));
            transform: scale(1.2);
        }

        .btn-uiverse.btn-send { --accent: #10b981; padding: 12px 20px; font-size: 1.2rem; color: var(--accent); }
        .btn-uiverse.btn-send:active { color: white; }

        /* =========================================
           COMENTARIOS CON EFECTO UIVERSE CARD
           ========================================= */
        .comments-section {
            background: #f8fafc; padding: 30px; border-radius: 16px; border: 1px solid #e2e8f0;
        }
        body.dark-mode .comments-section { background: #010409; border-color: #30363d; }

        /* Añadimos padding al contenedor para que el scale(1.05) del hover no se corte */
        .comments-list { max-height: 400px; overflow-y: auto; overflow-x: hidden; margin-bottom: 20px; padding: 10px; }
        .comments-list::-webkit-scrollbar { width: 6px; }
        .comments-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* --- AQUÍ ESTÁ LA MAGIA DE TU TARJETA APLICADA AL COMENTARIO --- */
        .comment-item {
            box-sizing: border-box;
            width: 100%; /* Ocupa todo el ancho en lugar de 190px */
            background: rgba(217, 217, 217, 0.4); /* Un poco más tenue para leer bien */
            border: 1px solid white;
            box-shadow: 12px 17px 51px rgba(0, 0, 0, 0.1); /* Sombra suavizada */
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: 17px;
            transition: all 0.5s;
            cursor: pointer;
            
            /* Alineación adaptada para texto (no centrada como el original) */
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            text-align: left;
            
            padding: 20px;
            margin-bottom: 15px;
            color: #334155;
            font-size: 0.95rem;
        }

        .comment-item:hover {
            border: 1px solid #94a3b8; /* Borde sutil gris al pasar el mouse */
            transform: scale(1.02); /* Escala más suave que 1.05 para que no maree leyendo */
        }

        /* La divertida rotación que pediste al hacer click */
        .comment-item:active {
            transform: scale(0.95) rotateZ(1.7deg);
        }

        /* Estilos Uiverse para el modo oscuro */
        body.dark-mode .comment-item { 
            background: rgba(22, 27, 34, 0.58); /* Gris azulado oscuro transparente */
            border: 1px solid #30363d;
            color: #c9d1d9; 
            box-shadow: 12px 17px 51px rgba(0, 0, 0, 0.4);
        }
        body.dark-mode .comment-item:hover {
            border: 1px solid var(--color-accion); /* Se ilumina en azul al hacer hover en oscuro */
        }

        .comment-user { font-weight: bold; color: #3b82f6; font-size: 0.9rem; margin-bottom: 8px; }
        
        .form-control { position: relative; width: 100%; }
        .input { color: inherit; font-size: 1rem; background: transparent; width: 100%; padding: 12px; border: none; border-bottom: 2px solid #cbd5e1; transition: 0.3s; }
        body.dark-mode .input { border-bottom-color: #30363d; }
        .input:focus { outline: none; }
        .input-border-alt { position: absolute; background: linear-gradient(90deg, #FF6464 0%, #FFBF59 50%, #47C9FF 100%); width: 0%; height: 3px; bottom: 0; left: 0; transition: 0.4s; }
        .input:focus + .input-border-alt { width: 100%; }

        .comment-form-container { display: flex; gap: 15px; align-items: flex-end; }

        /* =========================================
           MODAL DE SEGURIDAD
           ========================================= */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: flex; justify-content: center; align-items: center; z-index: 3000; }
        .modal-card { background: white; padding: 40px; border-radius: 24px; text-align: center; max-width: 450px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .modal-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; background: #e0f2fe; color: #0ea5e9; }
        .modal-btns { display: flex; gap: 12px; justify-content: center; margin-top: 25px; flex-wrap: wrap; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .btn-primary-modal { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .btn-close-modal { background: transparent; color: #94a3b8; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; }

        @media (max-width: 768px) {
            .post-title { font-size: 2rem; }
            .interaction-bar { flex-direction: column; align-items: stretch; }
            .btn-uiverse { width: 100%; }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="post-container">
        
        <?php if (isset($error)): ?>
            <a href="index.php" class="volver"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
            <div style="background: #fee2e2; color: #ef4444; padding: 30px; border-radius: 16px; text-align: center; border: 1px solid #fca5a5;">
                <i class="fas fa-heart-broken" style="font-size: 4rem; margin-bottom: 20px;"></i>
                <h2><?= htmlspecialchars($error) ?></h2>
            </div>
        <?php else: ?>

            <a href="categoria.php?id=<?= $pub['cat_id'] ?>" class="volver">
                <i class="fas fa-arrow-left"></i> Volver a <?= htmlspecialchars($pub['categoria_nombre']) ?>
            </a>

            <header class="post-header">
                <a href="categoria.php?id=<?= $pub['cat_id'] ?>" class="post-category"><?= htmlspecialchars($pub['categoria_nombre']) ?></a>
                <h1 class="post-title"><?= htmlspecialchars($pub['titulo']) ?></h1>
                <div class="post-meta">
                    <span><i class="fas fa-user-edit"></i> Escrito por <strong><?= htmlspecialchars($pub['autor_nombre'] ?? 'Anónimo') ?></strong></span>
                    <span><i class="fas fa-calendar-alt"></i> <?= date('d M, Y', strtotime($pub['fecha_creacion'])) ?></span>
                </div>
            </header>

            <?php if($pub['imagen']): ?>
                <div class="post-image-container">
                    <img src="../../assets/<?= htmlspecialchars($pub['imagen']) ?>" alt="Imagen de <?= htmlspecialchars($pub['titulo']) ?>">
                </div>
            <?php endif; ?>

            <div class="post-content">
                <?= nl2br(htmlspecialchars($pub['contenido'])) ?>
            </div>

            <div class="interaction-bar">
                <button class="btn-uiverse like-btn <?= $yaLiked ? 'liked' : '' ?>" data-pubid="<?= $pub['id'] ?>">
                    <svg class="like-icon" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    <span class="like-text"><?= $yaLiked ? 'Te gusta' : 'Me gusta' ?></span>
                    <span class="like-count"><?= $likesCount ?></span>
                </button>
                
                <button class="btn-uiverse comment-trigger-btn" onclick="document.getElementById('comments-section').style.display='block'; document.getElementById('comentario-input').focus();">
                    <i class="fas fa-comment"></i> Escribir un comentario
                </button>
            </div>

            <div class="comments-section" id="comments-section">
                <h3 style="margin-top: 0; color: var(--texto-titulos); margin-bottom: 20px;"><i class="fas fa-comments"></i> Comentarios</h3>
                
                <div class="comments-list" id="comments-list-<?= $pub['id'] ?>">
                    <?php 
                    $comentarios_pub = $comentarioController->obtenerComentariosPorPublicacion($pub['id']);
                    if (count($comentarios_pub) > 0): 
                        foreach($comentarios_pub as $comentario): 
                    ?>
                        <div class="comment-item">
                            <div class="comment-user"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($comentario['autor_nombre'] ?? 'Usuario') ?></div>
                            <p style="margin: 0;"><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
                        </div>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                        <p style="font-size: 0.9rem; color: #64748b; text-align: center; margin-bottom: 10px;" class="no-comments-msg">Aún no hay comentarios. ¡Sé el primero en opinar!</p>
                    <?php endif; ?>
                </div>

                <form class="comment-form" data-pubid="<?= $pub['id'] ?>">
                    <div class="comment-form-container">
                        <div class="form-control" style="flex-grow: 1; margin-bottom: 0;">
                            <input id="comentario-input" class="input input-alt" placeholder="Escribe tu opinión..." required="" type="text" name="comentario" autocomplete="off" style="padding-bottom: 15px;">
                            <span class="input-border-alt"></span>
                        </div>
                        
                        <button type="submit" class="btn-uiverse btn-send" title="Enviar comentario">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>

        <?php endif; ?>

        <div id="modalAuthRequired" class="modal-overlay" style="display: none;">
            <div class="modal-card">
                <div class="modal-icon info">
                    <i class="fas fa-user-lock"></i>
                </div>
                <h3 style="margin-bottom: 10px; color: #1e293b;">Acción requerida</h3>
                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">Para interactuar con las publicaciones, necesitas iniciar sesión o crear una cuenta.</p>
                
                <div class="modal-btns">
                    <button onclick="window.location.href='inicioSesion.php'" class="btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                    <button onclick="window.location.href='registro.php'" class="btn-primary-modal">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </button>
                </div>
                <div style="margin-top: 15px;">
                    <button onclick="document.getElementById('modalAuthRequired').style.display='none'" class="btn-close-modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

    </main>

    <?php include 'footer.php'; ?>

    <script>
        const usuarioLogueado = <?= json_encode($usuarioLogueado) ?>;
        
        // El script de comentarios adaptado específicamente para esta vista
        document.querySelectorAll('.comment-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!usuarioLogueado) {
                    document.getElementById('modalAuthRequired').style.display = 'flex';
                    return;
                }

                const pubId = this.getAttribute('data-pubid');
                const inputComentario = this.querySelector('input[name="comentario"]');
                const texto = inputComentario.value;

                if (texto.trim() === '') return;
                
                // Deshabilitamos el input y el botón mientras se envía para que no le den doble clic
                const submitBtn = this.querySelector('button[type="submit"]');
                inputComentario.disabled = true;
                submitBtn.disabled = true;

                fetch('../ajax/guardar_comentario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `publicacion_id=${pubId}&contenido=${encodeURIComponent(texto)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.reset();
                        
                        // Quitar el mensaje de "no hay comentarios" si existe
                        const noMsg = document.querySelector('.no-comments-msg');
                        if(noMsg) noMsg.remove();

                        const listaComentarios = document.getElementById(`comments-list-${pubId}`);
                        const nuevoComentario = document.createElement('div');
                        nuevoComentario.className = 'comment-item';
                        nuevoComentario.style.animation = 'fadeIn 0.4s ease';
                        nuevoComentario.innerHTML = `
                            <div class="comment-user"><i class="fas fa-user-circle"></i> ${data.autor}</div>
                            <p style="margin: 0;">${data.contenido}</p>
                        `;
                        listaComentarios.appendChild(nuevoComentario);
                        listaComentarios.scrollTop = listaComentarios.scrollHeight;
                    }
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    inputComentario.disabled = false;
                    submitBtn.disabled = false;
                    inputComentario.focus();
                });
            });
        });
    </script>
    <script src="../js/like-logic.js"></script>
</body>
</html>