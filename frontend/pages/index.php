<?php
session_start();

$posts = [];
try {
    $db = new PDO('sqlite:../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT posts.*, users.nombre AS autor FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.id DESC";
    $stmt = $db->query($query);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_db = "Error al conectar con la base de datos bro: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Plataforma ODS7</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f5f9;">

    <?php include 'navbar.php'; ?>

    <main class="main-content" style="padding: 0;">
        
        <div style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 80px 5%; text-align: center; border-radius: 0 0 40px 40px; margin-bottom: 50px; box-shadow: 0 10px 30px rgba(37, 99, 235, 0.2);">
            <h1 style="color: white; font-size: 3rem; margin-bottom: 15px; font-weight: 800; letter-spacing: -1px;">Plataforma ODS7</h1>
            <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">Conectando ideas y proyectos para garantizar el acceso a una energía asequible, segura, sostenible y moderna para todos.</p>
        </div>

        <div id="contenedor-posts" style="max-width: 750px; margin: 0 auto; text-align: left; padding: 0 20px 60px;">
            <?php if (isset($error_db)): ?>
                <div style="background: #fee2e2; color: #ef4444; padding: 20px; border-radius: 10px; text-align: center; font-weight: bold;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_db; ?>
                </div>
            <?php elseif (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    
                    <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 25px; position: relative; border: 1px solid #e2e8f0; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                            <button type="button" onclick="abrirModal(<?php echo $post['id']; ?>)" style="position: absolute; top: 25px; right: 25px; background: #fee2e2; border: none; color: #ef4444; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onmouseover="this.style.background='#fca5a5'; this.style.color='#b91c1c'" onmouseout="this.style.background='#fee2e2'; this.style.color='#ef4444'" title="Borrar publicación">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        <?php endif; ?>

                        <div style="display: flex; align-items: center; margin-bottom: 20px;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6, #60a5fa); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin-right: 15px; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">
                                <?php echo strtoupper(substr($post['autor'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 style="color: #0f172a; margin: 0 0 5px 0; font-size: 1.3rem; padding-right: 40px;"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                <div style="color: #64748b; font-size: 0.9rem; font-weight: 500;">
                                    <span style="color: #3b82f6; background: #eff6ff; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; margin-right: 10px;"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($post['categoria']); ?></span>
                                    <span>Publicado por <strong><?php echo htmlspecialchars($post['autor']); ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <p style="line-height: 1.7; color: #334155; font-size: 1.05rem; margin: 0; padding-top: 15px; border-top: 1px solid #f1f5f9;"><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></p>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #cbd5e1;">
                    <i class="fas fa-folder-open" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h3 style="color: #475569; margin-bottom: 10px;">Aún no hay publicaciones</h3>
                    <p style="color: #94a3b8;">¡Sé el primero en aportar una idea a la plataforma!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <div id="modal-borrar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div style="background: white; padding: 40px; border-radius: 20px; text-align: center; max-width: 420px; width: 90%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: aparecer 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="width: 80px; height: 80px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-trash-alt" style="font-size: 2.5rem; color: #ef4444;"></i>
            </div>
            <h3 style="color: #0f172a; margin-bottom: 15px; font-size: 1.5rem;">¿Borrar publicación?</h3>
            <p style="color: #64748b; margin-bottom: 30px; font-size: 1.05rem; line-height: 1.5;">Esta acción no se puede deshacer, bro. ¿Estás totalmente seguro de eliminarla?</p>
            
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="cerrarModal()" style="flex: 1; padding: 12px 20px; border: none; background: #f1f5f9; color: #475569; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 1rem; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'">Cancelar</button>
                
                <form id="form-confirmar-borrar" method="POST" action="borrar_post.php" style="margin: 0; flex: 1;">
                    <input type="hidden" name="post_id" id="modal-post-id" value="">
                    <button type="submit" style="width: 100%; padding: 12px 20px; border: none; background: #ef4444; color: white; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 1rem; transition: 0.2s; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);" onmouseover="this.style.background='#dc2626'">Sí, borrar</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        @keyframes aparecer {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        function abrirModal(postId) {
            document.getElementById('modal-post-id').value = postId;
            document.getElementById('modal-borrar').style.display = 'flex';
        }
        function cerrarModal() {
            document.getElementById('modal-borrar').style.display = 'none';
        }
    </script>
</body>
</html>