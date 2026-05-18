<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';

// ==========================
// 2. Conexión e instancias
// ==========================
$db = (new Conexion())->getConexion();
$catController = new CategoriesController($db);
$pubController = new PublicacionController($db);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioLogueado = isset($_SESSION['usuario_id']);

// ==========================
// 3. Obtener ID de categoría y Término de búsqueda
// ==========================
$categoria_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

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
    // 5. Obtener publicaciones y filtrar si hay búsqueda
    // ==========================
    $pub_stmt = $pubController->obtenerPorCategoria($categoria_id);
    $publicaciones = is_string($pub_stmt) ? [] : $pub_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lógica del buscador en PHP
    if (!empty($buscar)) {
        $publicaciones = array_filter($publicaciones, function($pub) use ($buscar) {
            return stripos(strtolower(strip_tags(html_entity_decode($pub['titulo']))), strtolower($buscar)) !== false || 
                   stripos(strtolower(strip_tags(html_entity_decode($pub['contenido']))), strtolower($buscar)) !== false;
        });
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($categoria) ? htmlspecialchars($categoria['nombre']) : 'Categoría' ?> - Red-novable</title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/categoria-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <header class="categoria-header-full">
        <a href="categorias.php" class="volver">
            <i class="fas fa-arrow-left"></i> Volver a categorías
        </a>

        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 20px; border-radius: 10px; text-align: center; max-width: 600px; margin: 0 auto;">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            <div class="titulo-categoria-full">
                <h1>
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($categoria['nombre']) ?>
                </h1>
                
                <?php if (!empty($categoria['descripcion'])): ?>
                    <?php if(strpos($categoria['descripcion'], 'creada desde editor') !== false): ?>
                        <div class="cat-description-editor">
                            <i class="fas fa-feather-alt"></i>
                            Una nueva sección centrada en soluciones de energía renovable.
                        </div>
                    <?php else: ?>
                        <p class="cat-description-text"><?= nl2br(htmlspecialchars($categoria['descripcion'])) ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="search-container">
                    <form method="GET" action="categoria.php" style="width: 100%;">
                        <input type="hidden" name="id" value="<?= $categoria_id ?>">
                        <input type="text" name="buscar" class="input-search" placeholder="Buscar publicación 🔎" value="<?= htmlspecialchars($buscar) ?>">
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <main class="publicaciones-grid">
        <?php if (!isset($error)): ?>
            <?php if (count($publicaciones) > 0): ?>
                
                <div class="destacados-grid">
                    <?php foreach($publicaciones as $pub): ?>
                        
                        <a href="publicacion.php?id=<?= $pub['id'] ?>" class="uiverse-wrapper">
                            <div class="uiverse-inner">
                                <?php if($pub['imagen']): ?>
                                <div class="destacado-imagen">
                                    <img src="../../assets/<?= htmlspecialchars($pub['imagen']) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>">
                                </div>
                                <?php endif; ?>
                                
                                <div class="destacado-contenido">
                                    <span class="destacado-categoria"><?= htmlspecialchars($categoria['nombre']) ?></span>
                                    
                                    <h3 class="destacado-titulo"><?= htmlspecialchars($pub['titulo']) ?></h3>
                                    
                                    <div class="destacado-fecha">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($pub['autor_nombre'] ?? $pub['autor'] ?? 'Autor') ?> <br>
                                        <i class="fas fa-calendar" style="margin-top: 5px;"></i> <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?>
                                    </div>
                                    
                                    <p class="destacado-resumen">
                                        <?= htmlspecialchars(mb_substr(strip_tags(html_entity_decode($pub['contenido'])), 0, 120)) ?>...
                                    </p>
                                </div>
                            </div>
                        </a>
                        
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="sin-publicaciones">
                    <i class="fas fa-search-minus" style="font-size: 4rem; color: var(--texto-secundario);"></i>
                    <?php if (!empty($buscar)): ?>
                        <h3>No hay resultados para "<?= htmlspecialchars($buscar) ?>"</h3>
                        <p>Intenta con otras palabras clave.</p>
                        <a href="categoria.php?id=<?= $categoria_id ?>" class="volver" style="margin-top: 15px;">Ver todas las publicaciones</a>
                    <?php else: ?>
                        <h3>No hay publicaciones en esta categoría</h3>
                        <p>Sé el primero en compartir contenido sobre <?= htmlspecialchars($categoria['nombre']) ?></p>
                        <?php if ($usuarioLogueado): ?>
                            <a href="../admin/crear_publicacion.php" class="btn-create">
                                <i class="fas fa-plus"></i> Crear publicación
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>