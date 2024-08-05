<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Guardar itinerarios
    $accion = $_POST['accion'];
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $unidad = $_POST['unidad'];
    $chofer = $_POST['chofer']; 
    $ruta = $_POST['ruta'];
    $dia = $_POST['dia'];
    $horaInicio = $_POST['horaInicio'];
    $horaFin = $_POST['horaFin'];
    $grupo = $_POST['grupo'];
    $fechaInicio = $_POST['fechaInicio'];
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
    $dia = isset($_POST['dia']) ? $_POST['dia'] : null; // Obtener el día del formulario

    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "transporte5");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Mapeo de días en inglés a español
    $dias_espanol = [
        "Monday" => "Lunes",
        "Tuesday" => "Martes",
        "Wednesday" => "Miércoles",
        "Thursday" => "Jueves",
        "Friday" => "Viernes",
        "Saturday" => "Sábado",
        "Sunday" => "Domingo"
    ];

    // Función para verificar la disponibilidad del vehículo y chofer
    function verificarDisponibilidad($conn, $unidad, $chofer, $fecha, $horaInicio, $horaFin, $id = null) {
        $query = "SELECT COUNT(*) as count FROM itinerarios 
                  WHERE (vehiculo_id = ? OR personal_id = ?) AND fecha = ? 
                  AND ((h_inicio < ? AND h_final > ?) OR (h_inicio < ? AND h_final > ?) OR (h_inicio = ? OR h_final = ?))";
        if ($id) {
            $query .= " AND id != ?";
        }
        $stmt = $conn->prepare($query);
        if ($id) {
            $stmt->bind_param("iisssssssi", $unidad, $chofer, $fecha, $horaFin, $horaInicio, $horaInicio, $horaFin, $horaInicio, $horaFin, $id);
        } else {
            $stmt->bind_param("iisssssss", $unidad, $chofer, $fecha, $horaFin, $horaInicio, $horaInicio, $horaFin, $horaInicio, $horaFin);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'] == 0;
    }

    // Calcular la fecha del próximo día seleccionado a partir de la fecha de inicio para 6 meses
    if ($accion == 'agregar') {
        $currentDate = new DateTime($fechaInicio);
        $endDate = (clone $currentDate)->modify('+6 months');

        $error = false;

        while ($currentDate <= $endDate) {
            $currentDayOfWeek = $currentDate->format('l');
            if ($currentDayOfWeek == $dia) {
                $dia_espanol = $dias_espanol[$currentDayOfWeek];
                $fecha = $currentDate->format('Y-m-d');

                // Verificar disponibilidad
                if (!verificarDisponibilidad($conn, $unidad, $chofer, $fecha, $horaInicio, $horaFin)) {
                    $error = true;
                    $_SESSION['message'] = "Error: El vehículo o el chofer ya están asignados a otra ruta en la fecha y hora seleccionada.";
                    $_SESSION['message_type'] = "danger";
                    break;
                }
            }
            $currentDate->modify('+1 day');
        }

        if (!$error) {
            $currentDate = new DateTime($fechaInicio);
            while ($currentDate <= $endDate) {
                $currentDayOfWeek = $currentDate->format('l');
                if ($currentDayOfWeek == $dia) {
                    $dia_espanol = $dias_espanol[$currentDayOfWeek];
                    $fecha = $currentDate->format('Y-m-d');

                    $stmt = $conn->prepare("INSERT INTO itinerarios (vehiculo_id, personal_id, ruta_id, dia, fecha, h_inicio, h_final, grupo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiisssss", $unidad, $chofer, $ruta, $dia_espanol, $fecha, $horaInicio, $horaFin, $grupo);
                    $stmt->execute();
                    $stmt->close();
                }
                $currentDate->modify('+1 day');
            }
            $_SESSION['message'] = "Éxito: Itinerario guardado exitosamente.";
            $_SESSION['message_type'] = "success";
        }
    } elseif ($accion == 'editarMultiple') {
        $grupo = $_POST['grupoEditar'];
        $nuevoGrupo = $_POST['nuevoGrupo'];
        $unidad = $_POST['editUnidad'];
        $chofer = $_POST['editChofer'];
        $ruta = $_POST['editRuta'];
        $dia = $_POST['editDia'];
        $horaInicio = $_POST['editHoraInicio'];
        $horaFin = $_POST['editHoraFin'];
        $fechaInicio = $_POST['editFechaInicio'];

        // Obtener los registros actuales del grupo
        $stmt = $conn->prepare("SELECT * FROM itinerarios WHERE grupo = ? ORDER BY fecha ASC");
        $stmt->bind_param("s", $grupo);
        $stmt->execute();
        $result = $stmt->get_result();
        $registros = [];
        while ($row = $result->fetch_assoc()) {
            $registros[] = $row;
        }
        $stmt->close();

        $error = false;
        $currentDate = new DateTime($fechaInicio);
        $endDate = (clone $currentDate)->modify('+6 months');
        $index = 0;

        while ($currentDate <= $endDate && $index < count($registros)) {
            $currentDayOfWeek = $currentDate->format('l');
            if ($currentDayOfWeek == $dia) {
                $dia_espanol = $dias_espanol[$currentDayOfWeek];
                $fecha = $currentDate->format('Y-m-d');

                // Verificar disponibilidad
                if (!verificarDisponibilidad($conn, $unidad, $chofer, $fecha, $horaInicio, $horaFin, $registros[$index]['id'])) {
                    $error = true;
                    $_SESSION['message'] = "Error: El vehículo o el chofer ya están asignados a otra ruta en la fecha y hora seleccionada.";
                    $_SESSION['message_type'] = "danger";
                    break;
                }

                // Actualizar el registro
                $stmt = $conn->prepare("UPDATE itinerarios SET vehiculo_id = ?, personal_id = ?, ruta_id = ?, dia = ?, fecha = ?, h_inicio = ?, h_final = ?, grupo = ? WHERE id = ?");
                $stmt->bind_param("iiisssssi", $unidad, $chofer, $ruta, $dia_espanol, $fecha, $horaInicio, $horaFin, $nuevoGrupo, $registros[$index]['id']);
                $stmt->execute();
                $stmt->close();

                $index++;
            }
            $currentDate->modify('+1 day');
        }

        if (!$error) {
            $_SESSION['message'] = "Éxito: Itinerarios actualizados exitosamente.";
            $_SESSION['message_type'] = "success";
        }
    } elseif ($accion == 'eliminarMultiple') {
        $grupo = $_POST['grupoEliminar'];

        // Eliminar los registros actuales del grupo
        $stmt = $conn->prepare("DELETE FROM itinerarios WHERE grupo = ?");
        $stmt->bind_param("s", $grupo);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = "Éxito: Itinerarios eliminados exitosamente.";
        $_SESSION['message_type'] = "success";
    } elseif ($accion == 'agregarIndividual') {
        $fecha = $_POST['fecha'];
        $dia_espanol = $dias_espanol[date('l', strtotime($fecha))];

        // Verificar disponibilidad
        if (!verificarDisponibilidad($conn, $unidad, $chofer, $fecha, $horaInicio, $horaFin)) {
            $_SESSION['message'] = "Error: El vehículo o el chofer ya están asignados a otra ruta en la fecha y hora seleccionada.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Guardar itinerario individual
            $stmt = $conn->prepare("INSERT INTO itinerarios (vehiculo_id, personal_id, ruta_id, dia, fecha, h_inicio, h_final, grupo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiisssss", $unidad, $chofer, $ruta, $dia_espanol, $fecha, $horaInicio, $horaFin, $grupo);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Éxito: Itinerario individual guardado exitosamente.";
            $_SESSION['message_type'] = "success";
        }
    } elseif ($accion == 'editarIndividual') {
        $fecha = $_POST['fecha'];
        $dia_espanol = $dias_espanol[date('l', strtotime($fecha))];

        // Verificar disponibilidad
        if (!verificarDisponibilidad($conn, $unidad, $chofer, $fecha, $horaInicio, $horaFin, $id)) {
            $_SESSION['message'] = "Error: El vehículo o el chofer ya están asignados a otra ruta en la fecha y hora seleccionada.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Editar itinerario individual
            $stmt = $conn->prepare("UPDATE itinerarios SET vehiculo_id = ?, personal_id = ?, ruta_id = ?, dia = ?, fecha = ?, h_inicio = ?, h_final = ?, grupo = ? WHERE id = ?");
            $stmt->bind_param("iiisssssi", $unidad, $chofer, $ruta, $dia_espanol, $fecha, $horaInicio, $horaFin, $grupo, $id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Éxito: Itinerario individual actualizado exitosamente.";
            $_SESSION['message_type'] = "success";
        }
    }

    $conn->close();
    header("Location: programacion_itinerarios.php");
    exit();
}

if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $conn = new mysqli("localhost", "root", "", "transporte5");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM itinerarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    $_SESSION['message'] = "Éxito: Itinerario eliminado exitosamente.";
    $_SESSION['message_type'] = "success";
    header("Location: programacion_itinerarios.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <link rel="stylesheet" href="css/gestion_unidades.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/boton.css">
    <link rel="stylesheet" href="css/horario.css"> <!-- Nueva hoja de estilos para el horario -->
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="d-flex">
        <div class="container-fluid mt-4" id="main-content">
            <main>
                <section class="section">
                    <h2 class="text-center mb-4">Programación de Itinerarios</h2>
                    <?php if(isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                            <?= $_SESSION['message'] ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-4">
                        <div class="btn-group-left">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#itinerarioModal">
                                Agregación Múltiple
                            </button>
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editMultipleModal">
                                Edición Múltiple
                            </button>
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteMultipleModal">
                                Eliminación Múltiple
                            </button>
                        </div>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#listModal">
                            <i class="fas fa-list"></i> Ver Lista
                        </button>
                    </div>

                    <!-- Calendario -->
                    <div id="calendar"></div>

                    <!-- Modal de Bootstrap para agregar itinerario multiple -->
                    <div class="modal fade" id="itinerarioModal" tabindex="-1" role="dialog" aria-labelledby="itinerarioModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="itinerarioModalLabel">Agregar Itinerario</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para agregar itinerario multiple -->
                                    <form id="addForm" action="programacion_itinerarios.php" method="POST" onsubmit="return validateForm('addForm', 'addFormError')">
                                        <input type="hidden" id="accion" name="accion" value="agregar">
                                        <input type="hidden" id="itinerario_id" name="id">

                                        <div class="form-group">
                                            <label for="unidad">Unidad Móvil:</label>
                                            <select id="unidad" name="unidad" class="form-control" required>
                                                <option value="" disabled selected>Selecciona unidad móvil</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, marca, placa FROM vehiculos");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['marca']} - {$row['placa']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="chofer">Chofer:</label>
                                            <select id="chofer" name="chofer" class="form-control" required>
                                                <option value="" disabled selected>Selecciona chofer</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, nombre, apellido, dni FROM personal WHERE rol='chofer'");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['nombre']} {$row['apellido']} - {$row['dni']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="ruta">Ruta:</label>
                                            <select id="ruta" name="ruta" class="form-control" required>
                                                <option value="" disabled selected>Selecciona ruta</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT ruta_id, ubicacionInicial, ubicacionFinal FROM rutas");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['ruta_id']}'>{$row['ubicacionInicial']} - {$row['ubicacionFinal']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="grupo">Grupo:</label>
                                            <input type="text" id="grupo" name="grupo" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="dia">Día de Operación:</label>
                                            <select id="dia" name="dia" class="form-control" required>
                                                <option value="" disabled selected>Selecciona un día</option>
                                                <option value="Monday">Lunes</option>
                                                <option value="Tuesday">Martes</option>
                                                <option value="Wednesday">Miércoles</option>
                                                <option value="Thursday">Jueves</option>
                                                <option value="Friday">Viernes</option>
                                                <option value="Saturday">Sábado</option>
                                                <option value="Sunday">Domingo</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="horaInicio">Hora de Inicio:</label>
                                            <input type="time" id="horaInicio" name="horaInicio" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div class="form-group">
                                            <label for="horaFin">Hora de Fin:</label>
                                            <input type="time" id="horaFin" name="horaFin" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div class="form-group">
                                            <label for="fechaInicio">Fecha de Inicio:</label>
                                            <input type="date" id="fechaInicio" name="fechaInicio" class="form-control" required>
                                        </div>
                                        <div id="addFormError"></div>
                                        <button type="submit" class="btn btn-primary" id="modalButton">Guardar Itinerario</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Bootstrap para editar múltiples itinerarios -->
                    <div class="modal fade" id="editMultipleModal" tabindex="-1" role="dialog" aria-labelledby="editMultipleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editMultipleModalLabel">Editar Múltiples Itinerarios</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para editar múltiples itinerarios -->
                                    <form id="editForm" action="programacion_itinerarios.php" method="POST" onsubmit="return validateForm('editForm', 'editFormError')">
                                        <input type="hidden" id="accion" name="accion" value="editarMultiple">

                                        <div class="form-group">
                                            <label for="grupoEditar">Seleccionar Grupo de Itinerario:</label>
                                            <select id="grupoEditar" name="grupoEditar" class="form-control" required>
                                                <option value="" disabled selected>Selecciona un grupo</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT DISTINCT grupo FROM itinerarios WHERE grupo IS NOT NULL");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['grupo']}'>{$row['grupo']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="nuevoGrupo">Nuevo Nombre del Grupo:</label>
                                            <input type="text" id="nuevoGrupo" name="nuevoGrupo" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="editUnidad">Unidad Móvil:</label>
                                            <select id="editUnidad" name="editUnidad" class="form-control" required>
                                                <option value="" disabled selected>Selecciona unidad móvil</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, marca, placa FROM vehiculos");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['marca']} - {$row['placa']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="editChofer">Chofer:</label>
                                            <select id="editChofer" name="editChofer" class="form-control" required>
                                                <option value="" disabled selected>Selecciona chofer</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, nombre, apellido, dni FROM personal WHERE rol='chofer'");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['nombre']} {$row['apellido']} - {$row['dni']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="editRuta">Ruta:</label>
                                            <select id="editRuta" name="editRuta" class="form-control" required>
                                                <option value="" disabled selected>Selecciona ruta</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT ruta_id, ubicacionInicial, ubicacionFinal FROM rutas");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['ruta_id']}'>{$row['ubicacionInicial']} - {$row['ubicacionFinal']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="editDia">Día de Operación:</label>
                                            <select id="editDia" name="editDia" class="form-control" required>
                                                <option value="" disabled selected>Selecciona un día</option>
                                                <option value="Monday">Lunes</option>
                                                <option value="Tuesday">Martes</option>
                                                <option value="Wednesday">Miércoles</option>
                                                <option value="Thursday">Jueves</option>
                                                <option value="Friday">Viernes</option>
                                                <option value="Saturday">Sábado</option>
                                                <option value="Sunday">Domingo</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="editHoraInicio">Hora de Inicio:</label>
                                            <input type="time" id="editHoraInicio" name="editHoraInicio" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div class="form-group">
                                            <label for="editHoraFin">Hora de Fin:</label>
                                            <input type="time" id="editHoraFin" name="editHoraFin" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div class="form-group">
                                            <label for="editFechaInicio">Fecha de Inicio:</label>
                                            <input type="date" id="editFechaInicio" name="editFechaInicio" class="form-control" required>
                                        </div>

                                        <div id="editFormError"></div>
                                        <button type="submit" class="btn btn-primary" id="editModalButton">Guardar Cambios</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Bootstrap para eliminar múltiples itinerarios -->
                    <div class="modal fade" id="deleteMultipleModal" tabindex="-1" role="dialog" aria-labelledby="deleteMultipleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteMultipleModalLabel">Eliminar Múltiples Itinerarios</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para eliminar múltiples itinerarios -->
                                    <form id="deleteMultipleForm" action="programacion_itinerarios.php" method="POST">
                                        <input type="hidden" id="accion" name="accion" value="eliminarMultiple">
                                        <div class="form-group">
                                            <label for="grupoEliminar">Seleccionar Grupo de Itinerario:</label>
                                            <select id="grupoEliminar" name="grupoEliminar" class="form-control" required>
                                                <option value="" disabled selected>Selecciona un grupo</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT grupo FROM itinerarios GROUP BY grupo HAVING COUNT(*) > 1");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['grupo']}'>{$row['grupo']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Eliminar Itinerarios</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Bootstrap para ver la lista de registros -->
                    <div class="modal fade" id="listModal" tabindex="-1" role="dialog" aria-labelledby="listModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div style="display: flex; align-items: center;">
                                        <h5 class="modal-title" id="listModalLabel" style="margin-right: 20px;">Lista de Itinerarios</h5>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#itinerarioIndividualModal">Agregar Itinerario</button>
                                    </div>
                                    <div style="flex-grow: 1; display: flex; justify-content: flex-end;">
                                        <input type="text" id="searchInput" class="form-control filter-input" placeholder="Filtrar registros" onkeyup="filterTable()">
                                    </div>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                                    <div class="table-responsive">
                                        <?php
                                        $conn = new mysqli("localhost", "root", "", "transporte5");

                                        if ($conn->connect_error) {
                                            die("Connection failed: " . $conn->connect_error);
                                        }

                                        $result = $conn->query("SELECT i.*, v.marca, v.placa, p.nombre, p.apellido, p.dni, r.ubicacionInicial, r.ubicacionFinal 
                                                                FROM itinerarios i 
                                                                JOIN vehiculos v ON i.vehiculo_id = v.id 
                                                                JOIN personal p ON i.personal_id = p.id
                                                                JOIN rutas r ON i.ruta_id = r.ruta_id
                                                                ORDER BY i.fecha ASC");

                                        if ($result->num_rows > 0) {
                                            $index = 1;
                                            echo "<table class='table table-striped' id='itinerarioTable'>
                                                    <thead class='thead-dark'>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Unidad Móvil</th>
                                                            <th>Grupo</th>
                                                            <th>Chofer</th>
                                                            <th>Ruta</th>
                                                            <th>Día de la Semana</th>
                                                            <th>Fecha</th>
                                                            <th>Hora de Inicio</th>
                                                            <th>Hora de Fin</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>";
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>
                                                        <td>{$index}</td>
                                                        <td>{$row['marca']} - {$row['placa']}</td>
                                                        <td>{$row['grupo']}</td>
                                                        <td>{$row['nombre']} {$row['apellido']} - {$row['dni']}</td>
                                                        <td>{$row['ubicacionInicial']} - {$row['ubicacionFinal']}</td>
                                                        <td>{$row['dia']}</td>
                                                        <td>{$row['fecha']}</td>
                                                        <td>{$row['h_inicio']}</td>
                                                        <td>{$row['h_final']}</td>
                                                        <td>
                                                            <button class='btn btn-warning btn-sm' onclick='openEditModal(\"{$row['id']}\", \"{$row['vehiculo_id']}\", \"{$row['personal_id']}\", \"{$row['ruta_id']}\", \"{$row['grupo']}\", \"{$row['h_inicio']}\", \"{$row['h_final']}\", \"{$row['fecha']}\")'>Editar</button>
                                                            <button class='btn btn-danger btn-sm' onclick='confirmarEliminacion({$row['id']})'>Eliminar</button>
                                                        </td>
                                                    </tr>";
                                                $index++;
                                            }
                                            echo "</tbody>
                                                </table>";
                                        } else {
                                            echo "<p>No hay registros encontrados.</p>";
                                        }

                                        $conn->close();
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Bootstrap para agregar itinerario individual -->
                    <div class="modal fade" id="itinerarioIndividualModal" tabindex="-1" role="dialog" aria-labelledby="itinerarioIndividualModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="itinerarioIndividualModalLabel">Agregar Itinerario Individual</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para agregar itinerario individual -->
                                    <form id="addIndividualForm" action="programacion_itinerarios.php" method="POST" onsubmit="return validateForm('addIndividualForm', 'addIndividualFormError')">
                                        <input type="hidden" id="accion" name="accion" value="agregarIndividual">
                                        <input type="hidden" id="diaIndividual" name="dia">

                                        <div class="form-group">
                                            <label for="unidad">Unidad Móvil:</label>
                                            <select id="unidadIndividual" name="unidad" class="form-control" required>
                                                <option value="" disabled selected>Selecciona unidad móvil</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, marca, placa FROM vehiculos");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['marca']} - {$row['placa']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="chofer">Chofer:</label>
                                            <select id="choferIndividual" name="chofer" class="form-control" required>
                                                <option value="" disabled selected>Selecciona chofer</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, nombre, apellido, dni FROM personal WHERE rol='chofer'");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['nombre']} {$row['apellido']} - {$row['dni']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="ruta">Ruta:</label>
                                            <select id="rutaIndividual" name="ruta" class="form-control" required>
                                                <option value="" disabled selected>Selecciona ruta</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT ruta_id, ubicacionInicial, ubicacionFinal FROM rutas");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['ruta_id']}'>{$row['ubicacionInicial']} - {$row['ubicacionFinal']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="grupo">Grupo:</label>
                                            <input type="text" id="grupoIndividual" name="grupo" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="fechaInicioIndividual">Fecha:</label>
                                            <input type="date" id="fechaInicioIndividual" name="fecha" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="horaInicioIndividual">Hora de Inicio:</label>
                                            <input type="time" id="horaInicioIndividual" name="horaInicio" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div class="form-group">
                                            <label for="horaFinIndividual">Hora de Fin:</label>
                                            <input type="time" id="horaFinIndividual" name="horaFin" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div id="addIndividualFormError"></div>
                                        <button type="submit" class="btn btn-primary" id="addIndividualModalButton">Guardar Itinerario</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Bootstrap para editar itinerario individual -->
                    <div class="modal fade" id="editIndividualModal" tabindex="-1" role="dialog" aria-labelledby="editIndividualModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editIndividualModalLabel">Editar Itinerario Individual</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para editar itinerario individual -->
                                    <form id="editIndividualForm" action="programacion_itinerarios.php" method="POST" onsubmit="return validateForm('editIndividualForm', 'editIndividualFormError')">
                                        <input type="hidden" id="accion" name="accion" value="editarIndividual">
                                        <input type="hidden" id="editDiaIndividual" name="dia">
                                        <input type="hidden" id="editItinerarioId" name="id">

                                        <div class="form-group">
                                            <label for="editUnidadIndividual">Unidad Móvil:</label>
                                            <select id="editUnidadIndividual" name="unidad" class="form-control" required>
                                                <option value="" disabled selected>Selecciona unidad móvil</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, marca, placa FROM vehiculos");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['marca']} - {$row['placa']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="editChoferIndividual">Chofer:</label>
                                            <select id="editChoferIndividual" name="chofer" class="form-control" required>
                                                <option value="" disabled selected>Selecciona chofer</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT id, nombre, apellido, dni FROM personal WHERE rol='chofer'");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['nombre']} {$row['apellido']} - {$row['dni']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="editRutaIndividual">Ruta:</label>
                                            <select id="editRutaIndividual" name="ruta" class="form-control" required>
                                                <option value="" disabled selected>Selecciona ruta</option>
                                                <?php
                                                $conn = new mysqli("localhost", "root", "", "transporte5");
                                                if ($conn->connect_error) {
                                                    die("Connection failed: " . $conn->connect_error);
                                                }
                                                $result = $conn->query("SELECT ruta_id, ubicacionInicial, ubicacionFinal FROM rutas");
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='{$row['ruta_id']}'>{$row['ubicacionInicial']} - {$row['ubicacionFinal']}</option>";
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="editGrupo">Grupo:</label>
                                            <input type="text" id="editGrupo" name="grupo" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="editFecha">Fecha:</label>
                                            <input type="date" id="editFecha" name="fecha" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="editHoraInicio">Hora de Inicio:</label>
                                            <input type="time" id="editHoraInicio" name="horaInicio" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div class="form-group">
                                            <label for="editHoraFin">Hora de Fin:</label>
                                            <input type="time" id="editHoraFin" name="horaFin" class="form-control" required min="05:00" max="23:00">
                                        </div>

                                        <div id="editIndividualFormError"></div>
                                        <button type="submit" class="btn btn-primary" id="editIndividualModalButton">Guardar Cambios</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de confirmación de eliminación -->
                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    ¿Está seguro de que desea eliminar este itinerario?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </main>
        </div>
    </div>
    <?php include '../src/views/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,dayGridDay'
                },
                events: 'cargar_eventos.php',
                eventClick: function(info) {
                    // Aquí puedes agregar lógica para la edición o eliminación de eventos si es necesario
                    alert('Evento: ' + info.event.title);
                    alert('Coordenadas: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
                    alert('Vista actual: ' + info.view.type);
                    // cambia la propiedad "backgroundColor" del evento
                    info.el.style.backgroundColor = 'red';
                }
            });
            calendar.render();
        });

        function validateForm(formId, errorContainerId) {
            const form = document.getElementById(formId);
            const horaInicio = form.querySelector('[name="horaInicio"], [name="editHoraInicio"]').value;
            const horaFin = form.querySelector('[name="horaFin"], [name="editHoraFin"]').value;
            const formError = document.getElementById(errorContainerId);

            if (horaInicio < '05:00' || horaInicio > '23:00') {
                formError.textContent = 'La hora de inicio debe estar entre las 5:00 AM y las 11:00 PM.';
                formError.className = 'alert alert-danger alert-small';
                return false;
            }

            if (horaFin < '05:00' || horaFin > '23:00') {
                formError.textContent = 'La hora de fin debe estar entre las 5:00 AM y las 11:00 PM.';
                formError.className = 'alert alert-danger alert-small';
                return false;
            }

            if (horaInicio >= horaFin) {
                formError.textContent = 'La hora de inicio debe ser menor que la hora de fin.';
                formError.className = 'alert alert-danger alert-small';
                return false;
            }

            formError.textContent = '';
            formError.className = '';
            return true;
        }

        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('itinerarioTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const tds = tr[i].getElementsByTagName('td');
                let showRow = false;

                for (let j = 0; j < tds.length; j++) {
                    if (tds[j]) {
                        const tdText = tds[j].textContent || tds[j].innerText;
                        if (tdText.toLowerCase().indexOf(filter) > -1) {
                            showRow = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = showRow ? '' : 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const timeInputs = document.querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                input.addEventListener('keydown', function(e) {
                    e.preventDefault();
                });
            });

            document.getElementById('grupoEditar').addEventListener('change', function() {
                const grupo = this.value;
                if (grupo) {
                    fetch(`programacion_itinerarios.php?grupo=${grupo}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('nuevoGrupo').value = data.grupo;
                            document.getElementById('editUnidad').value = data.unidad;
                            document.getElementById('editChofer').value = data.chofer;
                            document.getElementById('editRuta').value = data.ruta;
                            document.getElementById('editDia').value = data.dia;
                            document.getElementById('editHoraInicio').value = data.horaInicio;
                            document.getElementById('editHoraFin').value = data.horaFin;
                            document.getElementById('editFechaInicio').value = data.fechaInicio;
                        });
                }
            });

            // Función para calcular el día de la semana a partir de una fecha
            function obtenerDiaSemana(fecha) {
                const dias = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const date = new Date(fecha);
                return dias[date.getDay()];
            }

            // Calcular día de la semana y asignar al campo oculto en el formulario de agregar itinerario
            document.getElementById('fechaInicioIndividual').addEventListener('change', function() {
                const diaSemana = obtenerDiaSemana(this.value);
                document.getElementById('diaIndividual').value = diaSemana;
            });

            // Calcular día de la semana y asignar al campo oculto en el formulario de editar itinerario
            document.getElementById('editFecha').addEventListener('change', function() {
                const diaSemana = obtenerDiaSemana(this.value);
                document.getElementById('editDiaIndividual').value = diaSemana;
            });

        });

        function openEditModal(id, unidad, chofer, ruta, grupo, horaInicio, horaFin, fecha) {
            document.getElementById('editIndividualForm').reset();
            document.getElementById('editItinerarioId').value = id;
            document.getElementById('editUnidadIndividual').value = unidad;
            document.getElementById('editChoferIndividual').value = chofer;
            document.getElementById('editRutaIndividual').value = ruta;
            document.getElementById('editGrupo').value = grupo;
            document.getElementById('editFecha').value = fecha;
            document.getElementById('editHoraInicio').value = horaInicio;
            document.getElementById('editHoraFin').value = horaFin;
            $('#editIndividualModal').modal('show');
        }

        function confirmarEliminacion(id) {
            $('#confirmDeleteModal').modal('show');
            document.getElementById('confirmDeleteButton').onclick = function() {
                window.location.href = `programacion_itinerarios.php?accion=eliminar&id=${id}`;
            };
        }

    </script>
</body>
</html>
