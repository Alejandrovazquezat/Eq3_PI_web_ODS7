<?php
session_start();

// Opcional: Validar permisos (Recomendado para proteger la eliminación)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 2, 3])) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once __DIR__ . '/../../config/Conexion.php';
$db = (new Conexion())->getConexion();

// ==========================================
// LÓGICA PARA ELIMINAR UNA PUBLICACIÓN
// ==========================================
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    // Primero, si tienes una tabla de 'likes' o 'comentarios' vinculada a esta publicación, 
    // asegúrate de que tu base de datos tenga "ON DELETE CASCADE" configurado, 
    // o elimínalos manualmente antes de borrar la publicación.
    $stmt = $db->prepare("DELETE FROM publicaciones WHERE id = ?");
    $stmt->execute([$id_eliminar]);
    
    header("Location: publicaciones.php?msg=eliminado");
    exit;
}

// ==========================================
// OBTENER TODAS LAS PUBLICACIONES
// ==========================================
$pubs = $db->query("SELECT p.*, u.nombre as autor, c.nombre as categoria 
                    FROM publicaciones p 
                    JOIN usuarios u ON p.usuario_id = u.id 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/publicaciones_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Publicaciones - RedRenovable</title>
</head>
<body>
    <nav class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">RED-novable</div>
        </div>
        <div class="menu-groups">
            <a href="dashboard.php" class="nav-link">📊 Dashboard general</a>
            <a href="publicaciones.php" class="nav-link active">📝 Publicaciones</a>
            <a href="revisar.php" class="nav-link">✅ Pendientes de revisión</a>
            <a href="usuarios.php" class="nav-link">👥 Usuarios</a>
            <a href="comentarios.php" class="nav-link">💬 Comentarios</a>
            <a href="crear_publicacion.php" class="nav-link btn-special">+ Nueva publicación</a>
        </div>
    </nav>

    <main class="main">
        <header class="main-header">
            <h1>Gestión de Contenido</h1>
            <p>Administra y supervisa las publicaciones de la red.</p>
        </header>

        <!-- Mensaje de éxito al eliminar -->
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado'): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Publicación eliminada exitosamente.
            </div>
        <?php endif; ?>

        <div class="card-table-container">
            <div class="table-header">
                <h3>Listado de Publicaciones</h3>
            </div>
            <div class="table-responsive">
                <table class="publicaciones-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título y Autor</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th> <!-- Nueva columna añadida -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pubs as $item): ?>
                        <tr>
                            <td>
                                <div class="img-wrapper">
                                    <?php if($item['imagen']): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($item['imagen']) ?>" class="imagen-preview">
                                    <?php else: ?>
                                        <div class="sin-imagen">🍃</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="titulo-meta">
                                    <span class="p-titulo"><?= htmlspecialchars($item['titulo']) ?></span>
                                    <span class="p-autor">Por: <?= htmlspecialchars($item['autor']) ?></span>
                                </div>
                            </td>
                            <td><span class="cat-tag"><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?></span></td>
                            <td>
                                <span class="badge-estado <?= strtolower($item['estado']) ?>">
                                    <?= ucfirst($item['estado']) ?>
                                </span>
                            </td>
                            <td class="fecha-col"><?= date('d M, Y', strtotime($item['fecha_creacion'])) ?></td>
                            <td>
                                <!-- Botón de eliminar con onclick hacia el modal -->
                                <button class="btn-danger" onclick="abrirModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['titulo'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
    <div id="modal-confirmacion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h2 class="modal-title">¿Eliminar publicación?</h2>
            <p class="modal-text" id="modal-mensaje">¿Estás seguro de que quieres eliminar esta publicación?</p>
            
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-eliminar">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <!-- SCRIPT DEL MODAL -->
    <script>
        let publicacionIdSeleccionada = null;
        const modal = document.getElementById('modal-confirmacion');
        const mensajeTexto = document.getElementById('modal-mensaje');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');

        // Función para abrir el modal
        function abrirModal(id, titulo) {
            publicacionIdSeleccionada = id;
            // Personalizamos el mensaje dinámicamente con el título
            mensajeTexto.innerHTML = `¿Estás seguro de que quieres eliminar la publicación <strong>"${titulo}"</strong>? Esta acción no se puede deshacer.`;
            modal.classList.add('active');
        }

        // Función para cerrar el modal
        function cerrarModal() {
            modal.classList.remove('active');
            publicacionIdSeleccionada = null;
        }

        // Ejecutar eliminación
        btnConfirmar.addEventListener('click', function() {
            if (publicacionIdSeleccionada) {
                window.location.href = `publicaciones.php?eliminar=${publicacionIdSeleccionada}`;
            }
        });

        // Cerrar si hace clic fuera del cuadro blanco
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModal();
            }
        });
    </script>
</body>
</html>