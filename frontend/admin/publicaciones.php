<?php
session_start();

// Validar permisos: Solo Admin (1) y Editor (2)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 2])) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once __DIR__ . '/../../config/Conexion.php';
$db = (new Conexion())->getConexion();

if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt = $db->prepare("DELETE FROM publicaciones WHERE id = ?");
    $stmt->execute([$id_eliminar]);
    
    header("Location: publicaciones.php?msg=eliminado");
    exit;
}

$pubs = $db->query("SELECT p.*, u.nombre as autor, c.nombre as categoria 
                    FROM publicaciones p 
                    JOIN usuarios u ON p.usuario_id = u.id 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/publicaciones_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Publicaciones - RedRenovable</title>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main">
        <header class="main-header">
            <h1>Gestión de Contenido</h1>
            <p>Administra y supervisa las publicaciones de la red.</p>
        </header>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado'): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Publicación eliminada exitosamente.
            </div>
        <?php endif; ?>

        <div class="card-table-container">
            <div class="table-header">
                <h3>Listado de Publicaciones</h3>
            </div>
            <div class="table-responsive">
                <table class="publicaciones-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título y Autor</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pubs as $item): ?>
                        <tr>
                            <td>
                                <div class="img-wrapper">
                                    <?php if($item['imagen']): ?>
                                        <img src="../../assets/<?= htmlspecialchars($item['imagen']) ?>" class="imagen-preview">
                                    <?php else: ?>
                                        <div class="sin-imagen">🍃</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="titulo-meta">
                                    <span class="p-titulo"><?= htmlspecialchars($item['titulo']) ?></span>
                                    <span class="p-autor">Por: <?= htmlspecialchars($item['autor']) ?></span>
                                </div>
                            </td>
                            <td><span class="cat-tag"><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?></span></td>
                            <td>
                                <span class="badge-estado <?= strtolower($item['estado']) ?>">
                                    <?= ucfirst($item['estado']) ?>
                                </span>
                            </td>
                            <td class="fecha-col"><?= date('d M, Y', strtotime($item['fecha_creacion'])) ?></td>
                            <td>
                                <button class="btn-danger" onclick="abrirModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['titulo'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modal-confirmacion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h2 class="modal-title">¿Eliminar publicación?</h2>
            <p class="modal-text" id="modal-mensaje">¿Estás seguro de que quieres eliminar esta publicación?</p>
            
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-eliminar">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let publicacionIdSeleccionada = null;
        const modal = document.getElementById('modal-confirmacion');
        const mensajeTexto = document.getElementById('modal-mensaje');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');

        function abrirModal(id, titulo) {
            publicacionIdSeleccionada = id;
            mensajeTexto.innerHTML = `¿Estás seguro de que quieres eliminar la publicación <strong>"${titulo}"</strong>? Esta acción no se puede deshacer.`;
            modal.classList.add('active');
        }

        function cerrarModal() {
            modal.classList.remove('active');
            publicacionIdSeleccionada = null;
        }

        btnConfirmar.addEventListener('click', function() {
            if (publicacionIdSeleccionada) {
                window.location.href = `publicaciones.php?eliminar=${publicacionIdSeleccionada}`;
            }
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModal();
            }
        });
    </script>
</body>
</html>