<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/UsuarioController.php';

// ==========================
// 2. Conexión y sesión
// ==========================
$db = (new Conexion())->getConexion();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// 3. Verificar autenticación y permiso
// ==========================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/inicioSesion.php");
    exit;
}

$auth = new AuthController($db);
$usuario_id = $_SESSION['usuario_id'];

if (!$auth->tienePermiso($usuario_id, 'crear_usuario')) {
    header("Location: ../pages/index.php");
    exit;
}

// ==========================
// 4. Obtener roles para el filtro
// ==========================
$roles = $db->query("SELECT id, nombre FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// ==========================
// 5. Obtener usuarios según filtro
// ==========================
$usuarioController = new UsuarioController($db);
$rol_seleccionado = isset($_GET['rol']) ? intval($_GET['rol']) : 0;

if ($rol_seleccionado > 0) {
    $result = $usuarioController->listarPorRol($usuario_id, $rol_seleccionado);
} else {
    $result = $usuarioController->listarTodos($usuario_id);
}

if (is_string($result)) {
    $error_usuarios = $result;
    $usuarios = [];
} else {
    $usuarios = $result->fetchAll(PDO::FETCH_ASSOC);
}

// ==========================
// 6. Colores para roles
// ==========================
$rolColors = [
    'admin' => '#3b82f6',
    'editor' => '#8b5cf6',
    'autor' => '#f59e0b',
    'usuario' => '#10b981'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../css/dash_usuario_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Usuarios - Red-novable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">Red-novable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link">Publicaciones</a>
        <a href="usuarios.php" class="nav-link active">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Gestión de Usuarios</h1>
        
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['mensaje_error']) ?>
            </div>
            <?php unset($_SESSION['mensaje_error']); ?>
        <?php endif; ?>
        
        <?php if (isset($error_usuarios)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_usuarios) ?>
            </div>
        <?php endif; ?>
        
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
                    con rol <strong><?= htmlspecialchars($roles[array_search($rol_seleccionado, array_column($roles, 'id'))]['nombre'] ?? '') ?></strong>
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
                        <?php foreach($usuarios as $user): 
                            $nombre_rol = $user['rol_nombre'] ?? 'usuario';
                            $color_rol = $rolColors[$nombre_rol] ?? '#64748b';
                        ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px;"><?= $user['id'] ?></td>
                            <td style="padding: 12px; font-weight: 500;"><?= htmlspecialchars($user['nombre']) ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($user['email']) ?></td>
                            <td style="padding: 12px;">
                                <?php if ($user['id'] == 1): ?>
                                    <span style="background: linear-gradient(135deg, #f59e0b, #ef4444); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">Admin Supremo</span>
                                <?php else: ?>
                                    <span onclick="mostrarModalRol(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nombre'], ENT_QUOTES) ?>', <?= $user['rol_id'] ?>)" 
                                          style="background-color: <?= $color_rol ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer;">
                                        <?= ucfirst($nombre_rol) ?> <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <?php if ($user['id'] == 1): ?>
                                    <span style="color: #f59e0b; font-size: 0.8rem; font-weight: bold;">Irrevocable</span>
                                <?php elseif ($user['id'] != $usuario_id): ?>
                                    <button onclick="confirmarEliminar(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nombre'], ENT_QUOTES) ?>')" 
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

    <div id="modalCambioRol" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px;">
            <i class="fas fa-user-tag" style="font-size: 3rem; color: #3b82f6; margin-bottom: 15px;"></i>
            <h3>Cambiar Rol</h3>
            <p id="modalRolMensaje"></p>
            <select id="selectNuevoRol" style="width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #cbd5e1; border-radius: 8px;">
                <?php foreach ($roles as $rol): ?>
                    <option value="<?= $rol['id'] ?>"><?= ucfirst($rol['nombre']) ?></option>
                <?php endforeach; ?>
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