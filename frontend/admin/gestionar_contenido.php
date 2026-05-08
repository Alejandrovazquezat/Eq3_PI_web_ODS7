<?php
session_start();

// Validar permisos: Solo Admin (1) y Editor (2)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 2])) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php';

$db = (new Conexion())->getConexion();
$catController = new CategoriesController($db);
$error_msg = "";
$success_msg = "";

// =========================================
// 1. ACCIONES DE PUBLICACIONES
// =========================================
if (isset($_GET['eliminar_pub'])) {
    $id_eliminar = intval($_GET['eliminar_pub']);
    try {
        $db->beginTransaction();
        $db->prepare("DELETE FROM likes WHERE publicacion_id = ?")->execute([$id_eliminar]);
        $db->prepare("DELETE FROM comentarios WHERE publicacion_id = ?")->execute([$id_eliminar]);
        $db->prepare("DELETE FROM publicaciones WHERE id = ?")->execute([$id_eliminar]);
        $db->commit();
        $success_msg = "Publicación eliminada correctamente.";
    } catch (Exception $e) {
        $db->rollBack();
        $error_msg = "Error al eliminar: " . $e->getMessage();
    }
}

// =========================================
// 2. ACCIONES DE CATEGORÍAS (ELIMINAR Y EDITAR)
// =========================================
if (isset($_GET['eliminar_cat'])) {
    $id_cat = intval($_GET['eliminar_cat']);
    $resultado = $catController->eliminar($id_cat, $_SESSION['usuario_id']);
    if ($resultado['success']) $success_msg = $resultado['message'];
    else $error_msg = $resultado['message'];
}

// Procesar Edición de Categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar_cat') {
    $id_cat = intval($_POST['cat_id']);
    $nombre = trim($_POST['nombre']);
    $desc = trim($_POST['descripcion']);
    
    $res = $catController->actualizar($id_cat, $nombre, $desc, $_SESSION['usuario_id']);
    if ($res['success']) $success_msg = "Categoría actualizada correctamente.";
    else $error_msg = $res['message'];
}

// =========================================
// CONSULTAS
// =========================================
$pubs = $db->query("SELECT p.*, u.nombre as autor, c.nombre as categoria 
                    FROM publicaciones p 
                    JOIN usuarios u ON p.usuario_id = u.id 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    ORDER BY p.id DESC")->fetchAll();

$categorias = $db->query("SELECT c.*, (SELECT COUNT(*) FROM publicaciones p WHERE p.categoria_id = c.id) as total_pubs 
                          FROM categorias c 
                          ORDER BY c.nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Contenido - Red-novable</title>
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/gestionar_contenido.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main">
        <header class="main-header">
            <h1>Gestor de Contenido</h1>
            <p>Control total sobre publicaciones y categorías.</p>
        </header>

        <?php if($success_msg): ?> <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $success_msg ?></div> <?php endif; ?>
        <?php if($error_msg): ?> <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?= $error_msg ?></div> <?php endif; ?>

        <div class="card-table-container">
            <div class="table-header"><h3>📝 Publicaciones Recientes</h3></div>
            <div class="table-responsive">
                <table class="publicaciones-table">
                    <thead><tr><th>Imagen</th><th>Título</th><th>Categoría</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach($pubs as $p): ?>
                        <tr>
                            <td><div class="img-wrapper"><?php if($p['imagen']): ?><img src="../../assets/<?= $p['imagen'] ?>" class="imagen-preview"><?php else: ?>🍃<?php endif; ?></div></td>
                            <td><div class="titulo-meta"><span class="p-titulo"><?= htmlspecialchars($p['titulo']) ?></span><span class="p-autor">Por: <?= htmlspecialchars($p['autor']) ?></span></div></td>
                            <td><span class="cat-tag"><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></span></td>
                            <td><span class="badge-estado <?= strtolower($p['estado']) ?>"><?= $p['estado'] ?></span></td>
                            <td><button class="btn-danger" onclick="abrirModalEliminar('pub', <?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')"><i class="fas fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-table-container">
            <div class="table-header"><h3>🏷️ Gestión de Categorías</h3></div>
            <div class="table-responsive">
                <table class="publicaciones-table">
                    <thead><tr><th>ID</th><th>Nombre</th><th>Uso</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach($categorias as $c): ?>
                        <tr>
                            <td>#<?= $c['id'] ?></td>
                            <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                            <td><span class="badge-count <?= $c['total_pubs']==0?'zero':'' ?>"><?= $c['total_pubs'] ?> posts</span></td>
                            <td>
                                <div style="display:flex; gap:10px;">
                                    <button class="btn-warning" style="background:#f59e0b; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;" 
                                        onclick="abrirModalEditarCat(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['descripcion'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <?php if($c['total_pubs'] == 0): ?>
                                        <button class="btn-danger" onclick="abrirModalEliminar('cat', <?= $c['id'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>')"><i class="fas fa-trash"></i></button>
                                    <?php else: ?>
                                        <button class="btn-disabled" title="Categoría en uso"><i class="fas fa-lock"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modal-editar-cat" class="modal-overlay">
        <div class="modal-box">
            <h2 class="modal-title">Editar Categoría</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="editar_cat">
                <input type="hidden" name="cat_id" id="edit-cat-id">
                <div style="text-align:left; margin-bottom:15px;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Nombre:</label>
                    <input type="text" name="nombre" id="edit-cat-nombre" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;" required>
                </div>
                <div style="text-align:left; margin-bottom:20px;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Descripción:</label>
                    <textarea name="descripcion" id="edit-cat-desc" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; height:80px;"></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-editar-cat')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirm" style="background:#2563eb;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-eliminar" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container"><i class="fas fa-trash-alt"></i></div>
            <h2 class="modal-title" id="el-titulo">¿Eliminar?</h2>
            <p class="modal-text" id="el-msg"></p>
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-el">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let elId = null, elTipo = null;

        function cerrarModal(id) { document.getElementById(id).classList.remove('active'); }

        function abrirModalEliminar(tipo, id, nombre) {
            elId = id; elTipo = tipo;
            document.getElementById('el-titulo').innerText = tipo==='pub' ? '¿Eliminar Publicación?' : '¿Eliminar Categoría?';
            document.getElementById('el-msg').innerHTML = `¿Estás seguro de eliminar <strong>"${nombre}"</strong>?`;
            document.getElementById('modal-eliminar').classList.add('active');
        }

        function abrirModalEditarCat(id, nombre, desc) {
            document.getElementById('edit-cat-id').value = id;
            document.getElementById('edit-cat-nombre').value = nombre;
            document.getElementById('edit-cat-desc').value = desc;
            document.getElementById('modal-editar-cat').classList.add('active');
        }

        document.getElementById('btn-confirmar-el').onclick = function() {
            const param = elTipo === 'pub' ? 'eliminar_pub' : 'eliminar_cat';
            window.location.href = `gestionar_contenido.php?${param}=${elId}`;
        };
    </script>
</body>
</html>