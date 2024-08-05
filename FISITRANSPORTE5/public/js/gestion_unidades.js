document.addEventListener('DOMContentLoaded', function() {
    const capacidadInput = document.getElementById('capacidad');

    capacidadInput.addEventListener('input', function() {
        let value = parseInt(capacidadInput.value, 10);
        if (value < 1) {
            capacidadInput.value = 1;
        } else if (value > 50) {
            capacidadInput.value = 50;
        }
    });
});

function openAddModal() {
    document.getElementById('vehicleModalLabel').innerText = "Agregar Vehículo";
    document.getElementById('accion').value = "agregar";
    document.getElementById('vehiculo_id').value = "";
    document.getElementById('marca').value = "";
    document.getElementById('placa').value = "";
    document.getElementById('capacidad').value = 1; // Valor inicial
    $('#vehicleModal').modal('show');
}

function openEditModal(id, marca, placa, capacidad) {
    document.getElementById('vehicleModalLabel').innerText = "Editar Vehículo";
    document.getElementById('accion').value = "editar";
    document.getElementById('vehiculo_id').value = id;
    document.getElementById('marca').value = marca;
    document.getElementById('placa').value = placa;
    document.getElementById('capacidad').value = capacidad;
    $('#vehicleModal').modal('show');
}

function openDeleteModal(id) {
    $('#deleteModal').modal('show');
    document.getElementById('confirmDeleteBtn').onclick = function () {
        window.location.href = 'gestion_unidades.php?accion=eliminar&id=' + id;
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

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}
