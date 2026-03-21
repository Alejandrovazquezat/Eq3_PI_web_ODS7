<?php
session_start();
require_once 'Conexion.php';
$db = (new Conexion())->getConexion();

// Obtener todos los roles para el select
$roles = $db->query("SELECT id, nombre FROM roles ORDER BY id")->fetchAll();

// Obtener el rol seleccionado (si hay)
$rol_seleccionado = isset($_GET['rol']) ? intval($_GET['rol']) : 0;

// Construir la consulta según el filtro
if ($rol_seleccionado > 0) {
    $query = "SELECT u.id, u.nombre, u.email, r.nombre as rol 
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
    $usuarios = $db->query("SELECT u.id, u.nombre, u.email, r.nombre as rol 
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
                                <?php 
                                $rolColors = [
                                    'admin' => '#3b82f6',
                                    'editor' => '#8b5cf6',
                                    'autor' => '#f59e0b',
                                    'usuario' => '#10b981'
                                ];
                                $color = $rolColors[$user['rol']] ?? '#64748b';
                                ?>
                                <span style="background-color: <?= $color ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">
                                    <?= ucfirst($user['rol'] ?? 'usuario') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 40px; text-align: center; color: #64748b;">
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
</body>
</html>