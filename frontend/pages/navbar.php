<?php
// Arranca la sesión de PHP si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="navbar">
    <div class="logo">
        <img src="../images/logo/logo.png" alt="Logo ODS7" class="img-logo">
        <h2>Plataforma <span class="color-primario">ODS7</span></h2>
    </div>
    
    <nav>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="#"><i class="fas fa-th-large"></i> Categorías</a></li>

            <?php if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true): ?>
                <li><a href="publicar.php"><i class="fas fa-edit"></i> Crear Publicación</a></li>
                
                <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true): ?>
                    <li><a href="admin.php"><i class="fas fa-cog"></i> Panel Admin</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="nav-auth">
        <?php if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true): ?>
            <span style="font-weight: bold; margin-right: 15px;">Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
            <a href="logout.php" class="btn-login" style="color: #ef4444; border-color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
            <a href="registro.php" class="btn-registro"><i class="fas fa-user-plus"></i> Registrarse</a>
        <?php endif; ?>
    </div>
</header>