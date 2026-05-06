<?php
session_start();
require_once __DIR__ . '/../../config/Conexion.php';
$db = (new Conexion())->getConexion();

$rolColors = [
    'admin' => '#3b82f6',
    'editor' => '#8b5cf6',
    'autor' => '#f59e0b',
    'usuario' => '#10b981'
];

$roles = $db->query("SELECT id, nombre FROM roles ORDER BY id")->fetchAll();
$rol_seleccionado = isset($_GET['rol']) ? intval($_GET['rol']) : 0;

if ($rol_seleccionado > 0) {
    $query = "SELECT u.id, u.nombre, u.email, r.nombre as rol, u.rol_id
              FROM usuarios u 
              LEFT JOIN roles r ON u.rol_id = r.id 
              WHERE u.rol_id = :rol_id
              ORDER BY u.id DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':rol_id', $rol_seleccionado);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} else {
    $usuarios = $db->query("SELECT u.id, u.nombre, u.email, r.nombre as rol, u.rol_id
                            FROM usuarios u 
                            LEFT JOIN roles r ON u.rol_id = r.id 
                            ORDER BY u.id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css_dash/style.css">
    <link rel="stylesheet" href="../css_dash/dash_usuario_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Usuarios - RED-novable</title>
</head>
<body>
    <nav class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RED-novable</div>
        </div>
        <div class="menu-groups">
            <a href="dashboard.php" class="nav-link">📊 Dashboard general</a>
            <a href="publicaciones.php" class="nav-link">📝 Publicaciones</a>
            <a href="revisar.php" class="nav-link">✅ Pendientes de revisión</a>
            <a href="usuarios.php" class="nav-link active">👥 Usuarios</a>
            <a href="comentarios.php" class="nav-link">💬 Comentarios</a>
            <div class="sidebar-divider"></div>
            <a href="crear_publicacion.php" class="nav-link btn-special">+ Nueva publicación</a>
        </div>
    </nav>

    <main class="main">
        <header class="main-header">
            <h1>Gestión de Usuarios</h1>
            <p>Administra los roles y accesos de la comunidad.</p>
        </header>
        
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="filtro-rol">
            <div class="filtro-label">
                <i class="fas fa-filter"></i>
                <label>Filtrar por rol:</label>
            </div>
            <form method="GET" action="usuarios.php" class="filtro-form">
                <select name="rol">
                    <option value="0">Todos los roles</option>
                    <?php foreach($roles as $rol): ?>
                        <option value="<?= $rol['id'] ?>" <?= $rol_seleccionado == $rol['id'] ? 'selected' : '' ?>>
                            <?= ucfirst($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Filtrar</button>
                <?php if ($rol_seleccionado > 0): ?>
                    <a href="usuarios.php" class="limpiar">Limpiar</a>
                <?php endif; ?>
            </form>
            <div class="total-usuarios">
                Total: <span><?= count($usuarios) ?></span> usuarios
            </div>
        </div>

        <div class="card-table shadow">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach($usuarios as $user): ?>
                        <tr>
                            <td><span class="text-muted">#<?= $user['id'] ?></span></td>
                            <td class="font-bold"><?= htmlspecialchars($user['nombre']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if ($user['id'] == 1): ?>
                                    <span class="badge-root">
                                        <i></i> S.U Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge-role" 
                                          onclick="mostrarModalRol(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nombre']) ?>', <?= $user['rol_id'] ?>)" 
                                          style="background-color: <?= $rolColors[$user['rol']] ?? '#64748b' ?>;">
                                        <?= ucfirst($user['rol'] ?? 'usuario') ?> <i class="fas fa-chevron-down"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($user['id'] == 1): ?>
                                    <span class="locked-text">Irrevocable</span>
                                <?php elseif ($user['id'] != ($_SESSION['usuario_id'] ?? 0)): ?>
                                    <button class="btn-delete-icon" onclick="confirmarEliminar(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nombre']) ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="self-text">Tú</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-msg">No se encontraron usuarios</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modalConfirmacion" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-icon error">
                <i class="fas fa-user-times"></i>
            </div>
            <h3>¿Eliminar usuario?</h3>
            <p id="modalMensaje"></p>
            <div class="modal-btns">
                <button onclick="cerrarModal()" class="btn-secondary">Cancelar</button>
                <button id="btnConfirmarEliminar" class="btn-danger">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <div id="modalCambioRol" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-icon info">
                <i class="fas fa-user-shield"></i>
            </div>
            <h3>Cambiar Nivel de Acceso</h3>
            <p id="modalRolMensaje"></p>
            <select id="selectNuevoRol" class="modal-select">
                <option value="1">Admin</option>
                <option value="2">Editor</option>
                <option value="3">Autor</option>
                <option value="4">Usuario</option>
            </select>
            <div class="modal-btns">
                <button onclick="cerrarModalRol()" class="btn-secondary">Cancelar</button>
                <button onclick="confirmarCambioRol()" class="btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script src="../js/usuarios.js"></script>
</body>
</html>