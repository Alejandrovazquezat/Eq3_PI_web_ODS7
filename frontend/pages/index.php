<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';

// ==========================
// 2. Conexión e instancia del controlador
// ==========================
$db = (new Conexion())->getConexion();
$pubController = new PublicacionController($db);

// ==========================
// 3. Obtener publicaciones publicadas
// ==========================
$publicaciones_stmt = $pubController->obtenerPublicadas();

if (is_string($publicaciones_stmt)) {
    $error = $publicaciones_stmt;
    $publicaciones = [];
} else {
    $publicaciones = $publicaciones_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Separar destacados (primeras 4) y el resto
$destacados = array_slice($publicaciones, 0, 4);
$resto = array_slice($publicaciones, 4);

// ==========================
// 4. Iniciar sesión para el navbar
// ==========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>REDnovable</title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/index-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f5f9;">

    <?php include 'navbar.php'; ?>

    <main class="main-content">
        
        <div style="background-image: url('../image/Redenovable_inicio.jpg'); background-size: cover; background-position: center; color: white; padding: 100px 5%; text-align: center; border-radius: 0 0 40px 40px; margin-bottom: 30px; position: relative;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.55); border-radius: 0 0 40px 40px;"></div>
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; font-size: 3rem; margin-bottom: 15px;">Red-novable</h1>
                <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">Difusión de información sobre energías asequibles y no contaminantes.</p>
            </div>
        </div>

        <div class="publicaciones-grid">
            
            <?php if (isset($error)): ?>
                <div style="background: #fee2e2; color: #ef4444; padding: 20px; border-radius: 10px; text-align: center;">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif (count($publicaciones) > 0): ?>
                
                <!-- Destacados horizontal -->
                <div class="destacados-grid">
                    <?php foreach($destacados as $pub): ?>
                    <div class="destacado-card" onclick="window.location.href='categoria.php?id=<?= $pub['categoria_id'] ?>'">
                        <?php if($pub['imagen']): ?>
                        <div class="destacado-imagen">
                            <img src="data:image/jpeg;base64,<?= base64_encode($pub['imagen']) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>">
                        </div>
                        <?php endif; ?>
                        <div class="destacado-contenido">
                            <span class="destacado-categoria"><?= htmlspecialchars($pub['categoria'] ?? 'General') ?></span>
                            <h3 class="destacado-titulo"><?= htmlspecialchars($pub['titulo']) ?></h3>
                            <div class="destacado-fecha">
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?>
                            </div>
                            <p class="destacado-resumen">
                                <?= htmlspecialchars(substr($pub['contenido'], 0, 100)) ?>...
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Separador -->
                <div class="separador">
                    <span>Más publicaciones</span>
                </div>
                
                <!-- Lista vertical -->
                <div class="lista-vertical">
                    <?php foreach($resto as $pub): ?>
                    <div class="vertical-card" onclick="window.location.href='categoria.php?id=<?= $pub['categoria_id'] ?>'">
                        <?php if($pub['imagen']): ?>
                        <div class="vertical-imagen">
                            <img src="data:image/jpeg;base64,<?= base64_encode($pub['imagen']) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>">
                        </div>
                        <?php endif; ?>
                        <div class="vertical-contenido">
                            <span class="vertical-categoria"><?= htmlspecialchars($pub['categoria'] ?? 'General') ?></span>
                            <h2 class="vertical-titulo"><?= htmlspecialchars($pub['titulo']) ?></h2>
                            <div class="vertical-meta">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($pub['autor']) ?> &nbsp;|&nbsp;
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?>
                            </div>
                            <p class="vertical-resumen">
                                <?= htmlspecialchars(substr($pub['contenido'], 0, 200)) ?>...
                            </p>
                            <span class="vertical-leer-mas">Leer más <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
            <?php else: ?>
                <div style="text-align: center; padding: 60px; background: white; border-radius: 16px;">
                    <i class="fas fa-folder-open" style="font-size: 4rem; color: #cbd5e1;"></i>
                    <h3 style="margin-top: 20px;">No hay publicaciones aún</h3>
                    <p>Sé el primero en compartir contenido sobre energías renovables</p>
                </div>
            <?php endif; ?>
            
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>