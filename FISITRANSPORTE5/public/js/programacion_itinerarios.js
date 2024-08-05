function openAddModal() {
    document.getElementById('scheduleForm').reset();
    document.getElementById('accion').value = 'agregar';
    document.getElementById('itinerario_id').value = '';
    document.getElementById('itinerarioModalLabel').textContent = 'Agregar Itinerario';
    document.getElementById('modalButton').textContent = 'Guardar Itinerario';
    $('#itinerarioModal').modal('show');
}

function openEditModal(id, vehiculo_id, personal_id, ruta_id, dia, h_inicio, h_final, fecha) {
    document.getElementById('accion').value = 'editar';
    document.getElementById('itinerario_id').value = id;
    document.getElementById('unidad').value = vehiculo_id;
    document.getElementById('chofer').value = personal_id;
    document.getElementById('ruta').value = ruta_id;
    // Marcar los días de operación
    var dias = dia.split(',');
    document.querySelectorAll('#dias .form-check-input').forEach(input => {
        if (dias.includes(input.value)) {
            input.checked = true;
        } else {
            input.checked = false;
        }
    });
    document.getElementById('horaInicio').value = h_inicio;
    document.getElementById('horaFin').value = h_final;
    document.getElementById('fechaInicio').value = fecha;
    document.getElementById('itinerarioModalLabel').textContent = 'Editar Itinerario';
    document.getElementById('modalButton').textContent = 'Actualizar Itinerario';
    $('#itinerarioModal').modal('show');
}

function confirmarEliminacion(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este itinerario?')) {
        window.location.href = `programacion_itinerarios.php?accion=eliminar&id=${id}`;
    }
}


function toggleHorario(day) {
    const checkbox = document.getElementById(day);
    const horaInicio = document.getElementById(day + '_horaInicio');
    const horaFin = document.getElementById(day + '_horaFin');

    if (checkbox.checked) {
        horaInicio.disabled = false;
        horaFin.disabled = false;
    } else {
        horaInicio.disabled = true;
        horaFin.disabled = true;
    }
}
