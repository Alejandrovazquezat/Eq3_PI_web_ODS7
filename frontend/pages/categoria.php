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
// 6. Iniciar sesión para el navbar
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($categoria) ? htmlspecialchars($categoria['nombre']) : 'Categoría' ?> - Redrenovable</title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/categoria-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .publicacion-imagen {
            margin-bottom: 20px;
            text-align: center;
        }
        .publicacion-imagen img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
        }
    </style>
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
                <?php foreach($publicaciones as $pub): ?>
                <div class="publicacion-card">
                    
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
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sin-publicaciones">
                    <i class="fas fa-folder-open"></i>
                    <h3>No hay publicaciones en esta categoría aún</h3>
                    <p>Sé el primero en compartir contenido sobre <?= htmlspecialchars($categoria['nombre']) ?></p>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="../admin/crear_publicacion.php" style="display: inline-block; margin-top: 20px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 10px 24px; border-radius: 8px; text-decoration: none;">
                            <i class="fas fa-plus"></i> Crear publicación
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>