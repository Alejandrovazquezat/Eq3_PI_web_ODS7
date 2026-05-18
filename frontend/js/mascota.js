// frontend/js/mascota.js
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("mascota-container");
    const globo = document.getElementById("mascota-globo");
    const mensajeElem = document.getElementById("mascota-mensaje");
    const franxxImg = document.getElementById("franxx-img");

    if (!container || !franxxImg) return;

    let enLadoIzquierdo = true;
    let clickCount = 0;
    let moveMode = 0; 
    let estaHablando = false;
    let isDragging = false;
    let startX = 0, startY = 0, currentX = 0, currentY = 0;
    let idleTimer = null;

    const uriBase = "../image/franxx_base.png";
    const uriHablando = "../image/franxx_hablando.png";
    const uriBaseClose = "../image/franxx_base_closeeye.png";
    const uriHablandoClose = "../image/franxx_hablando_closeeye.png";

    // PRECARGA DE IMÁGENES
    const p1 = new Image(); p1.src = uriBaseClose;
    const p2 = new Image(); p2.src = uriHablandoClose;
    const p3 = new Image(); p3.src = uriHablando;

    franxxImg.src = uriBase;

    // Obtención de mensajes desde PHP
    const phpMensaje = container.getAttribute("data-php-mensaje") || "";
    const phpTipo = container.getAttribute("data-php-tipo") || ""; 
    const currentPath = window.location.pathname.toLowerCase();
    const isAuthPage = currentPath.includes("iniciosesion") || currentPath.includes("registro");

    // BASE DE DATOS - ODS 7 (Se quitaron los tecnicismos de programación y se agregaron datos generales)
    const ecoTipsOriginales = [
        "El Sol envía a la Tierra en una sola hora más energía de la que la humanidad entera consume en un año.",
        "La energía geotérmica aprovecha el calor del subsuelo terrestre, logrando un suministro ininterrumpido 24/7.",
        "Islandia genera casi el 100% de su electricidad a partir de fuentes renovables, principalmente geotérmica.",
        "Los paneles solares no necesitan calor, operan transformando la radiación lumínica en electricidad.",
        "La biomasa procesa residuos forestales y agrícolas, convirtiendo desechos en energía circular.",
        "Una turbina eólica marina genera hasta el doble de energía que una terrestre por la constancia del viento.",
        "El hidrógeno verde se obtiene mediante electrólisis con energía limpia.",
        "El ODS 7 busca garantizar el acceso a una energía asequible, segura y sostenible para todos en 2030.",
        "La energía mareomotriz es altamente predecible gracias a la gravedad lunar.",
        "La arquitectura bioclimática reduce el consumo de los edificios en un 60% al orientar bien las ventanas.",
        "Las baterías de estado sólido prometen revolucionar el almacenamiento renovable.",
        "Sustituir la iluminación por tecnología LED industrial reduce el consumo eléctrico hasta en un 80%.",
        "El uso de bicicletas eléctricas en la ciudad reduce drásticamente la contaminación del aire.",
        "Dinamarca y Costa Rica son pioneros en la integración de redes eléctricas inteligentes y eólicas.",
        "El silicio utilizado en celdas solares es el segundo elemento más abundante en la corteza terrestre.",
        "Las redes inteligentes permiten que los hogares con paneles solares compartan su excedente de energía.",
        "El reciclaje de aluminio requiere un 95% menos de energía que extraerlo desde cero.",
        "Las pequeñas turbinas en ríos pueden dar electricidad a zonas rurales sin alterar el ecosistema.",
        "Eficiencia energética es optimizar los recursos que tenemos en casa para hacer más con menos.",
        "Costa Rica mantuvo su red eléctrica funcionando solo con renovables durante 300 días seguidos.",
        "Los aerogeneradores modernos superan los 200 metros de altura para captar vientos más fuertes.",
        "Aprovechar la luz natural durante el día es el paso más sencillo para cuidar la energía.",
        "La energía termosolar usa espejos para fundir sales que almacenan calor para generar energía de noche.",
        "Desconectar los aparatos eléctricos que no usas evita el consumo de energía 'vampiro'.",
        "La fusión nuclear experimental promete energía limpia casi ilimitada replicando las estrellas.",
        "Un metro cuadrado de bosque de algas marinas ayuda a limpiar los océanos de manera natural.",
        "El transporte público eléctrico es una de las mejores alternativas para reducir emisiones en las ciudades.",
        "Los inversores solares transforman la energía del sol en corriente que puedes usar en tus enchufes.",
        "El biogás de vertederos evita que el metano dañe la capa de ozono.",
        "La energía undimotriz aprovecha el movimiento constante de las olas del mar para generar luz.",
        "La movilidad eléctrica reduce emisiones de CO2 y elimina la contaminación acústica urbana.",
        "Apagar los equipos en la oficina o escuela ahorra muchísima energía a nivel mundial.",
        "Las turbinas bajo el agua funcionan de manera silenciosa para no molestar a los peces.",
        "Transmitir energía limpia requiere cables especiales para que no se pierda en el camino.",
        "Pintar los techos de colores claros ayuda a mantener las casas frescas sin usar ventiladores.",
        "Las granjas solares flotantes reducen la evaporación del agua y se refrigeran naturalmente.",
        "Los paneles solares al final de su vida útil se pueden reciclar para crear nuevas tecnologías.",
        "Tener un buen aislamiento térmico en el hogar reduce la necesidad de usar calefacción.",
        "El calor interno de la tierra se puede usar para mantener la temperatura de grandes edificios.",
        "Ajustar el termostato un par de grados puede hacer una gran diferencia en el recibo de luz.",
        "Utilizar electrodomésticos con sello de bajo consumo ayuda de forma directa al planeta.",
        "Las ventanas modernas pueden diseñarse para dejar pasar la luz pero bloquear el calor del verano.",
        "La educación sobre el medio ambiente es la herramienta más fuerte para el cambio climático.",
        "La energía azul aprovecha la mezcla de agua dulce y salada en la desembocadura de los ríos.",
        "Se está investigando cómo generar energía en las banquetas simplemente con los pasos de la gente.",
        "Los autos eléctricos del futuro podrán prestar energía a las casas durante un apagón.",
        "Reducir las emisiones de carbono es vital para proteger los ecosistemas que nos rodean.",
        "La tecnología avanza muy rápido para encontrar formas de almacenar energía renovable a bajo costo.",
        "Algunas algas especiales se estudian para crear combustibles amigables con la naturaleza.",
        "Conocer de dónde viene la energía que usamos nos hace consumidores más responsables."
    ];

    let tipsDisponibles = [...ecoTipsOriginales];
    function obtenerTipSinRepetir() {
        if (tipsDisponibles.length === 0) tipsDisponibles = [...ecoTipsOriginales];
        return tipsDisponibles.splice(Math.floor(Math.random() * tipsDisponibles.length), 1)[0];
    }

    // --- PARPADEO NORMAL ---
    setInterval(() => {
        if (isDragging) return;
        
        if (estaHablando) {
            franxxImg.src = uriHablandoClose;
            setTimeout(() => { if(estaHablando) franxxImg.src = uriHablando; }, 300);
        } else {
            franxxImg.src = uriBaseClose;
            setTimeout(() => { if(!estaHablando) franxxImg.src = uriBase; }, 300);
        }
    }, 4500);

    // --- TEMPORIZADOR DE INACTIVIDAD ---
    function resetearInactividad() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            if (!isAuthPage && !estaHablando && !isDragging) {
                animarCambio(); 
                resetearInactividad(); 
            }
        }, 210000); 
    }
    resetearInactividad();

    // --- SINTETIZADOR ESTILO ANIMAL CROSSING ---
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function sonarPalabra() {
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.type = 'square'; 
        
        // Frecuencia aleatoria para el tono característico
        const frecuenciaAleatoria = 500 + Math.random() * 150;
        osc.frequency.setValueAtTime(frecuenciaAleatoria, audioCtx.currentTime);

        // Subida y bajada de volumen
        gain.gain.setValueAtTime(0.0, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0.08, audioCtx.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.1);

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.start();
        osc.stop(audioCtx.currentTime + 0.1);
    }

    function emitirSonidoTexto(palabras, velocidadPalabra) {
        let i = 0;
        const intervalo = setInterval(() => {
            if (i < palabras.length && estaHablando) {
                sonarPalabra();
                i++;
            } else {
                clearInterval(intervalo);
                if (estaHablando) {
                    franxxImg.src = uriBase;
                }
            }
        }, velocidadPalabra);
    }

    // --- FUNCIÓN HABLAR CON CÁLCULO DE TIEMPO DINÁMICO ---
    function hablar(mensaje, esError = false, accionAlTerminar = "nada") {
        estaHablando = true;
        resetearInactividad(); 
        
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        mensajeElem.textContent = mensaje;
        globo.style.display = "block";
        franxxImg.src = uriHablando;
        
        const palabras = mensaje.trim().split(/\s+/);
        const velocidadPalabra = 110; 
        emitirSonidoTexto(palabras, velocidadPalabra);
        
        // CORRECCIÓN DEL PARPADEO ROJO: Solo si es error
        if (esError) {
            globo.style.borderColor = "#ff4757";
            container.classList.add("shake-anim", "modo-alerta"); 
        } else {
            globo.style.borderColor = "#000000";
            container.classList.remove("shake-anim", "modo-alerta");
        }

        const tiempoHablando = palabras.length * velocidadPalabra;
        const tiempoParaLeer = 2000; 
        const duracionTotalGlobo = tiempoHablando + tiempoParaLeer;

        setTimeout(() => {
            globo.style.display = "none";
            container.classList.remove("shake-anim", "modo-alerta");
            franxxImg.src = uriBase;

            if (accionAlTerminar === "cambiar") {
                animarCambio();
                setTimeout(() => { estaHablando = false; }, 1200);
            } else if (accionAlTerminar === "irse") {
                container.classList.remove("mascota-visible");
                container.classList.add("mascota-oculto-v"); 
                estaHablando = false;
            } else {
                estaHablando = false;
            }
        }, duracionTotalGlobo);
    }

    function animarCambio() {
        container.classList.remove("mascota-visible");
        if (moveMode < 2) container.classList.add("mascota-oculto"); 
        else container.classList.add("mascota-oculto-v"); 

        setTimeout(() => {
            enLadoIzquierdo = !enLadoIzquierdo;
            moveMode = (moveMode + 1) % 4; 

            container.classList.remove("lado-izquierdo", "lado-derecho");
            container.classList.add(enLadoIzquierdo ? "lado-izquierdo" : "lado-derecho");
            
            if (enLadoIzquierdo) franxxImg.classList.remove("espejo");
            else franxxImg.classList.add("espejo");

            setTimeout(() => {
                container.classList.remove("mascota-oculto", "mascota-oculto-v");
                container.classList.add("mascota-visible");
            }, 100);
        }, 1000); 
    }

    // --- ARREGLOS DE FRASES NEUTRAS POR PÁGINA ---
    const saludosRegistro = [
        "¡Red-novable te da la bienvenida! Únete para aprender juntos sobre energías limpias.",
        "Qué gusto tenerte por aquí. ¡Crea una cuenta para ser parte de la comunidad!"
    ];

    const saludosIndex = [
        "¡Hola, soy Franxx! Red-novable te da la bienvenida.",
        "¡Hola, soy Franxx!, explora las publicaciones para aprender sobre energías limpias.",
        "¡Nos da gusto tenerte en Red-novable! Soy Franxx, tu acompañante en Red-novable."
    ];

    const saludosCrear = [
        "¡Qué gran idea! Comparte tus conocimientos con toda la comunidad.",
        "Tu aporte es muy valioso para inspirar a otras personas.",
        "Comparte tus conocimientos sobre energías renovables y ayuda a que más personas aprendan.",
        "¡Crear contenido de calidad es la mejor forma de contribuir al ODS 7!"
    ];

    const saludosCategorias = [
        "Encuentra el tema que más te apasione sobre energía renovable.",
        "Cada categoría tiene información increíble lista para ser descubierta.",
        "Navega entre los temas para aprender datos fascinantes del ODS 7."
    ];

    const saludosPerfil = [
        "Desde aquí es posible administrar la información personal.",
        "Este es tu espacio personal. ¡Mantenlo al día y comparte más ideas!",
        "Revisa las interacciones y el historial de tus publicaciones.",
        "¡Personaliza tu perfil para que otros usuarios te conozcan mejor!"
    ];

    const saludosPublicacion = [
        "Tómate tu tiempo para leer y no olvides dejar un 'Me gusta'.",
        "¡Es un gran artículo! La sección de comentarios te espera.",
        "Aprender un dato nuevo cada día hace una gran diferencia.",
        "Si te gusta lo que ves, no dudes en recomendar red-novable.com a tus amigos.",
        "Cada publicación es una oportunidad para aprender algo nuevo sobre energías limpias.",
        "¡Deja un comentario para compartir tu opinión o hacer preguntas sobre el artículo!"
    ];

    function obtenerFraseAleatoria(arreglo) {
        return arreglo[Math.floor(Math.random() * arreglo.length)];
    }

    // --- LÓGICA DE INICIO Y LLAMADAS AUTOMÁTICAS ---
    if (isAuthPage) {
        if (phpMensaje !== "") {
            setTimeout(() => {
                container.classList.add("mascota-visible");
                container.classList.remove("mascota-oculto");
                // Corrección: limpiar espacios extra del tipo de php para garantizar que la alerta se ponga en rojo
                let isErr = (phpTipo.trim().toLowerCase() === "error");
                setTimeout(() => { hablar(phpMensaje, isErr, "irse"); }, 600);
            }, 500);
        } else if (currentPath.includes("registro")) {
            setTimeout(() => {
                container.classList.add("mascota-visible");
                container.classList.remove("mascota-oculto");
                setTimeout(() => { hablar(obtenerFraseAleatoria(saludosRegistro), false, "irse"); }, 600);
            }, 500);
        }
    } else {
        setTimeout(() => {
            container.classList.add("mascota-visible");
            container.classList.remove("mascota-oculto");
            
            if (phpMensaje !== "") {
                let isErr = (phpTipo.trim().toLowerCase() === "error");
                setTimeout(() => hablar(phpMensaje, isErr), 1000);
            } else if (!sessionStorage.getItem("franxx_intro")) {
                setTimeout(() => { hablar(obtenerFraseAleatoria(saludosIndex)); }, 1000);
                sessionStorage.setItem("franxx_intro", "true");
            } else {
                setTimeout(() => {
                    if (currentPath.includes("crear_publicacion")) hablar(obtenerFraseAleatoria(saludosCrear));
                    else if (currentPath.includes("categorias")) hablar(obtenerFraseAleatoria(saludosCategorias));
                    else if (currentPath.includes("perfil")) hablar(obtenerFraseAleatoria(saludosPerfil));
                    else if (currentPath.includes("categoria")) hablar(obtenerFraseAleatoria(saludosCategorias)); // Reusamos las de categorías
                    else if (currentPath.includes("publicacion")) hablar(obtenerFraseAleatoria(saludosPublicacion));
                }, 1000);
            }
        }, 500);
    }

    // --- EVENTOS MANUALES ---
    franxxImg.addEventListener("click", () => {
        if (isAuthPage || estaHablando) return; 
        resetearInactividad(); 
        clickCount++;
        const tip = obtenerTipSinRepetir(); 
        const debeMoverse = (clickCount % 3 === 0); 
        hablar(tip, false, debeMoverse ? "cambiar" : "nada");
    });

    // --- DRAG & DROP (Ratón y Táctil) ---
    const iniciarArrastre = (x, y) => {
        isDragging = true;
        resetearInactividad();
        container.classList.add("dragging");
        startX = x - currentX;
        startY = y - currentY;
    };

    const moverArrastre = (e, x, y) => {
        if (!isDragging) return;
        e.preventDefault(); 
        currentX = x - startX;
        currentY = y - startY;
        container.style.transform = `translate(${currentX}px, ${currentY}px)`;
    };

    const soltarArrastre = () => {
        if (!isDragging) return;
        isDragging = false;
        resetearInactividad(); 
        container.classList.remove("dragging");
        
        container.style.transition = "transform 0.4s ease-out";
        container.style.transform = "translate(0px, 0px)";
        currentX = 0; currentY = 0;
        
        setTimeout(() => { container.style.transition = "none"; }, 400);
    };

    franxxImg.addEventListener("mousedown", (e) => iniciarArrastre(e.clientX, e.clientY));
    document.addEventListener("mousemove", (e) => moverArrastre(e, e.clientX, e.clientY));
    document.addEventListener("mouseup", soltarArrastre);

    franxxImg.addEventListener("touchstart", (e) => iniciarArrastre(e.touches[0].clientX, e.touches[0].clientY), {passive: false});
    document.addEventListener("touchmove", (e) => moverArrastre(e, e.touches[0].clientX, e.touches[0].clientY), {passive: false});
    document.addEventListener("touchend", soltarArrastre);
});