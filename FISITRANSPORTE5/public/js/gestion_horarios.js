document.addEventListener('DOMContentLoaded', function() {
    const horarioForm = document.getElementById('horarioForm');
    // No es necesario hacer validaciones aquí, ya que se manejan en el backend
});

function openAddModal() {
    document.getElementById('horarioModalLabel').innerText = "Agregar Horario";
    document.getElementById('accion').value = "agregar";
    document.getElementById('horario_id').value = "";
    document.getElementById('nombre').value = "";
    document.getElementById('hora_inicio').value = "";
    document.getElementById('hora_fin').value = "";
    $('#horarioModal').modal('show');
}

function openEditModal(id, nombre, horaInicio, horaFin) {
    document.getElementById('horarioModalLabel').innerText = "Editar Horario";
    document.getElementById('accion').value = "editar";
    document.getElementById('horario_id').value = id;
    document.getElementById('nombre').value = nombre;
    document.getElementById('hora_inicio').value = horaInicio;
    document.getElementById('hora_fin').value = horaFin;
    $('#horarioModal').modal('show');
}

function filterTable() {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById("search");
    filter = input.value.toUpperCase();
    table = document.getElementById("horarioTable");
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

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}
