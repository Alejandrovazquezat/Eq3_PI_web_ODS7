document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("mascota-container");
    const globo = document.getElementById("mascota-globo");
    const mensajeElem = document.getElementById("mascota-mensaje");
    const bot = document.getElementById("mascota-css");

    if (!container || !bot) return;

    let enLadoIzquierdo = true;
    let clickCount = 0;
    let moveMode = 0; 
    const phpError = container.getAttribute("data-php-mensaje");
    const currentPath = window.location.pathname;

    const ecoTipsOriginales = [
        "¿Sabías que el sol envía más energía a la Tierra en una hora de la que usamos en un año?",
        "Apagar las luces reduce drásticamente tu huella de carbono.",
        "El ODS 7 busca energía limpia para todos. ¡Gracias por estar aquí!",
        "Las luces LED consumen hasta un 80% menos energía que las normales.",
        "¡Pequeños cambios generan grandes impactos!",
        "La energía eólica es una de las fuentes renovables de más rápido crecimiento.",
        "Desconectar los aparatos que no usas evita el molesto 'consumo vampiro'.",
        "La energía hidroeléctrica es la fuente renovable más utilizada a nivel global.",
        "Aprovechar la luz natural durante el día es la mejor forma de ahorrar electricidad.",
        "¡Ojo! Los paneles solares pueden durar 25 años o más con un buen mantenimiento."
    ];

    let tipsDisponibles = [...ecoTipsOriginales];

    function obtenerTipSinRepetir() {
        if (tipsDisponibles.length === 0) {
            tipsDisponibles = [...ecoTipsOriginales];
        }
        const indice = Math.floor(Math.random() * tipsDisponibles.length);
        return tipsDisponibles.splice(indice, 1)[0];
    }

    function hablar(mensaje, duracion = 4000, esError = false, cambiarAlTerminar = false) {
        mensajeElem.textContent = mensaje;
        globo.style.display = "block";
        bot.classList.add("hablando");
        
        if (esError) {
            globo.style.borderColor = "#ff4757";
            container.classList.add("shake-anim");
        } else {
            globo.style.borderColor = "#000000";
            container.classList.remove("shake-anim");
        }

        setTimeout(() => {
            globo.style.display = "none";
            container.classList.remove("shake-anim");
            bot.classList.remove("hablando");
            if (cambiarAlTerminar) animarCambio();
        }, duracion);
    }

    function animarCambio() {
        container.classList.remove("mascota-visible");

        if (moveMode < 2) {
            container.classList.add("mascota-oculto"); 
        } else {
            container.classList.add("mascota-oculto-v"); 
        }

        setTimeout(() => {
            enLadoIzquierdo = !enLadoIzquierdo;
            moveMode = (moveMode + 1) % 4; 

            container.classList.remove("lado-izquierdo", "lado-derecho");
            container.classList.add(enLadoIzquierdo ? "lado-izquierdo" : "lado-derecho");
            
            if (enLadoIzquierdo) bot.classList.remove("espejo");
            else bot.classList.add("espejo");

            setTimeout(() => {
                container.classList.remove("mascota-oculto", "mascota-oculto-v");
                container.classList.add("mascota-visible");
            }, 100);

        }, 1000); 
    }

    // --- LÓGICA DE INICIO Y MENSAJES CONTEXTUALES ---
    setTimeout(() => {
        container.classList.add("mascota-visible");
        
        if (!sessionStorage.getItem("franxx_intro")) {
            setTimeout(() => {
                hablar("¡Hola! Soy Franxx, tu asistente de Red-novable.", 3500);
                setTimeout(() => {
                    hablar("¡Bienvenido! Únete para salvar el planeta.", 4000);
                }, 4000);
            }, 1000);
            sessionStorage.setItem("franxx_intro", "true");
            
        } else if (phpError && phpError.trim() !== "") {
            setTimeout(() => hablar(phpError, 6000, true), 1000);
            
        } else {
            setTimeout(() => {
                if (currentPath.includes("crear_publicacion.php")) {
                    hablar("¡Genial! Comparte tu información sobre energías renovables.", 4000);
                } else if (currentPath.includes("categorias.php")) {
                    hablar("Explora los temas. ¡Hay mucho que aprender sobre las energías renovables!", 4000);
                } else if (currentPath.includes("perfil.php")) {
                    hablar("Este es tu espacio. ¡Mantén tu info actualizada!", 4000);
                } else if (currentPath.includes("dashboard.php")) {
                    hablar("¡Bienvenido al dashboard! Aquí puedes gestionar la plataforma.", 4000);
                } else if (currentPath.includes("categoria.php")) {
                    hablar("¡Explora las publicaciones de esta categoría y aprende más!", 4000);
                } else if (currentPath.includes("editor.php") || currentPath.includes("panel_editor.php")) {
                    hablar("Tómate tu tiempo para redactar. ¡La información es poder!", 4000);
                }
            }, 1000);
        }
    }, 500);

    // --- INTERACCIÓN Y CONTADOR ---
    bot.addEventListener("click", () => {
        clickCount++;
        const tip = obtenerTipSinRepetir(); 
        const debeMoverse = (clickCount % 3 === 0);
        hablar(tip, 4000, false, debeMoverse);
    });

    // --- ARRASTRAR (DRAG & DROP) ---
    let isDragging = false;
    let startX, startY;
    let currentX = 0, currentY = 0;

    bot.addEventListener("mousedown", (e) => {
        isDragging = true;
        container.classList.add("dragging");
        startX = e.clientX - currentX;
        startY = e.clientY - currentY;
    });

    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        e.preventDefault(); 
        currentX = e.clientX - startX;
        currentY = e.clientY - startY;
        container.style.transform = `translate(${currentX}px, ${currentY}px)`;
    });

    document.addEventListener("mouseup", () => {
        if (!isDragging) return;
        isDragging = false;
        container.classList.remove("dragging");
        container.style.transform = "";
        currentX = 0;
        currentY = 0;
    });
    // ... (dentro de tu DOMContentLoaded)

    function hablar(mensaje, duracion = 5000, esError = false, cambiarAlTerminar = false) {
        mensajeElem.textContent = mensaje;
        globo.style.display = "block";
        bot.classList.add("hablando");
        
        if (esError) {
            globo.style.borderColor = "#ff4757";
            container.classList.add("shake-anim");
            bot.classList.add("modo-alerta"); // ACTIVA LA LUZ ROJA
        } else {
            globo.style.borderColor = "#000000";
            container.classList.remove("shake-anim");
            bot.classList.remove("modo-alerta");
        }

        setTimeout(() => {
            globo.style.display = "none";
            container.classList.remove("shake-anim");
            bot.classList.remove("hablando");
            // Quitamos la alerta después de hablar
            if (esError) bot.classList.remove("modo-alerta");
            
            if (cambiarAlTerminar) animarCambio();
        }, duracion);
    }

    // --- LÓGICA ESPECÍFICA PARA LOGIN Y REGISTRO ---
    setTimeout(() => {
        container.classList.add("mascota-visible");

        if (currentPath.includes("inicioSesion.php")) {
            if (phpError && phpError.trim() !== "") {
                hablar("Ten cuidado al ingresar los datos, algo no cuadra.", 5000, true);
            } else {
                hablar("¡Hola! Ingresa tus datos en las casillas correspondientes.", 5000);
            }
        } else if (currentPath.includes("registro.php")) {
            if (phpError && phpError.trim() !== "") {
                hablar("Revisa bien los campos, detecto un error en el registro.", 5000, true);
            } else {
                hablar("¿Nuevo en Red-novable? ¡Regístrate!", 5000);
            }
        } else {
            // ... (Tus otros mensajes de dashboard e intro aquí)
        }
    }, 800);
});