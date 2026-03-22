<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="navbar">
    <div class="logo">
        <img src="../image/LogotipoSinfondo.png" alt="Logo ODS7" class="img-logo">
        <h2>Redrenovable</h2>
    </div>
    
    <nav>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="categorias.php"><i class="fas fa-th-large"></i> Categorías</a></li>
            
            <?php if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true): ?>
                <?php 
                $puede_crear = in_array($_SESSION['rol_id'] ?? 0, [1, 2, 3]);
                ?>
                <?php if ($puede_crear): ?>
                    <li><a href="publicar.php"><i class="fas fa-edit"></i> Crear Publicación</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="nav-auth">
        <?php if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true): ?>
            <div class="menu-container">
                <div class="menu-icon" onclick="toggleMenu()">
                    <i class="fas fa-bars"></i>
                </div>
                <div id="userMenu" class="menu-dropdown" style="display: none;">
                    <div class="menu-user-info">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>
                    </div>
                    <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1): ?>
                        <a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
        <?php else: ?>
            <a href="inicioSesion.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
            <a href="registro.php" class="btn-registro"><i class="fas fa-user-plus"></i> Registrarse</a>
        <?php endif; ?>
    </div>
</header>

<script>
    function toggleMenu() {
        var menu = document.getElementById('userMenu');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
        } else {
            menu.style.display = 'none';
        }
    }
    
    window.onclick = function(event) {
        if (!event.target.closest('.menu-container')) {
            var menu = document.getElementById('userMenu');
            if (menu) {
                menu.style.display = 'none';
            }
        }
    }
</script>