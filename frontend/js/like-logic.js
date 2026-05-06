document.addEventListener('DOMContentLoaded', () => {
    // Seleccionamos todos los botones de like en la página
    const likeButtons = document.querySelectorAll('.like-btn');

    likeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // 1. Verificar si el usuario está logueado (esta variable viene de PHP)
            if (typeof usuarioLogueado === 'undefined' || !usuarioLogueado) {
                window.location.href = 'registro.php';
                return;
            }

            const pubId = this.getAttribute('data-pubid');
            const countSpan = this.querySelector('.like-count');
            const icon = this.querySelector('.like-icon');

            // Prevenimos múltiples clicks rápidos
            this.style.pointerEvents = 'none';

            fetch('../ajax/toggle_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `publicacion_id=${pubId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizamos el número con una pequeña transición
                    countSpan.textContent = data.total_likes;
                    
                    // Toggle de la clase 'liked' para el color rojo y animación
                    if (data.action === 'like') {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                this.style.pointerEvents = 'auto';
            });
        });
    });
});