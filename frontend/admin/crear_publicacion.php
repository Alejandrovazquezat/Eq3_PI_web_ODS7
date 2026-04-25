<?php
// ==========================
// 1. Cargar dependencias
// ==========================
require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/PublicacionController.php';
require_once __DIR__ . '/../../backend/controllers/CategoriesController.php';

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

if (!$auth->tienePermiso($usuario_id, 'crear_publicacion')) {
    header("Location: ../pages/index.php");
    exit;
}

// ==========================
// 4. Obtener categorías para el select
// ==========================
$catController = new CategoriesController($db);
$categorias_stmt = $catController->obtenerTodas();
$categorias = is_object($categorias_stmt) ? $categorias_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// ==========================
// 5. Procesar formulario
// ==========================
$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $categoria_id = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
    $imagen_binaria = null;
    
    // Validar campos obligatorios
    if (empty($titulo) || empty($contenido)) {
        $error = "El título y el contenido son obligatorios";
    } else {
        // Procesar imagen si se subió (mantenemos BLOB por ahora)
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            
            if (in_array($extension, $allowed)) {
                $imagen_binaria = file_get_contents($_FILES['imagen']['tmp_name']);
            } else {
                $error = "Formato de imagen no permitido. Usa: jpg, jpeg, png, gif, webp";
            }
        }
        
        if (empty($error)) {
            // Usar el controlador para crear la publicación (el estado se asigna automáticamente por rol)
            $pubController = new PublicacionController($db);
            $resultado = $pubController->crear($titulo, $contenido, $imagen_binaria, $categoria_id, $usuario_id);
            
            // El controlador devuelve un string con el mensaje
            if (strpos($resultado, 'correctamente') !== false) {
                $mensaje = $resultado;
                // Limpiar variables para nuevo formulario
                $titulo = $contenido = "";
                $categoria_id = null;
            } else {
                $error = $resultado;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../css/crear_publicacion_styles.css">
    <title>Crear Publicación - RedRenovable</title>
</head>
<body>
    <div class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'" style="cursor: pointer;">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RedRenovable</div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="publicaciones.php" class="nav-link">Publicaciones</a>
        <a href="usuarios.php" class="nav-link">Usuarios</a>
        <a href="crear_publicacion.php" class="nav-link active">+ Nueva publicación</a>
    </div>

    <main class="main">
        <h1>Crear Nueva Publicación</h1>
        
        <div class="form-container">
            <?php if ($mensaje): ?>
                <div class="mensaje-exito"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="mensaje-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título *</label>
                    <input type="text" id="titulo" name="titulo" required value="<?= htmlspecialchars($titulo ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="categoria_id">Categoría *</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <option value="">-- Seleccionar categoría --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($categoria_id) && $categoria_id == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Eliminamos el campo "Estado" porque el controlador lo asigna automáticamente según el rol -->
                
                <div class="form-group">
                    <label for="imagen">Imagen (opcional)</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)">
                    <div id="preview" class="preview-imagen"></div>
                </div>
                
                <div class="form-group">
                    <label for="contenido">Contenido *</label>
                    <textarea id="contenido" name="contenido" required><?= htmlspecialchars($contenido ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit">Publicar</button>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 10px;">
                    * Si eres autor, la publicación quedará pendiente de aprobación.<br>
                    * Administradores y editores publican directamente.
                </p>
            </form>
        </div>
    </main>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>