<?php
session_start();

// Validar permisos: Solo Admin (1) y Editor (2)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 2])) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once __DIR__ . '/../../config/Conexion.php';
$db = (new Conexion())->getConexion();
$error_msg = "";
$success_msg = "";

// LÓGICA PARA ELIMINAR UNA PUBLICACIÓN
if (isset($_GET['eliminar_pub'])) {
    $id_eliminar = intval($_GET['eliminar_pub']);
    try {
        $db->beginTransaction();
        $db->prepare("DELETE FROM likes WHERE publicacion_id = ?")->execute([$id_eliminar]);
        $db->prepare("DELETE FROM comentarios WHERE publicacion_id = ?")->execute([$id_eliminar]);
        $db->prepare("DELETE FROM publicaciones WHERE id = ?")->execute([$id_eliminar]);
        $db->commit();
        header("Location: gestionar_publicaciones.php?msg=eliminado");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $error_msg = "Error al eliminar: " . $e->getMessage();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
    $success_msg = "Publicación eliminada correctamente.";
}

// CONSULTAS PARTICULARES
$categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$pubs = $db->query("SELECT p.*, u.nombre as autor, c.nombre as categoria 
                    FROM publicaciones p 
                    JOIN usuarios u ON p.usuario_id = u.id 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Publicaciones - Red-novable</title>
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/gestionar_publicaciones.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: flex;">

    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <header class="main-header">
                <h1>Gestión de Publicaciones</h1>
                <p>Monitorea y elimina el contenido publicado en la comunidad.</p>
            </header>

            <?php if($success_msg): ?> <div class="alert-review alert-success-review"><i class="fas fa-check-circle"></i> <?= $success_msg ?></div> <?php endif; ?>
            <?php if($error_msg): ?> <div class="alert-review alert-danger-review"><i class="fas fa-exclamation-triangle"></i> <?= $error_msg ?></div> <?php endif; ?>

            <div class="filtro-container-card">
                <div class="filtro-box">
                    <div class="filtro-label">
                        <i class="fas fa-tags"></i>
                        <label>Filtrar Categoría:</label>
                    </div>
                    <div class="filtro-form">
                        <select id="select-filtro-categoria">
                            <option value="todos">Todas las categorías</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="search-box-uiverse">
                    <input type="text" id="input-buscar-publicacion" placeholder="Buscar por título o autor..." class="input-search-uiverse">
                </div>

                <div class="total-badge-box">
                    Total: <span id="total-count-badge"><?= count($pubs) ?></span> artículos
                </div>
            </div>

            <div class="table-cristal-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título / Autor</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-publicaciones-body">
                        <?php if(count($pubs) > 0): ?>
                            <?php foreach($pubs as $p): ?>
                                <tr class="pub-row-item" data-categoria-id="<?= $p['categoria_id'] ?? '0' ?>">
                                    <td>
                                        <div class="img-wrapper">
                                            <?php if($p['imagen']): ?>
                                                <img src="../../assets/<?= $p['imagen'] ?>" class="imagen-preview">
                                            <?php else: ?>
                                                🍃
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="titulo-meta">
                                            <span class="p-titulo pub-title-cell"><?= htmlspecialchars($p['titulo']) ?></span>
                                            <span class="p-autor pub-author-cell">Por: <?= htmlspecialchars($p['autor']) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="cat-tag"><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></span></td>
                                    <td><span class="badge-estado <?= strtolower($p['estado']) ?>"><?= $p['estado'] ?></span></td>
                                    <td>
                                        <button class="btn-delete-uiverse" onclick="abrirModalEliminar(<?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')" title="Eliminar Publicación">
                                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="no-data-row"><td colspan="5" style="text-align:center; padding:40px; color:var(--text-light);">No hay publicaciones registradas.</td></tr>
                        <?php endif; ?>
                        <tr id="js-empty-pub-row" style="display: none;">
                            <td colspan="5" style="text-align: center; padding: 50px; color: var(--text-light);">
                                <i class="fas fa-search" style="font-size: 2.5rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                No se encontraron publicaciones con los criterios seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modal-eliminar" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container"><i class="fas fa-trash-alt"></i></div>
            <h2 class="modal-title">¿Eliminar Publicación?</h2>
            <p class="modal-text" id="el-msg"></p>
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-el">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let pubIdSeleccionado = null;
        const modal = document.getElementById('modal-eliminar');

        function abrirModalEliminar(id, nombre) {
            pubIdSeleccionado = id;
            document.getElementById('el-msg').innerHTML = `¿Estás seguro de eliminar de forma permanente la publicación <strong>"${nombre}"</strong>?<br><small style="color:var(--text-light)">Se borrarán los likes y comentarios asociados.</small>`;
            modal.classList.add('active');
        }

        function cerrarModal() {
            modal.classList.remove('active');
            pubIdSeleccionado = null;
        }

        document.getElementById('btn-confirmar-el').onclick = function() {
            if(pubIdSeleccionado) {
                window.location.href = `gestionar_publicaciones.php?eliminar_pub=${pubIdSeleccionado}`;
            }
        };

        window.addEventListener('click', function(e) {
            if (e.target === modal) cerrarModal();
        });

        // LÓGICA DE FILTRADO DINÁMICO EN TIEMPO REAL (JS SPEED)
        const inputBuscar = document.getElementById('input-buscar-publicacion');
        const selectCategoria = document.getElementById('select-filtro-categoria');
        const rows = document.querySelectorAll('.pub-row-item');
        const emptyRow = document.getElementById('js-empty-pub-row');

        function filtrarPublicaciones() {
            const searchText = inputBuscar.value.toLowerCase().trim();
            const selectedCat = selectCategoria.value;
            let visibles = 0;

            rows.forEach(row => {
                const titulo = row.querySelector('.pub-title-cell').textContent.toLowerCase();
                const autor = row.querySelector('.pub-author-cell').textContent.toLowerCase();
                const rowCatId = row.getAttribute('data-categoria-id');

                const coincideTexto = titulo.includes(searchText) || autor.includes(searchText);
                const coincideCat = (selectedCat === 'todos') || (rowCatId === selectedCat);

                if (coincideTexto && coincideCat) {
                    row.style.display = '';
                    visibles++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (emptyRow) {
                emptyRow.style.display = (visibles === 0 && rows.length > 0) ? '' : 'none';
            }
        }

        if(inputBuscar) inputBuscar.addEventListener('input', filtrarPublicaciones);
        if(selectCategoria) selectCategoria.addEventListener('change', filtrarPublicaciones);
    </script>
</body>
</html>