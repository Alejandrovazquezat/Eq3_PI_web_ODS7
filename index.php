<?php
require_once 'backend/config/database.php';

$database = new Database();
$db = $database->connect();

// Consulta con JOINs para obtener nombres de usuario y categorías
$query = "SELECT p.*, u.nombre AS autor, c.nombre AS categoria_nombre,
          (SELECT COUNT(*) FROM likes WHERE publicacion_id = p.id) AS total_likes
          FROM publicaciones p
          INNER JOIN usuarios u ON p.usuario_id = u.id
          LEFT JOIN categorias c ON p.categoria_id = c.id
          WHERE p.estado = 'publicado' 
          ORDER BY p.fecha_creacion DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Contenidos</title>
    <link rel="stylesheet" href="dyseno_index.css">
</head>
<body>
    <main id="cont">
        <?php foreach ($publicaciones as $pub): ?>
            <div id="cartas">
                <?php 
                    // Lógica para mostrar imagen desde BLOB
                    $imgData = base64_encode($pub['imagen']);
                    $src = $pub['imagen'] ? "data:image/jpeg;base64,{$imgData}" : "placeholder.png";
                ?>
                
                <img src="<?php echo $src; ?>" alt="img_carta">
                <div id="cont_cartas_info">
                    <nav id="cartas_info">
                        <h2><?php echo htmlspecialchars($pub['titulo']); ?></h2>
                        <h3><?php echo htmlspecialchars(substr($pub['contenido'], 0, 50)) . '...'; ?></h3>
                    </nav>

                    <input type="checkbox" id="btn_modal_<?php echo $pub['id']; ?>" class="check_modal" style="display:none;">
                    <label for="btn_modal_<?php echo $pub['id']; ?>" class="lbl_modal" id="lab">Más</label>
                
                    <div class="modal">
                        <div class="contenedor">
                            <label for="btn_modal_<?php echo $pub['id']; ?>" class="lbl_modal" id="lab">X</label>
                            <div class="contenido">
                                <header class="c_hedar">
                                    <h1><?php echo htmlspecialchars($pub['titulo']); ?></h1>
                                    <div id="c_acomodo">
                                        <h3>Publicado por: <?php echo htmlspecialchars($pub['autor']); ?></h3>
                                        <h4>Categoría: <?php echo htmlspecialchars($pub['categoria_nombre'] ?? 'Sin categoría'); ?></h4>
                                    </div>
                                </header>
                                <div class="m_acomodo">
                                    <main class="m_main">
                                        <img id="m_img" src="<?php echo $src; ?>" alt="img_grande">
                                        <h3 id="m_info"><?php echo nl2br(htmlspecialchars($pub['contenido'])); ?></h3>
                                    </main>
                                    <aside class="m_aside">
                                        <section id="m_like">
                                            <form action="dar_like.php" method="POST" class="form-like">
                                                <input type="hidden" name="pub_id" value="<?php echo $pub['id']; ?>">

                                                <input type="checkbox" 
                                                       id="like_<?php echo $pub['id']; ?>" 
                                                       name="like_check" 
                                                       class="input-oculto" 
                                                       onChange="this.form.submit()">

                                                <label for="like_<?php echo $pub['id']; ?>" class="corazon-label">
                                                    <span class="icono-like">❤</span>
                                                    <span class="numero"><?php echo $pub['total_likes']; ?></span>
                                                </label>
                                            </form>
                                        </section>

                                        <section>
                                            <div id="m_comentario_for">
                                                <form action="guardar_comentario.php" method="POST">
                                                    <input type="hidden" name="publicacion_id" value="<?php echo $pub['id']; ?>">
                                                    <label for="comentario_<?php echo $pub['id']; ?>">Comentar:</label>
                                                    <input type="text" id="comentario_<?php echo $pub['id']; ?>" name="comentario" required>
                                                    <input type="submit" value="Enviar">
                                                </form>
                                            </div>
                                            <div >
                                                <h6>Comentarios:</h6>
                                                <div id="m_cometarios">
                                                    <?php
                                                    // Consulta rápida de comentarios para esta publicación
                                                    $stmt_com = $db->prepare("SELECT c.*, u.nombre FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE publicacion_id = ? ORDER BY fecha_creacion DESC");
                                                    $stmt_com->execute([$pub['id']]);
                                                    while($com = $stmt_com->fetch()):
                                                    ?>
                                                        <p><strong><?php echo htmlspecialchars($com['nombre']); ?>:</strong> <?php echo htmlspecialchars($com['contenido']); ?></p>
                                                    <?php endwhile; ?>
                                                </div>
                                            </div>
                                        </section>
                                    </aside>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>