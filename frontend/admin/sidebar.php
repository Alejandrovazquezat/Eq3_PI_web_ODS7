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

    <div class="franxx-sidebar-container">
        <div id="franxx-sidebar-globo">
            <p id="franxx-sidebar-mensaje"></p>
        </div>

        <div id="franxx-sidebar-bot">
            <div class="eco-antena-bolita"></div>
            <div class="eco-antena"></div>
            <div class="eco-cabeza">
                <div class="eco-ojo"></div>
                <div class="eco-ojo"></div>
            </div>
            
            <div class="eco-cuerpo-contenedor">
                <div class="eco-brazo-f eco-brazo-f-izq"></div>
                <div class="eco-cuerpo">
                    <div class="eco-pantalla">⚡</div>
                </div>
                <div class="eco-brazo-f eco-brazo-f-der"></div>
            </div>
            
            <div class="eco-llanta"></div>
        </div>
    </div>

    <script>
        // Sincronizar el modo oscuro
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        }

        document.addEventListener("DOMContentLoaded", () => {
            const bot = document.getElementById("franxx-sidebar-bot");
            const globo = document.getElementById("franxx-sidebar-globo");
            const mensajeElem = document.getElementById("franxx-sidebar-mensaje");
            const currentPage = "<?= $current_page ?>";

            const dashTips = [
                "Revisa las publicaciones pendientes para mantener el contenido fresco.",
                "Un buen administrador siempre revisa los reportes de usuarios.",
                "Esa interfaz neubrutalista está quedando muy limpia, ¡buen trabajo!",
                "Si borras un usuario, sus comentarios y likes también se irán.",
                "Mantén el ODS 7 como prioridad al aprobar el contenido."
            ];

            function hablar(mensaje, duracion = 4500) {
                mensajeElem.textContent = mensaje;
                globo.style.display = "block";
                bot.classList.add("hablando");
                
                setTimeout(() => {
                    globo.style.display = "none";
                    bot.classList.remove("hablando");
                }, duracion);
            }

            // Mensajes por página
            setTimeout(() => {
                if (currentPage === 'dashboard.php') hablar("Panel general listo. ¡Sistemas operando al 100%!");
                else if (currentPage === 'publicaciones.php') hablar("Aquí gestionamos el conocimiento. ¡Cuidado al borrar!");
                else if (currentPage === 'revisar.php') hablar("Hay aportes nuevos. ¿Aprobamos o rechazamos?");
                else if (currentPage === 'usuarios.php') hablar("Gestión de accesos. Un gran poder conlleva responsabilidad.");
                else if (currentPage === 'comentarios.php') hablar("Moderando la comunidad para un ambiente sano.");
            }, 800);

            bot.addEventListener("click", () => {
                const tip = dashTips[Math.floor(Math.random() * dashTips.length)];
                hablar(tip);
            });
        });
    </script>

    <style>
        .franxx-sidebar-container {
            margin-top: auto; 
            padding-bottom: 30px;
            display: flex;
            justify-content: center;
            position: relative;
        }

        #franxx-sidebar-bot {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transform: scale(0.85);
            /* Blindaje total contra el CSS del Dashboard */
            box-sizing: content-box !important;
        }

        #franxx-sidebar-bot div {
            box-sizing: content-box !important;
        }

        #franxx-sidebar-globo {
            position: absolute;
            left: 75%; 
            top: -10px;
            background-color: var(--bg-color, #ffffff);
            color: var(--text-color, #000000);
            padding: 12px 18px;
            border: 3px solid #000000;
            box-shadow: 4px 4px 0px #000000;
            border-radius: 16px 16px 16px 0px; 
            width: 220px;
            font-weight: bold;
            font-size: 13px;
            display: none;
            z-index: 1000;
        }

        /* --- PIEZAS --- */
        .eco-antena-bolita { width: 14px; height: 14px; background-color: #f1c40f; border: 3px solid #000; border-radius: 50%; margin-bottom: -3px; animation: brillar 1.5s infinite alternate; }
        .eco-antena { width: 4px; height: 12px; background-color: #000; }
        .eco-cabeza { width: 55px; height: 40px; background-color: #ffffff; border: 3px solid #000; border-radius: 12px; box-shadow: 4px 4px 0px #000; display: flex; justify-content: center; align-items: center; gap: 8px; z-index: 3; position: relative; }
        .eco-ojo { width: 10px; height: 14px; background-color: #000; border-radius: 50%; animation: parpadear 4s infinite; }
        
        .eco-cuerpo-contenedor { width: 45px; height: 35px; position: relative; margin-top: -3px; z-index: 2; }
        .eco-cuerpo { width: 100%; height: 100%; background-color: #2ecc71; border: 3px solid #000; border-radius: 8px; box-shadow: 4px 4px 0px #000; display: flex; justify-content: center; align-items: center; position: relative; z-index: 3; }
        .eco-pantalla { color: #fff; font-weight: bold; font-size: 18px; text-shadow: 1px 1px 0px #000; }
        
        /* --- BRAZOS CORREGIDOS DE LA SOMBRA --- */
        .eco-brazo-f { 
            width: 10px; height: 8px; background-color: #ffffff; 
            border: 3px solid #000; border-radius: 4px; 
            position: absolute; top: 12px; z-index: 1; 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        }
        
        .eco-brazo-f-izq { 
            left: -10px; /* Asoma por la izquierda */
            transform-origin: right center; 
            transform: rotate(-40deg); 
        }
        
        .eco-brazo-f-der { 
            left: 45px; /* Nace justo donde acaba el cuerpo y la sombra */
            transform-origin: left center; 
            transform: rotate(40deg); 
        }
        
        /* Apuntar */
        #franxx-sidebar-bot.hablando .eco-brazo-f-der { 
            transform: rotate(-35deg); 
            width: 24px; 
            /* Se mantiene el left en 45px para que el hombro no se despegue */
            z-index: 4; 
        }
        
        .eco-llanta { width: 32px; height: 10px; background-color: #555; border: 3px solid #000; border-radius: 6px; margin-top: -2px; box-shadow: 2px 2px 0px #000; z-index: 1; position: relative; }

        @keyframes parpadear { 0%, 95%, 98%, 100% { transform: scaleY(1); } 96%, 99% { transform: scaleY(0.1); } }
        @keyframes brillar { from { box-shadow: 0 0 2px #f1c40f, 2px 2px 0px #000; } to { box-shadow: 0 0 10px #f1c40f, 2px 2px 0px #000; } }
    </style>
</nav>