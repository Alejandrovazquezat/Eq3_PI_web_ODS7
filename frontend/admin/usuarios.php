<?php
session_start();
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();

// Colores para los roles
$rolColors = [
    'admin' => '#3b82f6',
    'editor' => '#8b5cf6',
    'autor' => '#f59e0b',
    'usuario' => '#10b981'
];

// Obtener todos los roles para el select
$roles = $db->query("SELECT id, nombre FROM roles ORDER BY id")->fetchAll();

// Obtener el rol seleccionado (si hay)
$rol_seleccionado = isset($_GET['rol']) ? intval($_GET['rol']) : 0;

// Construir la consulta según el filtro
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
    // Mostrar todos los usuarios
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../css/dash_usuario_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Usuarios - RedRenovable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RedRenovable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link">Publicaciones</a>
        <a href="usuarios.php" class="nav-link active">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Gestión de Usuarios</h1>
        
        <!-- Mensajes de éxito/error -->
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['mensaje_error'] ?>
            </div>
            <?php unset($_SESSION['mensaje_error']); ?>
        <?php endif; ?>
        
        <!-- Filtro por roles -->
        <div class="filtro-rol">
            <label><i class="fas fa-filter"></i> Filtrar por rol:</label>
            <form method="GET" action="usuarios.php" style="display: flex; gap: 10px; align-items: center;">
                <select name="rol">
                    <option value="0">-- Todos los roles --</option>
                    <?php foreach($roles as $rol): ?>
                        <option value="<?= $rol['id'] ?>" <?= $rol_seleccionado == $rol['id'] ? 'selected' : '' ?>>
                            <?= ucfirst($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtrar</button>
                <?php if ($rol_seleccionado > 0): ?>
                    <a href="usuarios.php" class="limpiar" style="padding: 8px 20px; background: #64748b; color: white; text-decoration: none; border-radius: 8px; font-weight: 500;">Limpiar filtro</a>
                <?php endif; ?>
            </form>
            <div class="total-usuarios">
                Total: <span><?= count($usuarios) ?></span> usuarios
                <?php if ($rol_seleccionado > 0): ?>
                    con rol <strong><?= $roles[array_search($rol_seleccionado, array_column($roles, 'id'))]['nombre'] ?? '' ?></strong>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f1f5f9; text-align: left;">
                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">ID</th>
                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Nombre</th>
                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Email</th>
                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Rol</th>
                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach($usuarios as $user): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px;"><?= $user['id'] ?></td>
                            <td style="padding: 12px; font-weight: 500;"><?= htmlspecialchars($user['nombre']) ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($user['email']) ?></td>
                            <td style="padding: 12px;">
                                <?php if ($user['id'] == 1): ?>
                                    <!-- Alejandro Admin Supremo - Protegido -->
                                    <span style="background: linear-gradient(135deg, #f59e0b, #ef4444); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                        <i></i> Admin Supremo
                                    </span>
                                <?php else: ?>
                                    <!-- Rol editable con cursor pointer -->
                                    <span onclick="mostrarModalRol(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nombre']) ?>', <?= $user['rol_id'] ?>)" 
                                          style="background-color: <?= $rolColors[$user['rol']] ?? '#64748b' ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer;">
                                        <?= ucfirst($user['rol'] ?? 'usuario') ?> <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <?php if ($user['id'] == 1): ?>
                                    <!-- Alejandro Admin Supremo - Intocable -->
                                    <span style="color: #f59e0b; font-size: 0.8rem; font-weight: bold;">
                                        <i></i> Irrevocable
                                    </span>
                                <?php elseif ($user['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="confirmarEliminar(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nombre']) ?>')" 
                                            style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.8rem;">Usuario actual</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">
                                <?php if ($rol_seleccionado > 0): ?>
                                    No hay usuarios con este rol
                                <?php else: ?>
                                    No hay usuarios registrados
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal de confirmación para eliminar -->
    <div id="modalConfirmacion" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ef4444; margin-bottom: 15px;"></i>
            <h3>¿Eliminar usuario?</h3>
            <p id="modalMensaje">¿Estás seguro de que quieres eliminar a <strong></strong>?</p>
            <p style="color: #64748b; font-size: 0.9rem;">Esta acción no se puede deshacer.</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                <button onclick="cerrarModal()" style="padding: 8px 20px; background: #64748b; color: white; border: none; border-radius: 6px; cursor: pointer;">Cancelar</button>
                <button id="btnConfirmarEliminar" style="padding: 8px 20px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer;">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar rol -->
    <div id="modalCambioRol" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px;">
            <i class="fas fa-user-tag" style="font-size: 3rem; color: #3b82f6; margin-bottom: 15px;"></i>
            <h3>Cambiar Rol</h3>
            <p id="modalRolMensaje"></p>
            <select id="selectNuevoRol" style="width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #cbd5e1; border-radius: 8px;">
                <option value="1">Admin</option>
                <option value="2">Editor</option>
                <option value="3">Autor</option>
                <option value="4">Usuario</option>
            </select>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="cerrarModalRol()" style="padding: 8px 20px; background: #64748b; color: white; border: none; border-radius: 6px; cursor: pointer;">Cancelar</button>
                <button onclick="confirmarCambioRol()" style="padding: 8px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer;">Cambiar Rol</button>
            </div>
        </div>
    </div>

    <script src="../js/usuarios.js"></script>
</body>
</html>