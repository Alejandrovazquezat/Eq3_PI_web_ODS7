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

// ELIMINAR CATEGORÍA
if (isset($_GET['eliminar_cat'])) {
    $id_cat = intval($_GET['eliminar_cat']);
    $resultado = $catController->eliminar($id_cat, $_SESSION['usuario_id']);
    if ($resultado['success']) {
        header("Location: gestionar_categorias.php?msg=eliminado");
        exit;
    } else {
        $error_msg = $resultado['message'];
    }
}

// PROCESAR EDICIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar_cat') {
    $id_cat = intval($_POST['cat_id']);
    $nombre = trim($_POST['nombre']);
    $desc = trim($_POST['descripcion']);
    
    $res = $catController->actualizar($id_cat, $nombre, $desc, $_SESSION['usuario_id']);
    if ($res['success']) {
        header("Location: gestionar_categorias.php?msg=editado");
        exit;
    } else {
        $error_msg = $res['message'];
    }
}

if(isset($_GET['msg'])) {
    if($_GET['msg'] === 'eliminado') $success_msg = "Categoría eliminada correctamente.";
    if($_GET['msg'] === 'editado') $success_msg = "Categoría actualizada correctamente.";
}

// CONSULTA DE CATEGORÍAS
$categorias = $db->query("SELECT c.*, (SELECT COUNT(*) FROM publicaciones p WHERE p.categoria_id = c.id) as total_pubs 
                          FROM categorias c 
                          ORDER BY c.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - Red-novable</title>
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/gestionar_categorias.css">
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
                <h1>Gestión de Categorías</h1>
                <p>Administra, edita o elimina las temáticas de la plataforma.</p>
            </header>

            <?php if($success_msg): ?> <div class="alert-review alert-success-review"><i class="fas fa-check-circle"></i> <?= $success_msg ?></div> <?php endif; ?>
            <?php if($error_msg): ?> <div class="alert-review alert-danger-review"><i class="fas fa-exclamation-triangle"></i> <?= $error_msg ?></div> <?php endif; ?>

            <div class="filtro-container-card">
                <div class="filtro-box">
                    <div class="filtro-label">
                        <i class="fas fa-search"></i>
                        <label>Buscar Categoría:</label>
                    </div>
                </div>
                <div class="search-box-uiverse">
                    <input type="text" id="input-buscar-categoria" placeholder="Escribe la categoría aquí..." class="input-search-uiverse">
                </div>
                <div class="total-badge-box">
                    Total: <span id="total-count-badge"><?= count($categorias) ?></span> categorías
                </div>
            </div>

            <div class="table-cristal-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Categoría</th>
                            <th>Uso en Plataforma</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-categorias-body">
                        <?php foreach($categorias as $c): ?>
                        <tr class="cat-row-item">
                            <td><span class="text-muted">#<?= $c['id'] ?></span></td>
                            <td><strong class="cat-name-cell"><?= htmlspecialchars($c['nombre']) ?></strong></td>
                            <td><span class="badge-count <?= $c['total_pubs'] == 0 ? 'zero' : '' ?>"><?= $c['total_pubs'] ?> posts asignados</span></td>
                            <td>
                                <div class="actions-group">
                                    <button class="btn-warning-action" onclick="abrirModalEditarCat(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['descripcion'], ENT_QUOTES) ?>')" title="Editar Categoría">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <?php if($c['total_pubs'] == 0): ?>
                                        <button class="btn-delete-uiverse" onclick="abrirModalEliminar(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>')" title="Eliminar Categoría">
                                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-disabled-glass" title="Categoría bloqueada (Tiene artículos vinculados)"><i class="fas fa-lock"></i> En uso</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="js-empty-cat-row" style="display: none;">
                            <td colspan="4" style="text-align: center; padding: 50px; color: var(--text-light);">
                                <i class="fas fa-search" style="font-size: 2.5rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                No se encontraron categorías con ese nombre.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modal-editar-cat" class="modal-overlay">
        <div class="modal-box text-center-box">
            <h2 class="modal-title">Editar Categoría</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="editar_cat">
                <input type="hidden" name="cat_id" id="edit-cat-id">
                <div class="form-group">
                    <label>Nombre de Categoría:</label>
                    <input type="text" name="nombre" id="edit-cat-nombre" class="form-control-modal" required>
                </div>
                <div class="form-group">
                    <label>Descripción detallada:</label>
                    <textarea name="descripcion" id="edit-cat-desc" class="form-control-modal" style="height:100px; resize:none;"></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modal-editar-cat')">Cancelar</button>
                    <button type="submit" class="btn-modal-confirm confirm-blue">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-eliminar" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container"><i class="fas fa-trash-alt"></i></div>
            <h2 class="modal-title">¿Eliminar Categoría?</h2>
            <p class="modal-text" id="el-msg"></p>
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-el">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let catIdSeleccionado = null;
        const modalEliminar = document.getElementById('modal-eliminar');
        const modalEditar = document.getElementById('modal-editar-cat');

        function cerrarModal(id) { document.getElementById(id).classList.remove('active'); }

        function abrirModalEliminar(id, nombre) {
            catIdSeleccionado = id;
            document.getElementById('el-msg').innerHTML = `¿Estás seguro de eliminar la categoría <strong>"${nombre}"</strong>?<br><small style="color:var(--text-light)">Esta acción es irreversible.</small>`;
            modalEliminar.classList.add('active');
        }

        function abrirModalEditarCat(id, nombre, desc) {
            document.getElementById('edit-cat-id').value = id;
            document.getElementById('edit-cat-nombre').value = nombre;
            document.getElementById('edit-cat-desc').value = desc;
            modalEditar.classList.add('active');
        }

        document.getElementById('btn-confirmar-el').onclick = function() {
            if(catIdSeleccionado) {
                window.location.href = `gestionar_categorias.php?eliminar_cat=${catIdSeleccionado}`;
            }
        };

        // Cierre de ventanas al hacer clic por fuera
        window.addEventListener('click', function(e) {
            if (e.target === modalEliminar) modalEliminar.classList.remove('active');
            if (e.target === modalEditar) modalEditar.classList.remove('active');
        });

        // BÚSQUEDA PREDICTIVA LOCAL EN TIEMPO REAL
        document.getElementById('input-buscar-categoria').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.cat-row-item');
            const emptyRow = document.getElementById('js-empty-cat-row');
            let visibles = 0;

            rows.forEach(row => {
                const nombre = row.querySelector('.cat-name-cell').textContent.toLowerCase();
                if (nombre.includes(query)) {
                    row.style.display = '';
                    visibles++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (emptyRow) {
                emptyRow.style.display = (visibles === 0 && rows.length > 0) ? '' : 'none';
            }
        });
    </script>
</body>
</html>