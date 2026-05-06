<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="navbar">
    <div class="logo">
        <img src="../image/LogotipoSinfondo.png" alt="Logo ODS7" class="img-logo">
        <h2>Red-novable</h2>
    </div>
    
    <nav>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-btn-eco"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="categorias.php" class="nav-btn-eco"><i class="fas fa-th-large"></i> Categorías</a></li>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <?php 
                $puede_crear = in_array($_SESSION['rol_id'] ?? 0, [1, 2, 3]);
                ?>
                <?php if ($puede_crear): ?>
                    <li><a href="../admin/crear_publicacion.php" class="nav-btn-eco"><i class="fas fa-edit"></i> Crear</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="nav-auth">
        <?php if (!isset($_SESSION['usuario_id'])): ?>
            <!-- Botones visibles para invitados -->
            <a href="inicioSesion.php" class="nav-btn-eco"><i class="fas fa-sign-in-alt"></i> Entrar</a>
            <a href="registro.php" class="nav-btn-eco primary"><i class="fas fa-user-plus"></i> Registro</a>
        <?php endif; ?>

        <!-- EL MENÚ DE RAYITAS (Ahora va al final para todos) -->
        <div class="menu-container">
            <div class="menu-icon nav-btn-eco" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
            
            <div id="userMenu" class="menu-dropdown">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <div class="menu-header">
                        Conectado como <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></strong>
                    </div>
                    
                    <a href="perfil.php" class="menu-item">
                        <i class="fas fa-user-circle"></i> Mi Perfil
                    </a>
                    
                    <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1): ?>
                        <a href="../admin/dashboard.php" class="menu-item">
                            <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="menu-header">
                        Configuración
                    </div>
                <?php endif; ?>

                <!-- Toggle de Modo Oscuro (Visible para todos) -->
                <div class="menu-item theme-toggle-container" onclick="toggleDarkMode(event)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-moon"></i> Modo Oscuro
                    </div>
                    <div class="toggle-switch">
                        <label class="switch-label">
                            <input type="checkbox" class="checkbox" id="theme-checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="logout.php" class="menu-item logout">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleMenu() {
        var menu = document.getElementById('userMenu');
        if(menu) menu.classList.toggle('show');
    }
    
    window.onclick = function(event) {
        if (!event.target.closest('.menu-container')) {
            var menu = document.getElementById('userMenu');
            if (menu && menu.classList.contains('show')) {
                menu.classList.remove('show');
            }
        }
    }

    // --- Lógica del Modo Oscuro Unificada ---
    const themeCheckbox = document.getElementById('theme-checkbox');
    const body = document.body;

    function aplicarTema() {
        if (themeCheckbox.checked) {
            body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
        }
    }

    // Cargar preferencia al inicio
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        if(themeCheckbox) themeCheckbox.checked = true;
    }

    // Evento manual del switch
    if(themeCheckbox) {
        themeCheckbox.addEventListener('change', aplicarTema);
    }

    // Permitir clic en el contenedor del menú para activar el switch
    function toggleDarkMode(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SPAN') return;
        if(themeCheckbox) {
            themeCheckbox.checked = !themeCheckbox.checked;
            aplicarTema();
        }
    }
</script>