// Variables globales
let usuarioIdAEliminar = null;
let usuarioNombreAEliminar = null;
let usuarioIdCambiarRol = null;
let usuarioNombreCambiarRol = null;

// Función para confirmar eliminación
function confirmarEliminar(id, nombre) {
    usuarioIdAEliminar = id;
    usuarioNombreAEliminar = nombre;
    document.getElementById('modalMensaje').innerHTML = '¿Estás seguro de que quieres eliminar a <strong>' + nombre + '</strong>?';
    document.getElementById('modalConfirmacion').style.display = 'flex';
}

// Función para cerrar modal de eliminación
function cerrarModal() {
    document.getElementById('modalConfirmacion').style.display = 'none';
    usuarioIdAEliminar = null;
    usuarioNombreAEliminar = null;
}

// Función para mostrar modal de cambio de rol
function mostrarModalRol(id, nombre, rolActual) {
    usuarioIdCambiarRol = id;
    usuarioNombreCambiarRol = nombre;
    
    let rolesHtml = '';
    const roles = [
        {id: 1, nombre: 'Admin'},
        {id: 2, nombre: 'Editor'},
        {id: 3, nombre: 'Autor'},
        {id: 4, nombre: 'Usuario'}
    ];
    
    roles.forEach(rol => {
        const selected = (rol.id == rolActual) ? 'selected' : '';
        rolesHtml += `<option value="${rol.id}" ${selected}>${rol.nombre}</option>`;
    });
    
    document.getElementById('modalRolMensaje').innerHTML = `Cambiar rol de <strong>${nombre}</strong>`;
    document.getElementById('selectNuevoRol').innerHTML = rolesHtml;
    document.getElementById('modalCambioRol').style.display = 'flex';
}

// Función para cerrar modal de cambio de rol
function cerrarModalRol() {
    document.getElementById('modalCambioRol').style.display = 'none';
    usuarioIdCambiarRol = null;
    usuarioNombreCambiarRol = null;
}

// Función para confirmar cambio de rol
function confirmarCambioRol() {
    const nuevoRol = document.getElementById('selectNuevoRol').value;
    if (usuarioIdCambiarRol && nuevoRol) {
        window.location.href = `cambiar_rol.php?id=${usuarioIdCambiarRol}&rol=${nuevoRol}`;
    }
}

// Evento para confirmar eliminación
document.addEventListener('DOMContentLoaded', function() {
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) {
        btnConfirmar.onclick = function() {
            if (usuarioIdAEliminar) {
                window.location.href = 'eliminar_usuario.php?id=' + usuarioIdAEliminar;
            }
        }
    }
    
    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        const modalEliminar = document.getElementById('modalConfirmacion');
        const modalRol = document.getElementById('modalCambioRol');
        if (event.target == modalEliminar) {
            cerrarModal();
        }
        if (event.target == modalRol) {
            cerrarModalRol();
        }
    }
});