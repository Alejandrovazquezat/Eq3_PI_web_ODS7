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
    
    try {
        $db->beginTransaction();
        $stmt_likes = $db->prepare("DELETE FROM likes WHERE publicacion_id = ?");
        $stmt_likes->execute([$id_rechazar]);
        $stmt_comentarios = $db->prepare("DELETE FROM comentarios WHERE publicacion_id = ?");
        $stmt_comentarios->execute([$id_rechazar]);
        $stmt_pub = $db->prepare("DELETE FROM publicaciones WHERE id = ?");
        $stmt_pub->execute([$id_rechazar]);
        $db->commit();
        header("Location: revisar.php?msg=rechazado");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        die("Error crítico: " . $e->getMessage());
    }
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

    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="header-admin">
                <h1 style="color: #1e293b;"><i class="fas fa-check-double"></i> Revisión de Publicaciones</h1>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <?php if($_GET['msg'] == 'aprobado'): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> Aprobada.</div>
                <?php elseif($_GET['msg'] == 'rechazado'): ?>
                    <div style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><i class="fas fa-trash-alt"></i> Eliminada.</div>
                <?php elseif($_GET['msg'] == 'editado'): ?>
                    <div style="background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin-bottom: 20px;"><i class="fas fa-edit"></i> Actualizada correctamente.</div>
                <?php endif; ?>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr><th>ID</th><th>Título</th><th>Autor</th><th>Categoría</th><th>Fecha</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if(count($pendientes) > 0): ?>
                        <?php foreach($pendientes as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['titulo']) ?></strong></td>
                                <td><?= htmlspecialchars($p['autor_nombre'] ?? 'Desconocido') ?></td>
                                <td><span class="cat-tag"><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                                <td>
                                    <div class="actions-group">
                                        <button class="btn-info" onclick="abrirModalPreview(<?= $p['id'] ?>)"><i class="fas fa-eye"></i></button>
                                        <button class="btn-warning" onclick="abrirModalEditar(<?= $p['id'] ?>)"><i class="fas fa-edit"></i></button>
                                        <button class="btn-success" onclick="abrirModalAccion('aprobar', <?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')"><i class="fas fa-check"></i></button>
                                        <button class="btn-danger" onclick="abrirModalAccion('rechazar', <?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')"><i class="fas fa-times"></i></button>
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
                        <tr><td colspan="6" style="text-align:center; padding:40px;">No hay pendientes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modal-preview" class="modal-overlay">
        <div class="modal-box preview-box">
            <h2 class="modal-title" id="preview-titulo"></h2>
            <div class="preview-meta">
                <strong id="preview-autor"></strong> | <span id="preview-cat"></span> | <span id="preview-fecha"></span>
            </div>
            <div class="preview-content-scroll">
                <img id="preview-imagen" src="" alt="Imagen" style="width:100%; border-radius:8px; margin-bottom:15px; display:none; max-height:400px; object-fit:contain;">
                <div id="preview-contenido"></div>
            </div>
            <div class="modal-buttons"><button class="btn-modal-cancel" onclick="cerrarModal('modal-preview')">Cerrar</button></div>
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
                <div class="modal-buttons"><button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button><button type="submit" class="btn-modal-confirm">Guardar</button></div>
            </form>
        </div>
    </div>

    <div id="modal-accion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container" id="modal-icon-bg"><i id="modal-icon" class="fas fa-question"></i></div>
            <h2 class="modal-title" id="modal-title"></h2>
            <p class="modal-text" id="modal-mensaje"></p>
            <div class="modal-buttons"><button class="btn-modal-cancel" onclick="cerrarModal('modal-accion')">Cancelar</button><button class="btn-modal-confirm" id="btn-confirmar-accion">Sí, confirmar</button></div>
        </div>
    </div>

    <script>
        function cerrarModal(id) { document.getElementById(id).classList.remove('active'); }

        function abrirModalPreview(id) {
            document.getElementById('preview-titulo').textContent = document.getElementById('data-titulo-' + id).textContent;
            document.getElementById('preview-autor').textContent = document.getElementById('data-autor-' + id).textContent;
            document.getElementById('preview-cat').textContent = document.getElementById('data-cat-nombre-' + id).textContent;
            document.getElementById('preview-fecha').textContent = document.getElementById('data-fecha-' + id).textContent;
            document.getElementById('preview-contenido').innerHTML = document.getElementById('data-contenido-html-' + id).innerHTML;
            
  
            const imgName = document.getElementById('data-imagen-' + id).textContent.trim();
            const imgElement = document.getElementById('preview-imagen');
            if (imgName !== '') {
                imgElement.src = '../../assets/' + imgName; // Ruta hacia tu carpeta de fotos
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
            accionSeleccionada = accion; publicacionIdSeleccionada = id;
            const title = document.getElementById('modal-title');
            const msg = document.getElementById('modal-mensaje');
            const btn = document.getElementById('btn-confirmar-accion');
            if (accion === 'aprobar') {
                title.textContent = '¿Aprobar?'; msg.textContent = 'Se hará pública.'; btn.style.background = '#10b981';
            } else {
                title.textContent = '¿Rechazar?'; msg.textContent = 'Se eliminará.'; btn.style.background = '#ef4444';
            }
            document.getElementById('modal-accion').classList.add('active');
        }

        document.getElementById('btn-confirmar-accion').addEventListener('click', () => {
            window.location.href = `revisar.php?${accionSeleccionada}=${publicacionIdSeleccionada}`;
        });
    </script>
</body>
</html>