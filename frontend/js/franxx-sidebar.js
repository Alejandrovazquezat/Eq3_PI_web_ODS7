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

    // --- TIPS ADMINISTRATIVOS GENERALES (Sin lenguaje técnico avanzado) ---
    const dashTipsOriginales = [
        "Revisar las publicaciones a tiempo ayuda a mantener el sitio lleno de buena información.",
        "Asegúrate de que las publicaciones nuevas cumplan con las reglas de respeto y amabilidad.",
        "Mantener las categorías organizadas facilita mucho la lectura a todas las personas.",
        "Si notas que un comentario no aporta o falta al respeto, es mejor retirarlo del sistema.",
        "Aprobar contenido de calidad sobre energías limpias es el motor de esta comunidad.",
        "Es importante que los datos publicados sean útiles y cuenten con buenas fuentes.",
        "Tu labor de revisión es fundamental para que Red-novable siga creciendo cada día.",
        "Revisar la información compartida previene la difusión de noticias incorrectas.",
        "El respeto en los comentarios hace que más personas se sientan cómodas participando.",
        "Mantener actualizados a los usuarios fomenta un ambiente de aprendizaje sano.",
        "Un buen artículo sobre paneles solares o eólica puede inspirar grandes cambios.",
        "Rechazar con un buen comentario ayuda a los autores a mejorar su contenido.",
        "Cuidar el entorno digital es tan importante como cuidar nuestro medio ambiente.",
        "El trabajo en equipo hace que la moderación del sitio sea mucho más sencilla.",
        "Las personas aprecian cuando sus publicaciones se revisan de forma justa y amigable.",
        "¡Recuerda tomar pequeños descansos durante tus jornadas de revisión de artículos!",
        "La claridad al escribir una observación ayuda enormemente a quien redacta.",
        "Administrar una comunidad requiere empatía, paciencia y mucha atención a los detalles.",
        "Si tienes dudas sobre un artículo, busca información oficial del ODS 7 para corroborar.",
        "¡Gracias por ayudar a que Red-novable sea un sitio seguro y enriquecedor para todos!"
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

    function obtenerFraseAleatoria(arreglo) {
        return arreglo[Math.floor(Math.random() * arreglo.length)];
    }

    // --- SALUDO CORRECTO Y ALEATORIO SEGÚN LA PÁGINA ---
    const saludosDashboard = [
        "El panel general está listo. ¡Manos a la obra!",
        "Resumen general activo. Es un buen momento para revisar el progreso.",
        "¡Hola! Desde aquí puedes ver cómo va creciendo la comunidad."
    ];

    const saludosPublicaciones = [
        "Sección de publicaciones activa. Revisa bien antes de eliminar contenido.",
        "Aquí puedes gestionar los artículos para mantener el sitio impecable.",
        "Administra los textos publicados por la comunidad desde este módulo."
    ];

    const saludosCategorias = [
        "Módulo de categorías listo. Una buena estructura ayuda a la navegación.",
        "Mantener las categorías al día facilita la lectura a todos los visitantes.",
        "Verifica que cada temática sea clara y fácil de entender."
    ];

    const saludosRevisar = [
        "Es hora de revisar los textos pendientes. ¡Seguro hay artículos excelentes!",
        "Revisa las publicaciones con atención para asegurar contenido de calidad.",
        "Aprobar buenos artículos es lo que mantiene vivo a este proyecto."
    ];

    const saludosUsuarios = [
        "Módulo de gestión listo. Verifica que todo marche en orden con las cuentas.",
        "Desde aquí puedes ayudar a mantener una comunidad respetuosa.",
        "Revisa la participación general de las personas en la plataforma."
    ];

    const saludosComentarios = [
        "Panel de moderación. Eliminar comentarios que no aportan es de gran ayuda.",
        "Fomentar conversaciones amigables y educativas es la meta principal.",
        "Verifica que las personas interactúen con respeto en los artículos."
    ];

    setTimeout(() => {
        if (currentPage.includes('dashboard')) {
            hablar(obtenerFraseAleatoria(saludosDashboard), 5000);
        } 
        else if (currentPage.includes('gestionar_publicaciones')) {
            hablar(obtenerFraseAleatoria(saludosPublicaciones), 5000);
        } 
        else if (currentPage.includes('gestionar_categorias')) {
            hablar(obtenerFraseAleatoria(saludosCategorias), 5000);
        } 
        else if (currentPage.includes('revisar')) {
            hablar(obtenerFraseAleatoria(saludosRevisar), 5000);
        } 
        else if (currentPage.includes('usuarios')) {
            hablar(obtenerFraseAleatoria(saludosUsuarios), 5000);
        } 
        else if (currentPage.includes('comentarios')) {
            hablar(obtenerFraseAleatoria(saludosComentarios), 5000);
        }
    }, 800);

    // Interacción manual
    botImg.addEventListener("click", () => {
        if (estaHablando) return; 
        const tip = obtenerTipSinRepetir();
        hablar(tip, 6000);
    });
});