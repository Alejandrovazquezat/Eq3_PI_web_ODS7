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
// LÓGICA PARA RECHAZAR/ELIMINAR PUBLICACIÓN
// ==========================================
if (isset($_GET['rechazar'])) {
    $id_rechazar = intval($_GET['rechazar']);
    $stmt = $db->prepare("DELETE FROM publicaciones WHERE id = ?");
    $stmt->execute([$id_rechazar]);
    header("Location: revisar.php?msg=rechazado");
    exit;
}

// ==========================================
// LÓGICA PARA EDITAR PUBLICACIÓN (NUEVA)
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

// ==========================================
// OBTENER CATEGORÍAS (Para el select del modal de edición)
// ==========================================
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

    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="header-admin">
                <h1 style="color: #1e293b;"><i class="fas fa-check-double"></i> Revisión de Publicaciones</h1>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <?php if($_GET['msg'] == 'aprobado'): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> La publicación ha sido aprobada y ya es visible en la plataforma.
                    </div>
                <?php elseif($_GET['msg'] == 'rechazado'): ?>
                    <div style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <i class="fas fa-trash-alt"></i> La publicación ha sido rechazada y eliminada.
                    </div>
                <?php elseif($_GET['msg'] == 'editado'): ?>
                    <div style="background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <i class="fas fa-edit"></i> La publicación ha sido actualizada correctamente. Sigue en estado "pendiente".
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Fecha de envío</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($pendientes) > 0): ?>
                        <?php foreach($pendientes as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['titulo']) ?></strong></td>
                                <td><i class="fas fa-user-edit" style="color: #94a3b8;"></i> <?= htmlspecialchars($p['autor_nombre'] ?? 'Desconocido') ?></td>
                                <td><span class="cat-tag"><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></span></td>
                                <td style="color: #64748b;"><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                                <td>
                                    <div class="actions-group">
                                        <button class="btn-info" onclick="abrirModalPreview(<?= $p['id'] ?>)" title="Vista Previa">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn-warning" onclick="abrirModalEditar(<?= $p['id'] ?>)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn-success" onclick="abrirModalAccion('aprobar', <?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button class="btn-danger" onclick="abrirModalAccion('rechazar', <?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')" title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div id="data-titulo-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['titulo']) ?></div>
                                    <div id="data-autor-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['autor_nombre'] ?? 'Desconocido') ?></div>
                                    <div id="data-cat-nombre-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></div>
                                    <div id="data-cat-id-<?= $p['id'] ?>" style="display:none;"><?= $p['categoria_id'] ?></div>
                                    <div id="data-fecha-<?= $p['id'] ?>" style="display:none;"><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></div>
                                    <div id="data-contenido-html-<?= $p['id'] ?>" style="display:none;"><?= nl2br(htmlspecialchars($p['contenido'])) ?></div>
                                    <div id="data-contenido-raw-<?= $p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['contenido']) ?></div>
                                    <div id="data-imagen-<?= $p['id'] ?>" style="display:none;"><?= $p['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($p['imagen']) : '' ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fas fa-glass-cheers" style="font-size: 2.5rem; margin-bottom: 15px; display: block; color: #10b981;"></i>
                                <h3 style="margin: 0; color: #1e293b;">¡Todo al día!</h3>
                                <p style="margin-top: 5px;">No hay publicaciones pendientes de revisión en este momento.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modal-preview" class="modal-overlay">
        <div class="modal-box preview-box">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <h2 class="modal-title" id="preview-titulo" style="margin: 0;">Título</h2>
                <button onclick="cerrarModal('modal-preview')" style="background:none; border:none; font-size: 1.8rem; cursor:pointer; color: #94a3b8; line-height: 1;">&times;</button>
            </div>
            
            <div class="preview-meta">
                <i class="fas fa-user-edit"></i> <strong id="preview-autor"></strong> &nbsp;|&nbsp;
                <i class="fas fa-tag"></i> <span id="preview-cat"></span> &nbsp;|&nbsp;
                <i class="fas fa-calendar"></i> <span id="preview-fecha"></span>
            </div>

            <div class="preview-content-scroll">
                <img id="preview-imagen" src="" alt="Imagen adjunta" style="width: 100%; border-radius: 8px; margin-bottom: 15px; display: none; object-fit: contain; max-height: 300px; background: #f8fafc;">
                <div id="preview-contenido"></div>
            </div>
            
            <div class="modal-buttons" style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <button class="btn-modal-cancel" onclick="cerrarModal('modal-preview')">Cerrar Vista Previa</button>
            </div>
        </div>
    </div>

    <div id="modal-editar" class="modal-overlay">
        <div class="modal-box preview-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="modal-title" style="margin: 0; color: #f59e0b;"><i class="fas fa-edit"></i> Editar Publicación</h2>
                <button onclick="cerrarModal('modal-editar')" style="background:none; border:none; font-size: 1.8rem; cursor:pointer; color: #94a3b8; line-height: 1;">&times;</button>
            </div>

            <form action="revisar.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="pub_id" id="edit-id">

                <div class="form-group">
                    <label>Título:</label>
                    <input type="text" name="titulo" id="edit-titulo" class="form-control-modal" required>
                </div>

                <div class="form-group">
                    <label>Categoría:</label>
                    <select name="categoria" id="edit-cat" class="form-control-modal" required>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Contenido:</label>
                    <textarea name="contenido" id="edit-contenido" class="form-control-modal" required></textarea>
                </div>

                <div class="modal-buttons" style="margin-top: 25px;">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirm" style="background: #f59e0b;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-accion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container" id="modal-icon-bg">
                <i id="modal-icon" class="fas fa-question"></i>
            </div>
            <h2 class="modal-title" id="modal-title">¿Confirmar acción?</h2>
            <p class="modal-text" id="modal-mensaje">¿Estás seguro?</p>
            
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal('modal-accion')">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-accion">Sí, confirmar</button>
            </div>
        </div>
    </div>

    <script>
        // Función unificada para cerrar modales
        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // --- LÓGICA VISTA PREVIA ---
        function abrirModalPreview(id) {
            document.getElementById('preview-titulo').innerHTML = document.getElementById('data-titulo-' + id).innerHTML;
            document.getElementById('preview-autor').innerHTML = document.getElementById('data-autor-' + id).innerHTML;
            document.getElementById('preview-cat').innerHTML = document.getElementById('data-cat-nombre-' + id).innerHTML;
            document.getElementById('preview-fecha').innerHTML = document.getElementById('data-fecha-' + id).innerHTML;
            document.getElementById('preview-contenido').innerHTML = document.getElementById('data-contenido-html-' + id).innerHTML;
            
            const imgData = document.getElementById('data-imagen-' + id).innerHTML;
            const imgElement = document.getElementById('preview-imagen');
            if (imgData && imgData.trim() !== '') {
                imgElement.src = imgData;
                imgElement.style.display = 'block';
            } else {
                imgElement.style.display = 'none';
                imgElement.src = '';
            }
            document.getElementById('modal-preview').classList.add('active');
        }

        // --- LÓGICA EDICIÓN ---
        function abrirModalEditar(id) {
            document.getElementById('edit-id').value = id;
            
            // Decodificar el HTML entities para los inputs (el navegador lo hace auto si le pasamos el textContent)
            document.getElementById('edit-titulo').value = document.getElementById('data-titulo-' + id).textContent;
            document.getElementById('edit-contenido').value = document.getElementById('data-contenido-raw-' + id).textContent;
            
            // Seleccionar categoría correcta
            const catId = document.getElementById('data-cat-id-' + id).textContent;
            document.getElementById('edit-cat').value = catId;

            document.getElementById('modal-editar').classList.add('active');
        }

        // --- LÓGICA ACCIÓN (APROBAR/RECHAZAR) ---
        let accionSeleccionada = null;
        let publicacionIdSeleccionada = null;
        const btnConfirmar = document.getElementById('btn-confirmar-accion');

        function abrirModalAccion(accion, id, titulo) {
            accionSeleccionada = accion;
            publicacionIdSeleccionada = id;

            const modalTitle = document.getElementById('modal-title');
            const modalMensaje = document.getElementById('modal-mensaje');
            const modalIcon = document.getElementById('modal-icon');
            const modalIconBg = document.getElementById('modal-icon-bg');

            if (accion === 'aprobar') {
                modalTitle.textContent = '¿Aprobar publicación?';
                modalMensaje.innerHTML = `¿Estás seguro de que quieres APROBAR la publicación <strong>"${titulo}"</strong>? Será visible para todos.`;
                modalIcon.className = 'fas fa-check';
                modalIcon.style.color = '#10b981';
                modalIconBg.style.background = '#d1fae5';
                btnConfirmar.textContent = 'Sí, aprobar';
                btnConfirmar.style.background = '#10b981';
            } else if (accion === 'rechazar') {
                modalTitle.textContent = '¿Rechazar publicación?';
                modalMensaje.innerHTML = `¿Estás seguro de que quieres RECHAZAR la publicación <strong>"${titulo}"</strong>? Se eliminará permanentemente.`;
                modalIcon.className = 'fas fa-times';
                modalIcon.style.color = '#ef4444';
                modalIconBg.style.background = '#fee2e2';
                btnConfirmar.textContent = 'Sí, rechazar';
                btnConfirmar.style.background = '#ef4444';
            }
            document.getElementById('modal-accion').classList.add('active');
        }

        btnConfirmar.addEventListener('click', function() {
            if (accionSeleccionada && publicacionIdSeleccionada) {
                window.location.href = `revisar.php?${accionSeleccionada}=${publicacionIdSeleccionada}`;
            }
        });

        // Cerrar modales si se hace clic en el fondo gris
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                cerrarModal(e.target.id);
            }
        });
    </script>
</body>
</html>