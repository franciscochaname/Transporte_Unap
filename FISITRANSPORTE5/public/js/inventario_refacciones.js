document.addEventListener('DOMContentLoaded', function() {
    const repuestos = json_encode($repuestosArray);

    document.getElementById('nombre_pieza').addEventListener('blur', function() {
        autocompletarCosto(repuestos);
    });

    document.getElementById('buscarPieza').addEventListener('keyup', function() {
        filtrarPiezas();
    });
});

function autocompletarCosto(repuestos) {
    var nombrePieza = document.getElementById('nombre_pieza').value;
    repuestos.forEach(function(repuesto) {
        if (repuesto['nombre_pieza'] === nombrePieza) {
            document.getElementById('costo').value = repuesto['costo'];
        }
    });
}

function filtrarPiezas() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById('buscarPieza');
    filter = input.value.toLowerCase();
    table = document.getElementById('tablaPiezas');
    tr = table.getElementsByTagName('tr');
    for (i = 1; i < tr.length; i++) {
        td = tr[i].getElementsByTagName('td')[1]; // La columna de 'Nombre de la Pieza' es la segunda (índice 1)
        if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

function openAddModal() {
    document.getElementById('piezaModalLabel').innerText = "Agregar Pieza";
    document.getElementById('accion').value = "agregar";
    document.getElementById('pieza_id').value = "";
    document.getElementById('proveedor_id').value = "";
    document.getElementById('nombre_pieza').value = "";
    document.getElementById('costo').value = "";
    document.getElementById('cantidad').value = "";
    $('#piezaModal').modal('show');
}

function openEditModal(id, proveedorId, nombrePieza, costo, cantidad) {
    document.getElementById('piezaModalLabel').innerText = "Editar Pieza";
    document.getElementById('accion').value = "editar";
    document.getElementById('pieza_id').value = id;
    document.getElementById('proveedor_id').value = proveedorId;
    document.getElementById('nombre_pieza').value = nombrePieza;
    document.getElementById('costo').value = costo;
    document.getElementById('cantidad').value = cantidad;
    $('#piezaModal').modal('show');
}
