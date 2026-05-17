<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php';

// ==========================
// 2. Conexión e instancia del controlador
// ==========================
$db = (new Conexion())->getConexion();
$catController = new CategoriesController($db);

// ==========================
// 3. Obtener todas las categorías
// ==========================
$categorias_stmt = $catController->obtenerTodas();

if (is_string($categorias_stmt)) {
    $error = $categorias_stmt;
    $categorias = [];
} else {
    $categorias = $categorias_stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Red-novable</title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/categorias-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="hero-categorias">
        <div class="hero-glow-1"></div>
        <div class="hero-glow-2"></div>
        <div class="hero-categorias-content">
            <h1><i class="fas fa-tags" style="font-size: 0.8em; opacity: 0.8;"></i> Categorías</h1>
            <p>Explora todos los temas sobre energías asequibles y no contaminantes.</p>
        </div>
    </div>

    <main class="categorias-container">
        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 20px; border-radius: 10px; text-align: center;">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (count($categorias) > 0): ?>
            <div class="categorias-grid">
                <?php foreach($categorias as $cat): ?>
                
                <a href="categoria.php?id=<?= $cat['id'] ?>" class="uiverse-wrapper">
                    <div class="uiverse-inner">
                        <div class="categoria-icono">
                            <?php 
                            $iconos = [
                                'Energia Solar' => 'fas fa-sun',
                                'Energia Eólica' => 'fas fa-wind',
                                'Biomasa' => 'fas fa-leaf',
                                'Innovacion' => 'fas fa-lightbulb'
                            ];
                            $icono = $iconos[$cat['nombre']] ?? 'fas fa-tag';
                            ?>
                            <i class="<?= $icono ?>"></i>
                        </div>
                        
                        <h3><?= htmlspecialchars($cat['nombre']) ?></h3>
                        
                        <div class="publicaciones-count">
                            <span>Ir a las publicaciones</span> <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </a>

                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="sin-categorias">
                <i class="fas fa-folder-open"></i>
                <h3>No hay categorías disponibles</h3>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <?php include 'mascota.php'; ?>

</body>
</html>