function goToSolicitudAlquiler() {
    window.location.href = 'solicitud_alquiler.php';
}

function openModal() {
    document.getElementById('modalRecuperarCuenta').style.display = 'block';
}

function closeModal() {
    document.getElementById('modalRecuperarCuenta').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('modalRecuperarCuenta');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

function validarFormularioLogin() {
    var usuario = document.getElementById('usuario').value;
    var password = document.getElementById('password').value;

    if (usuario.length > 20) {
        alert('El usuario no puede tener más de 20 caracteres.');
        return false;
    }

    if (password.length > 8) {
        alert('La contraseña no puede tener más de 8 caracteres.');
        return false;
    }

    return true;
}

function validarFormularioRecuperar() {
    var usuario = document.getElementById('usuario_recuperar').value;
    var correo = document.getElementById('correo_recuperar').value;

    if (usuario.length > 20) {
        alert('El usuario no puede tener más de 20 caracteres.');
        return false;
    }

    return true;
}
