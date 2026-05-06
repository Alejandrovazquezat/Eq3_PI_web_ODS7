<?php
// Obtenemos el nombre del archivo actual para marcarlo como 'active'
$current_page = basename($_SERVER['PHP_SELF']);
$rol = $_SESSION['rol_id'] ?? 0;
?>
<nav class="sidebar">
    <div class="logo-box" onclick="window.location.href='../pages/index.php'">
        <img src="../image/LogotipoSinfondo.png" alt="Logo">
        <div class="logo-name">RED-novable</div>
    </div>
    <div class="menu-groups">
        
        <?php if ($rol == 1): /* == ADMINISTRADOR == */ ?>
            <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard general</a>
            <a href="publicaciones.php" class="nav-link <?= $current_page == 'publicaciones.php' ? 'active' : '' ?>">📝 Publicaciones</a>
            <a href="revisar.php" class="nav-link <?= $current_page == 'revisar.php' ? 'active' : '' ?>">✅ Pendientes de revisión</a>
            <a href="usuarios.php" class="nav-link <?= $current_page == 'usuarios.php' ? 'active' : '' ?>">👥 Usuarios</a>
            <a href="comentarios.php" class="nav-link <?= $current_page == 'comentarios.php' ? 'active' : '' ?>">💬 Comentarios</a>
            
        <?php elseif ($rol == 2): /* == EDITOR == */ ?>
            <a href="publicaciones.php" class="nav-link <?= $current_page == 'publicaciones.php' ? 'active' : '' ?>">📝 Publicaciones</a>
            <a href="revisar.php" class="nav-link <?= $current_page == 'revisar.php' ? 'active' : '' ?>">✅ Pendientes de revisión</a>
        <?php endif; ?>
        
    </div>
    <script>
        // Sincronizar el modo oscuro con el resto de la plataforma
        if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        }
    </script>
</nav>