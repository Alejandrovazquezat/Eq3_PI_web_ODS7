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

    // Prevención de errores si data-php-mensaje viene nulo
    const phpError = container.getAttribute("data-php-mensaje") || "";
    const currentPath = window.location.pathname.toLowerCase();
    const isAuthPage = currentPath.includes("iniciosesion") || currentPath.includes("registro");

    // BASE DE DATOS - ODS 7
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
        "Los centros de datos modernos migran hacia zonas frías para aprovechar el 'free cooling'.",
        "Dinamarca y Costa Rica son pioneros en la integración de redes eléctricas inteligentes y eólicas.",
        "El silicio utilizado en celdas solares es el segundo elemento más abundante en la corteza terrestre.",
        "La Smart Grid permite que los hogares con paneles solares vendan su excedente de energía.",
        "El reciclaje de aluminio requiere un 95% menos de energía que extraerlo desde cero.",
        "Las micro-redes hidroeléctricas electrifican zonas rurales sin construir presas invasivas.",
        "Eficiencia energética es optimizar los sistemas para hacer más utilizando menos recursos.",
        "Costa Rica mantuvo su red eléctrica funcionando solo con renovables durante 300 días seguidos.",
        "Los aerogeneradores modernos superan los 200 metros de altura para captar vientos más fuertes.",
        "El efecto termoeléctrico convierte el calor residual de maquinarias directamente en electricidad.",
        "La energía termosolar usa espejos para fundir sales que almacenan calor para generar energía de noche.",
        "Optimizar código fuente (Green Computing) reduce ciclos de CPU y consumo energético de servidores.",
        "La fusión nuclear experimental promete energía limpia casi ilimitada replicando las estrellas.",
        "Un metro cuadrado de bosque de algas marinas secuestra más carbono que un bosque terrestre.",
        "La cogeneración industrial produce simultáneamente electricidad y calor útil.",
        "Los inversores solares transforman la corriente continua en alterna sincronizada con la red.",
        "El biogás de vertederos evita que el metano llegue a la atmósfera.",
        "La energía undimotriz aprovecha el movimiento oscilatorio de las olas del mar.",
        "La movilidad eléctrica reduce emisiones de CO2 y elimina la contaminación acústica urbana.",
        "Apagar equipos en lugar de suspenderlos evita un masivo consumo de energía de reserva mundial.",
        "Las turbinas hidrocinéticas operan como aerogeneradores bajo el agua sin bloquear ríos.",
        "Las redes HVDC transportan electricidad limpia a miles de kilómetros con pérdidas mínimas.",
        "Materiales con cambio de fase (PCM) en construcción absorben y liberan calor pasivamente.",
        "Las granjas solares flotantes reducen la evaporación del agua y se refrigeran naturalmente.",
        "La economía circular en paneles solares recupera hasta el 95% de su silicio y metales.",
        "El estándar 'Passivhaus' reduce la demanda energética de un edificio en más del 80%.",
        "Las bombas de calor geotérmicas son 400% más eficientes que las calefacciones tradicionales.",
        "Los sistemas EMS con IA predicen picos de demanda y ajustan el suministro en milisegundos.",
        "Motores eléctricos de alta eficiencia (IE4 o IE5) reducen pérdidas electromagnéticas en la industria.",
        "Ventanas con celdas fotovoltaicas transparentes convierten rascacielos en generadores verticales.",
        "Compilar software de manera eficiente contribuye directamente a la meta del ODS 7.",
        "La energía azul aprovecha la presión osmótica de los ríos al desembocar en el mar.",
        "La piezoelectricidad genera corriente en baldosas aprovechando el paso de los peatones.",
        "Los sistemas V2G permiten que las baterías de autos eléctricos actúen como respaldo de la ciudad.",
        "La descarbonización es vital para limitar el aumento de la temperatura global a 1.5°C.",
        "Los cables superconductores tienen resistencia eléctrica cero, revolucionando la transmisión de energía.",
        "Las microalgas producen biocombustibles de aviación sin usar suelo agrícola fértil.",
        "La meteorología satelital permite a parques eólicos ajustar sus aspas con anticipación."
    ];

    let tipsDisponibles = [...ecoTipsOriginales];
    function obtenerTipSinRepetir() {
        if (tipsDisponibles.length === 0) tipsDisponibles = [...ecoTipsOriginales];
        return tipsDisponibles.splice(Math.floor(Math.random() * tipsDisponibles.length), 1)[0];
    }

    // --- PARPADEO A PRUEBA DE BALAS ---
    setInterval(() => {
        if (isDragging) return;
        
        let srcOriginal = franxxImg.src;
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

    // --- FUNCIÓN HABLAR ---
    function hablar(mensaje, duracion = 5000, esError = false, accionAlTerminar = "nada") {
        estaHablando = true;
        resetearInactividad(); 
        
        mensajeElem.textContent = mensaje;
        globo.style.display = "block";
        franxxImg.src = uriHablando;
        
        if (esError) {
            globo.style.borderColor = "#ff4757";
            container.classList.add("shake-anim", "modo-alerta"); 
        } else {
            globo.style.borderColor = "#000000";
            container.classList.remove("shake-anim", "modo-alerta");
        }

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
            } else {
                estaHablando = false;
            }
        }, duracion);
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

    // --- LÓGICA DE INICIO ---
    if (isAuthPage) {
        if (phpError !== "") {
            setTimeout(() => {
                container.classList.add("mascota-visible");
                container.classList.remove("mascota-oculto");
                setTimeout(() => { hablar("Cuidado, ingresa tus datos correctamente.", 5000, true, "irse"); }, 600);
            }, 500);
        } else if (currentPath.includes("registro")) {
            setTimeout(() => {
                container.classList.add("mascota-visible");
                container.classList.remove("mascota-oculto");
                setTimeout(() => { hablar("¿Eres nuevo en Red-novable? ¡Bienvenido a la plataforma!", 4000, false, "irse"); }, 600);
            }, 500);
        }
    } else {
        setTimeout(() => {
            container.classList.add("mascota-visible");
            container.classList.remove("mascota-oculto");
            
            if (!sessionStorage.getItem("franxx_intro")) {
                setTimeout(() => { hablar("¡Hola! Soy Franxx, ¡Bienvenido a red-novable!.", 4000); }, 1000);
                sessionStorage.setItem("franxx_intro", "true");
            } else if (phpError !== "") {
                setTimeout(() => hablar(phpError, 6000, true), 1000);
            } else {
                setTimeout(() => {
                    if (currentPath.includes("crear_publicacion")) hablar("¡Genial! Comparte tu información y asegúrate de citar fuentes confiables.", 4000);
                    else if (currentPath.includes("categorias")) hablar("Explora el directorio de temas especializados en tecnologías sostenibles.", 4000);
                    else if (currentPath.includes("perfil")) hablar("Este es tu perfil. Mantén tu información actualizada.", 4000);
                    else if (currentPath.includes("categoria")) hablar("Revisa todaslas publicaciones asociadas a esta categoría.", 4000);
                }, 1000);
            }
        }, 500);
    }

    // --- EVENTOS ---
    franxxImg.addEventListener("click", () => {
        if (isAuthPage || estaHablando) return; 
        resetearInactividad(); 
        clickCount++;
        const tip = obtenerTipSinRepetir(); 
        const debeMoverse = (clickCount % 3 === 0); 
        hablar(tip, 6000, false, debeMoverse ? "cambiar" : "nada");
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
        e.preventDefault(); // Evita que la pantalla haga scroll al mover la mascota
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

    // Eventos para Computadora (Ratón)
    franxxImg.addEventListener("mousedown", (e) => iniciarArrastre(e.clientX, e.clientY));
    document.addEventListener("mousemove", (e) => moverArrastre(e, e.clientX, e.clientY));
    document.addEventListener("mouseup", soltarArrastre);

    // Eventos para Celular (Táctil)
    franxxImg.addEventListener("touchstart", (e) => iniciarArrastre(e.touches[0].clientX, e.touches[0].clientY), {passive: false});
    document.addEventListener("touchmove", (e) => moverArrastre(e, e.touches[0].clientX, e.touches[0].clientY), {passive: false});
    document.addEventListener("touchend", soltarArrastre);
});