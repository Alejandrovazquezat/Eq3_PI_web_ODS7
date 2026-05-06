<?php
session_start();

// Validar que el usuario sea administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) { 
    header("Location: ../pages/index.php");
    exit;
}

require_once '../../config/Conexion.php';
$db = (new Conexion())->getConexion();

// LÓGICA PARA ELIMINAR UN COMENTARIO
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt = $db->prepare("DELETE FROM comentarios WHERE id = ?");
    $stmt->execute([$id_eliminar]);
    header("Location: comentarios.php?msg=eliminado");
    exit;
}

// OBTENER TODOS LOS COMENTARIOS CON DATOS DEL USUARIO Y PUBLICACIÓN
$query = "SELECT c.id, c.contenido, c.fecha_creacion, 
                 u.nombre AS autor_nombre, 
                 p.titulo AS publicacion_titulo 
          FROM comentarios c 
          LEFT JOIN usuarios u ON c.usuario_id = u.id 
          LEFT JOIN publicaciones p ON c.publicacion_id = p.id 
          ORDER BY c.fecha_creacion DESC";

$comentarios = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Comentarios - Red-novable Admin</title>
    
    <!-- Hojas de estilo y fuentes -->
    <link rel="stylesheet" href="../css_dash/style.css"> 
    <link rel="stylesheet" href="../css_dash/comentarios_styles.css"> <!-- Aquí vinculamos el nuevo CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f5f9; display: flex;">

    <!-- INICIO SIDEBAR -->
    <nav class="sidebar">
        <div class="logo-box" onclick="window.location.href='../../frontend/pages/index.php'">
            <img src="../image/LogotipoSinfondo.png" alt="Logo">
            <div class="logo-name">Red-novable</div>
        </div>
        <div class="menu-groups">
            <a href="dashboard.php" class="nav-link">📊 Dashboard general</a>
            <a href="publicaciones.php" class="nav-link">📝 Publicaciones</a>
            <a href="revisar.php" class="nav-link">✅ Pendientes de revisión</a>
            <a href="usuarios.php" class="nav-link">👥 Usuarios</a>
            <a href="comentarios.php" class="nav-link active">💬 Comentarios</a>
            <a href="crear_publicacion.php" class="nav-link btn-special">+ Nueva publicación</a>
        </div>
    </nav>
    <!-- FIN SIDEBAR -->

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <div class="container">
            <div class="header-admin">
                <h1 style="color: #1e293b;"><i class="fas fa-comments"></i> Gestión de Comentarios</h1>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado'): ?>
                <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> Comentario eliminado exitosamente.
                </div>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Publicación</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($comentarios) > 0): ?>
                        <?php foreach($comentarios as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><i class="fas fa-user" style="color: #94a3b8;"></i> <strong><?= htmlspecialchars($c['autor_nombre'] ?? 'Usuario Eliminado') ?></strong></td>
                                <td style="color: #3b82f6;"><?= htmlspecialchars($c['publicacion_titulo'] ?? 'Publicación Eliminada') ?></td>
                                <td style="max-width: 300px;"><?= htmlspecialchars($c['contenido']) ?></td>
                                <td style="color: #64748b;"><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])) ?></td>
                                <td>
                                    <!-- Botón que dispara el modal -->
                                    <button class="btn-danger" onclick="abrirModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['autor_nombre'] ?? 'Usuario', ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">
                                <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Aún no hay comentarios registrados en la plataforma.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- MODAL DE CONFIRMACIÓN -->
    <div id="modal-confirmacion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-container">
                <i class="fas fa-comment-slash"></i>
            </div>
            <h2 class="modal-title">¿Eliminar comentario?</h2>
            <p class="modal-text" id="modal-mensaje">¿Estás seguro de que quieres eliminar el comentario de <strong>Usuario</strong>?</p>
            
            <div class="modal-buttons">
                <button class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-modal-confirm" id="btn-confirmar-eliminar">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <!-- SCRIPT PARA CONTROLAR EL MODAL -->
    <script>
        let comentarioIdSeleccionado = null;
        const modal = document.getElementById('modal-confirmacion');
        const mensajeTexto = document.getElementById('modal-mensaje');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');

        // Función para abrir el modal
        function abrirModal(id, autor) {
            comentarioIdSeleccionado = id;
            mensajeTexto.innerHTML = `¿Estás seguro de que quieres eliminar el comentario de <strong>${autor}</strong>?`;
            modal.classList.add('active');
        }

        // Función para cerrar el modal
        function cerrarModal() {
            modal.classList.remove('active');
            comentarioIdSeleccionado = null;
        }

        // Evento al dar clic en "Sí, eliminar"
        btnConfirmar.addEventListener('click', function() {
            if (comentarioIdSeleccionado) {
                window.location.href = `comentarios.php?eliminar=${comentarioIdSeleccionado}`;
            }
        });

        // Cerrar el modal si se hace clic afuera de la caja blanca
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModal();
            }
        });
    </script>

</body>
</html>