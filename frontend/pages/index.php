<?php
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';

$db = (new Conexion())->getConexion();
// ==========================================
// LÓGICA DEL CONTADOR DE VISITAS (CON COOKIES)
// ==========================================
if (!isset($_COOKIE['visita_registrada'])) {
    $stmt = $db->prepare("UPDATE contador_visitas SET total_visitas = total_visitas + 1 WHERE id = 1");
    $stmt->execute();
    setcookie('visita_registrada', 'true', time() + 86400, '/');
}

$pubController = new PublicacionController($db);
$publicaciones_stmt = $pubController->obtenerPublicadas();

if (is_string($publicaciones_stmt)) {
    $error = $publicaciones_stmt;
    $publicaciones = [];
} else {
    $publicaciones = $publicaciones_stmt->fetchAll(PDO::FETCH_ASSOC);
}

$destacados = array_slice($publicaciones, 0, 4);
$resto = array_slice($publicaciones, 4);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red-novable</title>
    <link rel="stylesheet" href="../css/navbar-style.css">
    <link rel="stylesheet" href="../css/index-styles.css">
    <link rel="stylesheet" href="../css/mascota.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="main-content">
        
        <div class="hero-abstract">
            <div class="wind-lines">
                <div class="wind-line w-1"></div>
                <div class="wind-line w-2"></div>
                <div class="wind-line w-3"></div>
                <div class="wind-line w-4"></div>
            </div>

            <div class="wave-layer wave-1"></div>
            <div class="wave-layer wave-2"></div>
            <div class="wave-layer wave-3"></div>

            <div class="hero-text-box">
                <h1>Red-novable</h1>
                <p>Difusión de información sobre energías asequibles y no contaminantes.</p>
                <a href="#seccion-publicaciones" class="btn-hero-scroll">
                    Ver las publicaciones más recientes <i class="fas fa-arrow-down"></i>
                </a>
            </div>
        </div>

        <div id="seccion-publicaciones" class="publicaciones-grid">
            
            <?php if (isset($error)): ?>
                <div style="background: #fee2e2; color: #ef4444; padding: 20px; border-radius: 10px; text-align: center;">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif (count($publicaciones) > 0): ?>
                
                <div class="destacados-grid">
                    <?php foreach($destacados as $pub): ?>
                    <div class="uiverse-wrapper" onclick="window.location.href='publicacion.php?id=<?= $pub['id'] ?>'">
                        <div class="uiverse-inner">
                            <?php if($pub['imagen']): ?>
                            <div class="destacado-imagen">
                                <img src="../../assets/<?= htmlspecialchars($pub['imagen']) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>">
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
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="separador">
                    <span>Más publicaciones</span>
                </div>
                
                <div class="lista-vertical">
                    <?php foreach($resto as $pub): ?>
                    <div class="vertical-card-wrapper" onclick="window.location.href='publicacion.php?id=<?= $pub['id'] ?>'">
                        <div class="vertical-card-inner">
                            <?php if($pub['imagen']): ?>
                            <div class="vertical-imagen">
                                <img src="../../assets/<?= htmlspecialchars($pub['imagen']) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>">
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
                    </div>
                    <?php endforeach; ?>
                </div>
                
            <?php else: ?>
                <div style="text-align: center; padding: 60px; background-color: transparent; border: 1px solid var(--borde-tarjeta); border-radius: 16px;">
                    <i class="fas fa-folder-open" style="font-size: 4rem; color: var(--texto-secundario);"></i>
                    <h3 style="margin-top: 20px; color: var(--text-dark);">No hay publicaciones aún</h3>
                    <p style="color: var(--texto-secundario);">Sé el primero en compartir contenido sobre energías renovables</p>
                </div>
            <?php endif; ?>
            
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>