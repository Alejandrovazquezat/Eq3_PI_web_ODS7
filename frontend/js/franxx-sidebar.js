// frontend/js/franxx-sidebar.js
document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".franxx-sidebar-container");
    const botImg = document.getElementById("franxx-img-sidebar");
    const globo = document.getElementById("franxx-sidebar-globo");
    const mensajeElem = document.getElementById("franxx-sidebar-mensaje");

    if (!container || !botImg) return;

    const currentPage = container.getAttribute("data-page").toLowerCase();

    let estaHablando = false;

    const uriBase = "../image/franxx_base.png";
    const uriHablando = "../image/franxx_hablando.png";
    const uriBaseClose = "../image/franxx_base_closeeye.png";
    const uriHablandoClose = "../image/franxx_hablando_closeeye.png";

    // PRECARGA
    const p1 = new Image(); p1.src = uriBaseClose;
    const p2 = new Image(); p2.src = uriHablandoClose;
    const p3 = new Image(); p3.src = uriHablando;

    // --- BASE DE DATOS TÉCNICA PARA ADMINS ---
    const dashTipsOriginales = [
        "Monitorear las métricas de tráfico te ayuda a comprender el impacto de las publicaciones.",
        "Verifica siempre la integridad referencial antes de eliminar una categoría principal.",
        "Mantener un log actualizado de usuarios baneados previene intrusiones repetitivas.",
        "La revisión de publicaciones pendientes es crucial para la calidad científica del portal.",
        "Asegúrate de realizar backups de la base de datos SQL periódicamente.",
        "Posees permisos globales (CRUD). Maneja las credenciales con suma precaución.",
        "Filtrar contenido duplicado o spam mantiene nuestra base optimizada.",
        "Los autores aprecian los tiempos de respuesta rápidos al evaluar su contenido.",
        "Auditar las categorías asegura una arquitectura de la información limpia.",
        "Si detectas anomalías en los comentarios, revisa las IP para mitigar bots.",
        "La tabla de usuarios utiliza encriptación estricta de contraseñas.",
        "Limpiar datos huérfanos mejora la latencia de las consultas al servidor.",
        "Revisa los 'likes' para identificar temáticas con mayor conversión.",
        "Las llaves foráneas previenen registros huérfanos al eliminar datos.",
        "Aprobar contenido verificado fortalece nuestro cumplimiento del ODS 7.",
        "Escala privilegios de usuario solo a personal de alta confianza.",
        "No realices pruebas de estrés (Stress Tests) en el entorno de producción.",
        "El uso de sentencias preparadas (PDO) nos protege de inyecciones SQL.",
        "Optimizar consultas JOIN reduce el consumo de memoria RAM del servidor.",
        "Revisar Error Logs ayuda a encontrar cuellos de botella en la plataforma."
    ];

    let tipsDisponibles = [...dashTipsOriginales];

    function obtenerTipSinRepetir() {
        if (tipsDisponibles.length === 0) tipsDisponibles = [...dashTipsOriginales];
        return tipsDisponibles.splice(Math.floor(Math.random() * tipsDisponibles.length), 1)[0];
    }

    // --- PARPADEO ---
    setInterval(() => {
        if (estaHablando) {
            botImg.src = uriHablandoClose;
            setTimeout(() => { if(estaHablando) botImg.src = uriHablando; }, 300);
        } else {
            botImg.src = uriBaseClose;
            setTimeout(() => { if(!estaHablando) botImg.src = uriBase; }, 300);
        }
    }, 4000); 

    function hablar(mensaje, duracion = 5000) {
        estaHablando = true;
        mensajeElem.textContent = mensaje;
        globo.style.display = "block";
        botImg.src = uriHablando;
        
        setTimeout(() => {
            globo.style.display = "none";
            botImg.src = uriBase;
            estaHablando = false;
        }, duracion);
    }

    // --- SALUDO CORRECTO SEGÚN LA PÁGINA ---
    setTimeout(() => {
        if (currentPage.includes('dashboard')) {
            hablar("Panel general listo. Servidores operando con normalidad.");
        } 
        // AL USAR "GESTIONAR" ATRAPAMOS CUALQUIER VARIACIÓN DEL NOMBRE
        else if (currentPage.includes('gestionar_contenido')) {
            hablar("Gestor de bases de datos activo. Revisa las dependencias al borrar.");
        } 
        else if (currentPage.includes('revisar')) {
            hablar("Existen borradores en la cola. Procede con la revisión de textos.");
        } 
        else if (currentPage.includes('usuarios')) {
            hablar("Módulo de autenticación. Verifica los privilegios otorgados.");
        } 
        else if (currentPage.includes('comentarios')) {
            hablar("Panel de moderación. Eliminar interacciones tóxicas es vital.");
        }
    }, 800);

    // Interacción manual
    botImg.addEventListener("click", () => {
        if (estaHablando) return; 
        const tip = obtenerTipSinRepetir();
        hablar(tip, 6000);
    });
});