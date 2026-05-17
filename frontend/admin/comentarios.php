<?php
session_start();

// Validar que el usuario sea administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once '../../config/Conexion.php';
$db = (new Conexion())->getConexion();

// LÓGICA PARA ELIMINAR UN COMENTARIO
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt = $db->prepare("DELETE FROM comentarios WHERE id = ?");
    $stmt->execute([$id_eliminar]);
    header("Location: comentarios.php?msg=eliminado");
    exit;
}

// OBTENER CATEGORÍAS PARA EL SELECTOR DE FILTRO
$categorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// OBTENER TODOS LOS COMENTARIOS CON DATOS DEL USUARIO, PUBLICACIÓN Y CATEGORÍA
$query = "SELECT c.id, c.contenido, c.fecha_creacion, 
                 u.nombre AS autor_nombre, u.email AS autor_email,
                 p.titulo AS publicacion_titulo, p.categoria_id, cat.nombre AS categoria_nombre
          FROM comentarios c 
          LEFT JOIN usuarios u ON c.usuario_id = u.id 
          LEFT JOIN publicaciones p ON c.publicacion_id = p.id 
          LEFT JOIN categorias cat ON p.categoria_id = cat.id
          ORDER BY c.fecha_creacion DESC";

$comentarios = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Comentarios - Red-novable Admin</title>
    
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/comentarios_styles.css"> 
    <link href="https://fonts.googleapis.com/css2 family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: flex;">

    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="header-admin">
                <h1 style="color: var(--text-dark);"><i class="fas fa-comments"></i> Gestión de Comentarios</h1>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado'): ?>
                <div class="alert-review alert-success-review">
                    <i class="fas fa-check-circle"></i> Comentario eliminado exitosamente.
                </div>
            <?php endif; ?>

            <div class="filtro-comentarios-container-card">
                <div class="filtro-categoria-box">
                    <div class="filtro-label">
                        <i class="fas fa-tags"></i>
                        <label>Categoría del Post:</label>
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
                    <input type="text" id="input-buscar-comentario" placeholder="Buscar por usuario o correo..." class="input-search-uiverse">
                </div>

                <div class="total-comentarios-box">
                    Total: <span id="total-count-badge"><?= count($comentarios) ?></span> comentarios
                </div>
            </div>

            <div class="table-cristal-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Publicación</th>
                            <th>Comentario</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-comentarios-body">
                        <?php if(count($comentarios) > 0): ?>
                            <?php foreach($comentarios as $c): ?>
                                <tr class="comment-row-item" data-categoria-id="<?= $c['categoria_id'] ?? '0' ?>">
                                    <td><span class="text-muted">#<?= $c['id'] ?></span></td>
                                    <td>
                                        <i class="fas fa-user" style="color: var(--text-light); margin-right: 5px;"></i> 
                                        <strong class="comment-user-cell"><?= htmlspecialchars($c['autor_nombre'] ?? 'Usuario Eliminado') ?></strong>
                                        <span class="comment-email-hidden" style="display:none;"><?= htmlspecialchars($c['autor_email'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <span class="post-title-link"><?= htmlspecialchars($c['publicacion_titulo'] ?? 'Publicación Eliminada') ?></span>
                                        <br><small class="cat-subtitle-tag"><i class="fas fa-tag"></i> <?= htmlspecialchars($c['categoria_nombre'] ?? 'Sin categoría') ?></small>
                                    </td>
                                    <td class="comment-text-cell"><?= htmlspecialchars($c['contenido']) ?></td>
                                    <td style="color: var(--text-light); font-size: 0.9rem; font-weight: 500;"><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])) ?></td>
                                    <td>
                                        <button class="btn-delete-uiverse" onclick="abrirModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['autor_nombre'] ?? 'Usuario', ENT_QUOTES) ?>')" title="Eliminar comentario">
                                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="no-comments-row">
                                <td colspan="6" style="text-align: center; padding: 50px; color: var(--text-light);">
                                    <i class="fas fa-folder-open" style="font-size: 2.5rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                    Aún no hay comentarios registrados en la plataforma.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr id="js-empty-comments-row" style="display: none;">
                            <td colspan="6" style="text-align: center; padding: 50px; color: var(--text-light);">
                                <i class="fas fa-search" style="font-size: 2.5rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                No se encontraron comentarios con los filtros aplicados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modal-confirmacion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container">
                <i class="fas fa-comment-slash"></i>
            </div>
            <h2 class="modal-title">¿Eliminar comentario?</h2>
            <p class="modal-text" id="modal-mensaje">¿Estás seguro de que quieres eliminar el comentario de <strong>Usuario</strong>?</p>
            
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-eliminar">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let comentarioIdSeleccionado = null;
        const modal = document.getElementById('modal-confirmacion');
        const mensajeTexto = document.getElementById('modal-mensaje');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');

        function abrirModal(id, autor) {
            comentarioIdSeleccionado = id;
            mensajeTexto.innerHTML = `¿Estás seguro de que quieres eliminar el comentario de <strong>${autor}</strong>?<br><small style="color: var(--text-light); font-weight: 500;">Esta acción no se puede deshacer.</small>`;
            modal.classList.add('active');
        }

        function cerrarModal() {
            modal.classList.remove('active');
            comentarioIdSeleccionado = null;
        }

        btnConfirmar.addEventListener('click', function() {
            if (comentarioIdSeleccionado) {
                window.location.href = `comentarios.php?eliminar=${comentarioIdSeleccionado}`;
            }
        });

        // ✅ SE CORRIGIÓ: Escuchar clics en el overlay exterior para cerrar de golpe
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModal();
            }
        });

        // ✅ LÓGICA INTEGRADA: FILTRADO DUAL PREDICTIVO (CATEGORÍA + BUSCADOR) EN TIEMPO REAL
        const inputBuscar = document.getElementById('input-buscar-comentario');
        const selectCategoria = document.getElementById('select-filtro-categoria');
        const rows = document.querySelectorAll('.comment-row-item');
        const emptyRow = document.getElementById('js-empty-comments-row');

        function filtrarComentarios() {
            const searchText = inputBuscar.value.toLowerCase().trim();
            const selectedCat = selectCategoria.value;
            let visibles = 0;

            rows.forEach(row => {
                const usuario = row.querySelector('.comment-user-cell').textContent.toLowerCase();
                const email = row.querySelector('.comment-email-hidden').textContent.toLowerCase();
                const rowCatId = row.getAttribute('data-categoria-id');

                const coincideTexto = usuario.includes(searchText) || email.includes(searchText);
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

        if(inputBuscar) inputBuscar.addEventListener('input', filtrarComentarios);
        if(selectCategoria) selectCategoria.addEventListener('change', filtrarComentarios);
    </script>

</body>
</html>