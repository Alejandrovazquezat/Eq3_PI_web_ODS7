document.addEventListener('DOMContentLoaded', () => {
    // 1. Manejar la apertura de la sección de comentarios
    document.querySelectorAll('.comment-trigger-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const pubId = this.getAttribute('data-pubid');
            const section = document.getElementById(`comments-${pubId}`);
            
            if (section.style.display === 'block') {
                section.style.display = 'none';
            } else {
                section.style.display = 'block';
            }
        });
    });

    // 2. Manejar el envío de comentarios
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (typeof usuarioLogueado === 'undefined' || !usuarioLogueado) {
                window.location.href = 'registro.php';
                return;
            }

            const pubId = this.getAttribute('data-pubid');
            const inputComentario = this.querySelector('input[name="comentario"]');
            const texto = inputComentario.value;
            const submitBtn = this.querySelector('button[type="submit"]');

            if (texto.trim() === '') return;

            // Deshabilitar input mientras envía
            inputComentario.disabled = true;

            fetch('../ajax/guardar_comentario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `publicacion_id=${pubId}&contenido=${encodeURIComponent(texto)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Limpiar el input
                    this.reset();
                    
                    // Crear el HTML del nuevo comentario
                    const listaComentarios = document.querySelector(`#comments-${pubId} .comments-list`);
                    const nuevoComentario = document.createElement('div');
                    nuevoComentario.className = 'comment-item';
                    
                    // Animación de aparición para el nuevo comentario
                    nuevoComentario.style.animation = 'fadeIn 0.4s ease';
                    
                    nuevoComentario.innerHTML = `
                        <div class="comment-user">${data.autor}</div>
                        <p>${data.contenido}</p>
                    `;
                    
                    // Añadirlo al final de la lista
                    listaComentarios.appendChild(nuevoComentario);
                    
                    // Hacer scroll automático hacia abajo para ver el nuevo comentario
                    listaComentarios.scrollTop = listaComentarios.scrollHeight;
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                inputComentario.disabled = false;
                inputComentario.focus();
            });
        });
    });
});