function openAddModal() {
    document.getElementById('vehicleModalLabel').innerText = "Agregar Personal";
    document.getElementById('accion').value = "agregar";
    document.getElementById('personal_id').value = "";
    document.getElementById('nombre').value = "";
    document.getElementById('apellido').value = "";
    document.getElementById('dni').value = "";
    document.getElementById('telefono').value = "";
    document.getElementById('email').value = "";
    document.getElementById('rol').value = "";
    document.getElementById('horario_id').value = "";
    $('#vehicleModal').modal('show');
}

function openEditModal(id, nombre, apellido, dni, rol, horario_id, telefono, email) {
    document.getElementById('vehicleModalLabel').innerText = "Editar Personal";
    document.getElementById('accion').value = "editar";
    document.getElementById('personal_id').value = id;
    document.getElementById('nombre').value = nombre;
    document.getElementById('apellido').value = apellido;
    document.getElementById('dni').value = dni;
    document.getElementById('telefono').value = telefono;
    document.getElementById('email').value = email;
    document.getElementById('rol').value = rol;
    document.getElementById('horario_id').value = horario_id;
    $('#vehicleModal').modal('show');
}

function openDeleteModal(id) {
    $('#deleteModal').modal('show');
    document.getElementById('confirmDeleteBtn').onclick = function () {
        window.location.href = 'gestion_personal.php?accion=eliminar&id=' + id;
    };
}

function filterTable() {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById("search");
    filter = input.value.toUpperCase();
    table = document.getElementById("vehicleTable");
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