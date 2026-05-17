<?php
session_start();

// Validar que el usuario sea Administrador (1) o Editor (2)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 2])) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once '../../config/Conexion.php';
$db = (new Conexion())->getConexion();

// ==========================================
// LÓGICA PARA APROBAR PUBLICACIÓN
// ==========================================
if (isset($_GET['aprobar'])) {
    $id_aprobar = intval($_GET['aprobar']);
    $stmt = $db->prepare("UPDATE publicaciones SET estado = 'publicado' WHERE id = ?");
    $stmt->execute([$id_aprobar]);
    header("Location: revisar.php?msg=aprobado");
    exit;
}

// ==========================================
// LÓGICA PARA RECHAZAR PUBLICACIÓN (NUEVA: DEVUELVE CON OBSERVACIÓN)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'rechazar') {
    $id_rechazar = intval($_POST['pub_id']);
    $observacion = trim($_POST['observacion']);
    
    // Cambiamos el estado a 'rechazado' y guardamos la observación en lugar de eliminar
    $stmt = $db->prepare("UPDATE publicaciones SET estado = 'rechazado', observacion = ? WHERE id = ?");
    $stmt->execute([$observacion, $id_rechazar]);
    
    header("Location: revisar.php?msg=rechazado");
    exit;
}

// ==========================================
// LÓGICA PARA EDITAR PUBLICACIÓN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id_editar = intval($_POST['pub_id']);
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = intval($_POST['categoria']);
    
    $stmt = $db->prepare("UPDATE publicaciones SET titulo = ?, contenido = ?, categoria_id = ? WHERE id = ?");
    $stmt->execute([$titulo, $contenido, $categoria_id, $id_editar]);
    
    header("Location: revisar.php?msg=editado");
    exit;
}

$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// OBTENER PUBLICACIONES PENDIENTES
// ==========================================
$query = "SELECT p.id, p.titulo, p.contenido, p.imagen, p.fecha_creacion, p.categoria_id,
                 u.nombre AS autor_nombre, 
                 c.nombre AS categoria_nombre 
          FROM publicaciones p 
          LEFT JOIN usuarios u ON p.usuario_id = u.id 
          LEFT JOIN categorias c ON p.categoria_id = c.id 
          WHERE p.estado = 'pendiente' 
          ORDER BY p.fecha_creacion ASC"; 

