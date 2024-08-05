function openAddModal() {
    document.getElementById('userModalLabel').innerText = "Agregar Usuario";
    document.getElementById('accion').value = "agregar";
    document.getElementById('usuario_id').value = "";
    document.getElementById('username').value = "";
    document.getElementById('password').value = "";
    document.getElementById('rol').value = "";
    document.getElementById('personal_id').value = "";
    document.getElementById('propietario_nombre').value = "";
    togglePropietario('');
    $('#userModal').modal('show');
}

function openEditModal(id, username, rol, personal_id, propietario_nombre) {
    document.getElementById('userModalLabel').innerText = "Editar Usuario";
    document.getElementById('accion').value = "editar";
    document.getElementById('usuario_id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('password').value = "";
    document.getElementById('rol').value = rol;
    document.getElementById('personal_id').value = personal_id;
    document.getElementById('propietario_nombre').value = propietario_nombre;

    togglePropietario(rol);
    $('#userModal').modal('show');
}

function openDeleteModal(id) {
    $('#deleteModal').modal('show');
    document.getElementById('confirmDeleteBtn').onclick = function () {
        window.location.href = 'gestion_usuarios.php?accion=eliminar&id=' + id;
    };
}

function filterTable() {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById("search");
    filter = input.value.toUpperCase();
    table = document.getElementById("userTable");
    tr = table.getElementsByTagName("tr");
    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none";
        td = tr[i].getElementsByTagName("td");
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    break;
                }
            }
        }
    }
}

function toggleEstadoCuenta(id) {
    window.location.href = 'gestion_usuarios.php?accion=cambiar_estado&id=' + id;
}

function togglePropietario(rol) {
    var propietarioNombreContainer = document.getElementById('propietarioNombreContainer');
    var propietarioContainer = document.getElementById('propietarioContainer');
    if (rol === 'Administrador') {
        propietarioNombreContainer.style.display = 'block';
        propietarioContainer.style.display = 'none';
    } else if (rol === 'Mécanico') {
        propietarioNombreContainer.style.display = 'none';
        propietarioContainer.style.display = 'block';
    } else {
        propietarioNombreContainer.style.display = 'none';
        propietarioContainer.style.display = 'none';
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}
