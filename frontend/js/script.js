// URL del servidor backend
const API_URL = 'http://localhost:3000/api';

// 1. REGISTRO DE USUARIOS
const formRegistro = document.getElementById('form-registro');
if (formRegistro) {
    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault(); 
        const nombre = document.getElementById('reg-nombre').value;
        const email = document.getElementById('reg-email').value;
        const password = document.getElementById('reg-password').value;

        try {
            const res = await fetch(`${API_URL}/registro`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre, email, password })
            });
            const data = await res.json();
            
            const msgBox = document.getElementById('msg-registro');
            msgBox.innerText = data.message || data.error;
            msgBox.style.color = data.message ? 'green' : 'red';
            
            if(data.message) formRegistro.reset();
        } catch (error) {
            console.error("Error al registrar bro:", error);
        }
    });
}

// 2. INICIO DE SESIÓN (LOGIN)
const formLogin = document.getElementById('form-login');
if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('log-email').value;
        const password = document.getElementById('log-password').value;

        try {
            const res = await fetch(`${API_URL}/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            const msgBox = document.getElementById('msg-login');
            
            if (data.token) {
                localStorage.setItem('token', data.token);
                msgBox.style.color = 'green';
                msgBox.innerText = `¡Bienvenido ${data.usuario}! Entrando...`;
                setTimeout(() => window.location.href = 'publicar.html', 1000);
            } else {
                msgBox.style.color = 'red';
                msgBox.innerText = data.error;
            }
        } catch (error) {
            console.error("Error al iniciar sesión bro:", error);
        }
    });
}

// 3. CREAR PUBLICACIONES
const formPost = document.getElementById('form-post');
if (formPost) {
    formPost.addEventListener('submit', async (e) => {
        e.preventDefault();
        const titulo = document.getElementById('post-titulo').value;
        const categoria = document.getElementById('post-categoria').value;
        const contenido = document.getElementById('post-contenido').value;
        const token = localStorage.getItem('token'); 

        if (!token) {
            alert('¡Alto ahí bro! Debes iniciar sesión primero para publicar.');
            return window.location.href = 'login.html';
        }

        try {
            const res = await fetch(`${API_URL}/posts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ titulo, categoria, contenido, token })
            });
            const data = await res.json();
            
            const msgBox = document.getElementById('msg-post');
            msgBox.innerText = data.message || data.error;
            msgBox.style.color = data.message ? 'green' : 'red';
            
            if(data.message) formPost.reset();
        } catch (error) {
            console.error("Error al publicar bro:", error);
        }
    });
}

// MEJORAS: FEED DINÁMICO Y BORRAR POSTS

// Que el Navbar cambie si ya iniciaste sesión
const tokenActual = localStorage.getItem('token');
const navAuth = document.querySelector('.nav-auth');

if (tokenActual && navAuth) {
    navAuth.innerHTML = `
        <a href="publicar.html" class="btn-registro" style="background-color: #10b981; border: none;"><i class="fas fa-plus"></i> Publicar</a>
        <a href="#" id="btn-logout" class="btn-login" style="color: #ef4444; border: 1px solid #ef4444;"><i class="fas fa-sign-out-alt"></i> Salir</a>
    `;

    document.getElementById('btn-logout').addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('token');
        window.location.href = 'index.html';
    });
}

//Función para borrar publicaciones
window.borrarPost = async function(idPost) {
    const token = localStorage.getItem('token');
    if (!token) {
        alert("¡Inicia sesión para borrar bro!");
        return;
    }

    if (confirm("¿Estás seguro de que quieres borrar esta publicación?")) {
        try {
            const res = await fetch(`${API_URL}/posts/${idPost}`, {
                method: 'DELETE',
                headers: { 'Authorization': token }
            });
            const data = await res.json();
            
            if (data.message) {
                alert(data.message);
                location.reload(); // Recarga la página
            } else {
                alert(data.error); 
            }
        } catch (error) {
            console.error("Error al borrar bro:", error);
        }
    }
};

// Cargar los posts de la base de datos al inicio
const contenedorPosts = document.getElementById('contenedor-posts');

if (contenedorPosts) {
    async function cargarPublicaciones() {
        try {
            const res = await fetch(`${API_URL}/posts`);
            const posts = await res.json();
            
            contenedorPosts.innerHTML = ''; 

            if (posts.length === 0) {
                contenedorPosts.innerHTML = '<p style="text-align: center; color: #888;">No hay publicaciones aún. ¡Sé el primero en romper el hielo!</p>';
                return;
            }

            posts.forEach(post => {
                const postHTML = `
                    <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--color-primario); margin-bottom: 20px; transition: 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                        
                        <button onclick="borrarPost(${post.id})" style="float: right; background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.2rem; transition: 0.2s;" onmouseover="this.style.color='#b91c1c'" onmouseout="this.style.color='#ef4444'" title="Borrar publicación">
                            <i class="fas fa-trash-alt"></i>
                        </button>

                        <h3 style="color: var(--color-oscuro); margin-bottom: 5px;">${post.titulo}</h3>
                        <small style="color: #888; font-weight: bold; display: block; margin-bottom: 15px;">
                            <i class="fas fa-tag"></i> ${post.categoria} | <i class="fas fa-user"></i> Por: ${post.autor}
                        </small>
                        <p style="line-height: 1.6; color: #444;">${post.contenido}</p>
                    </div>
                `;
                contenedorPosts.innerHTML += postHTML;
            });
        } catch (error) {
            console.error("Error al cargar el feed bro:", error);
            contenedorPosts.innerHTML = '<p style="color: red;">Error al conectar con el servidor.</p>';
        }
    }

    cargarPublicaciones();
}