$pendientes = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Publicaciones - Red-novable</title>
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/revisar_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: flex;">

    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="header-admin">
                <h1 style="color: var(--text-dark);"><i class="fas fa-check-double"></i> Revisión de Publicaciones</h1>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <?php if($_GET['msg'] == 'aprobado'): ?>
                    <div class="alert-review alert-success-review"><i class="fas fa-check-circle"></i> Publicación aprobada con éxito.</div>
                <?php elseif($_GET['msg'] == 'rechazado'): ?>
                    <div class="alert-review alert-danger-review"><i class="fas fa-undo-alt"></i> Devuelta al autor con observaciones de forma correcta.</div>
                <?php elseif($_GET['msg'] == 'editado'): ?>
                    <div class="alert-review alert-info-review"><i class="fas fa-edit"></i> Actualizada correctamente por el moderador.</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="filtro-publicaciones-container-card">
                <div class="filtro-categoria-box">
                    <div class="filtro-label">
                        <i class="fas fa-tags"></i>
                        <label>Categoría:</label>
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

                <div class="total-publicaciones-box">
                    Pendientes: <span id="total-count-badge"><?= count($pendientes) ?></span> artículos
                </div>
            </div>

            <div class="table-cristal-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Título</th><th>Autor</th><th>Categoría</th><th>Fecha</th><th>Acciones</th></tr>
                    </thead>
                    <tbody id="table-publicaciones-body">
                        <?php if(count($pendientes) > 0): ?>
                            <?php foreach($pendientes as $p): ?>
                                <tr class="publicacion-row-item" data-categoria-id="<?= $p['categoria_id'] ?? '0' ?>">
                                    <td><span class="text-muted">#<?= $p['id'] ?></span></td>
                                    <td><strong class="pub-title-cell"><?= htmlspecialchars($p['titulo']) ?></strong></td>
                                    <td class="pub-author-cell"><?= htmlspecialchars($p['autor_nombre'] ?? 'Desconocido') ?></td>
                                    <td><span class="cat-tag"><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></span></td>
                                    <td style="color: var(--text-light); font-size: 0.9rem; font-weight: 500;"><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                                    <td>
                                        <div class="actions-group">
                                            <button class="btn-info" onclick="abrirModalPreview(<?= $p['id'] ?>)" title="Vista Previa"><i class="fas fa-eye"></i></button>
                                            <button class="btn-warning" onclick="abrirModalEditar(<?= $p['id'] ?>)" title="Editar de emergencia"><i class="fas fa-edit"></i></button>
                                            <button class="btn-success" onclick="abrirModalAccion('aprobar', <?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')" title="Aprobar artículo"><i class="fas fa-check"></i></button>
                                            <button class="btn-danger" onclick="abrirModalRechazar(<?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')" title="Devolver con cambios"><i class="fas fa-times"></i></button>
                                        </div>

                                        <div id="data-titulo-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['titulo']) ?></div>
                                        <div id="data-autor-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['autor_nombre'] ?? 'Desconocido') ?></div>
                                        <div id="data-cat-nombre-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></div>
                                        <div id="data-cat-id-<?= $p['id'] ?>" style="display:none;"><?= $p['categoria_id'] ?></div>
                                        <div id="data-fecha-<?= $p['id'] ?>" style="display:none;"><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></div>
                                        <div id="data-contenido-html-<?= $p['id'] ?>" style="display:none;"><?= nl2br(htmlspecialchars($p['contenido'])) ?></div>
                                        <div id="data-contenido-raw-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['contenido']) ?></div>
                                        <div id="data-imagen-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['imagen']) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="no-initial-data-row"><td colspan="6" style="text-align:center; padding:50px; color: var(--text-light);">No hay publicaciones pendientes de revisión en este momento.</td></tr>
                        <?php endif; ?>
                        <tr id="js-empty-pub-row" style="display: none;">
                            <td colspan="6" style="text-align: center; padding: 50px; color: var(--text-light);">
                                <i class="fas fa-search" style="font-size: 2.5rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                No se encontraron publicaciones con los criterios seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modal-preview" class="modal-overlay">
        <div class="modal-box preview-box">
            <h2 class="modal-title" id="preview-titulo"></h2>
            <div class="preview-meta">
                <strong id="preview-autor"></strong> | <span id="preview-cat"></span> | <span id="preview-fecha"></span>
            </div>
            <div class="preview-content-scroll">
                <img id="preview-imagen" src="" alt="Imagen" style="width:100%; border-radius:12px; margin-bottom:15px; display:none; max-height:400px; object-fit:contain; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                <div id="preview-contenido"></div>
            </div>
            <div class="modal-buttons"><button class="btn-modal-cancel" onclick="cerrarModal('modal-preview')">Cerrar Vista Previa</button></div>
        </div>
    </div>

    <div id="modal-editar" class="modal-overlay">
        <div class="modal-box preview-box">
            <h2 class="modal-title">Editar Publicación</h2>
            <form action="revisar.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="pub_id" id="edit-id">
                <div class="form-group"><label>Título:</label><input type="text" name="titulo" id="edit-titulo" class="form-control-modal" required></div>
                <div class="form-group"><label>Categoría:</label>
                    <select name="categoria" id="edit-cat" class="form-control-modal">
                        <?php foreach($categorias as $cat): ?><option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Contenido:</label><textarea name="contenido" id="edit-contenido" class="form-control-modal" required></textarea></div>
                <div class="modal-buttons">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirm confirm-orange">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-accion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container" id="modal-icon-bg"><i id="modal-icon" class="fas fa-question"></i></div>
            <h2 class="modal-title" id="modal-title"></h2>
            <p class="modal-text" id="modal-mensaje"></p>
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal('modal-accion')">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-accion">Sí, confirmar</button>
            </div>
        </div>
    </div>

    <div id="modal-rechazar" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> Devolver Publicación</h2>
                <button onclick="cerrarModal('modal-rechazar')" style="background:none; border:none; font-size: 1.8rem; cursor:pointer; color: #94a3b8; line-height: 1;">&times;</button>
            </div>
            <p style="text-align: left; color: var(--text-dark); margin-bottom: 15px;">Estás devolviendo la publicación: <strong id="rechazar-titulo-texto" style="color: var(--text-dark);"></strong></p>
            <form action="revisar.php" method="POST">
                <input type="hidden" name="accion" value="rechazar">
                <input type="hidden" name="pub_id" id="rechazar-id">
                <div class="form-group">
                    <label>Observación / Motivo del rechazo:</label>
                    <textarea name="observacion" id="rechazar-observacion" class="form-control-modal" placeholder="Explica al autor qué debe corregir para que sea aprobada..." required></textarea>
                </div>
                <div class="modal-buttons" style="margin-top: 20px;">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-rechazar')">Cancelar</button>
                    <button type="submit" class="btn-danger-action">Devolver y Notificar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function cerrarModal(id) { document.getElementById(id).classList.remove('active'); }

        // Cierra los modales al dar clic afuera en el fondo translúcido
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                cerrarModal(e.target.id);
            }
        });

        // LÓGICA DE FILTRADO DUAL COMBINADO EN TIEMPO REAL
        const inputBuscar = document.getElementById('input-buscar-publicacion');
        const selectCategoria = document.getElementById('select-filtro-categoria');
        const rows = document.querySelectorAll('.publicacion-row-item');
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

        function abrirModalPreview(id) {
            document.getElementById('preview-titulo').textContent = document.getElementById('data-titulo-' + id).textContent;
            document.getElementById('preview-autor').textContent = document.getElementById('data-autor-' + id).textContent;
            document.getElementById('preview-cat').textContent = document.getElementById('data-cat-nombre-' + id).textContent;
            document.getElementById('preview-fecha').textContent = document.getElementById('data-fecha-' + id).textContent;
            document.getElementById('preview-contenido').innerHTML = document.getElementById('data-contenido-html-' + id).innerHTML;
            
            const imgName = document.getElementById('data-imagen-' + id).textContent.trim();
            const imgElement = document.getElementById('preview-imagen');
            if (imgName !== '') {
                imgElement.src = '../../assets/' + imgName; 
                imgElement.style.display = 'block';
            } else {
                imgElement.style.display = 'none';
            }
            document.getElementById('modal-preview').classList.add('active');
        }

        function abrirModalEditar(id) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-titulo').value = document.getElementById('data-titulo-' + id).textContent;
            document.getElementById('edit-contenido').value = document.getElementById('data-contenido-raw-' + id).textContent;
            document.getElementById('edit-cat').value = document.getElementById('data-cat-id-' + id).textContent;
            document.getElementById('modal-editar').classList.add('active');
        }

        let accionSeleccionada = null;
        let publicacionIdSeleccionada = null;
        function abrirModalAccion(accion, id, titulo) {
            if(accion === 'aprobar'){
                accionSeleccionada = accion; publicacionIdSeleccionada = id;
                const title = document.getElementById('modal-title');
                const msg = document.getElementById('modal-mensaje');
                const btn = document.getElementById('btn-confirmar-accion');
                
                title.textContent = '¿Aprobar artículo?'; 
                msg.textContent = 'La publicación se hará pública en el sitio inmediatamente.'; 
                btn.className = "btn-modal-confirm confirm-green";
                document.getElementById('modal-accion').classList.add('active');
            }
        }

        document.getElementById('btn-confirmar-accion').addEventListener('click', () => {
            if(accionSeleccionada === 'aprobar'){
                window.location.href = `revisar.php?aprobar=${publicacionIdSeleccionada}`;
            }
        });

        function abrirModalRechazar(id, titulo) {
            document.getElementById('rechazar-id').value = id;
            document.getElementById('rechazar-titulo-texto').textContent = titulo;
            document.getElementById('rechazar-observacion').value = ''; 
            document.getElementById('modal-rechazar').classList.add('active');
        }
    </script>
</body>
</html>