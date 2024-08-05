<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include '../config/config.php';

$conn = getDBConnection();
$solicitudes = $conn->query("SELECT * FROM solicitudes_alquiler");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        $solicitud_id = $_POST['solicitud_id'];
        if ($accion === 'eliminar') {
            $stmt = $conn->prepare("DELETE FROM solicitudes_alquiler WHERE id = ?");
            $stmt->bind_param("i", $solicitud_id);
            $stmt->execute();
        } elseif ($accion === 'actualizar_estado') {
            $estado = $_POST['estado'];
            $stmt = $conn->prepare("UPDATE solicitudes_alquiler SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $estado, $solicitud_id);
            $stmt->execute();
        }
        header("Location: gestion_solicitudes.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Solicitudes de Alquiler - UNAP</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/boton.css">
    <link rel="stylesheet" href="css/gestion_unidades.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <style>
        .search-box {
            float: right;
            margin-bottom: 20px;
        }
        .search-box input {

            padding: 5px 10px;
            border: 1px solid #ccc;
            width: 300px;
            height: 50px;
        }
        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="d-flex">
        <!-- Contenido principal -->
        <div class="container-fluid mt-4" id="main-content">
            <main>
                <section class="section">
                    <h2 class="text-center mb-4">Solicitudes de Alquiler</h2>
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Filtrar registros">
                    </div>
                    <div class="table-container">
                        <table class="table table-striped" id="solicitudesTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Tipo Cliente</th>
                                    <th>Razón Social o Nombre</th>
                                    <th>RUC / DNI</th>
                                    <th>Recibido</th>
                                    <th>Destino/Ruta</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($solicitud = $solicitudes->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($solicitud['tipo_cliente']) ?></td>
                                        <td><?= htmlspecialchars($solicitud['razon_social_o_nombre']) ?></td>
                                        <td><?= htmlspecialchars($solicitud['ruc_dni']) ?></td>
                                        <td><?= htmlspecialchars($solicitud['fecha_hora_solicitud']) ?></td>
                                        <td><?= htmlspecialchars($solicitud['destino_ruta']) ?></td>
                                        <td><?= htmlspecialchars($solicitud['estado']) ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick="showDetails(<?= htmlspecialchars(json_encode($solicitud)) ?>)">Detalles</button>
                                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $solicitud['id'] ?>)">Eliminar</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
    <?php include '../src/views/footer.php'; ?>

    <script>
        function showDetails(solicitud) {
            $('#detallesModal input[name="tipo_cliente"]').val(solicitud.tipo_cliente);
            $('#detallesModal input[name="razon_social_o_nombre"]').val(solicitud.razon_social_o_nombre);
            $('#detallesModal input[name="ruc_dni"]').val(solicitud.ruc_dni);
            $('#detallesModal input[name="direccion"]').val(solicitud.direccion);
            $('#detallesModal input[name="telefono"]').val(solicitud.telefono);
            $('#detallesModal input[name="correo"]').val(solicitud.correo);
            $('#detallesModal input[name="fecha"]').val(solicitud.fecha);
            $('#detallesModal input[name="h_inicio"]').val(solicitud.h_inicio);
            $('#detallesModal input[name="h_final"]').val(solicitud.h_final);
            $('#detallesModal input[name="num_pasajeros"]').val(solicitud.num_pasajeros);
            $('#detallesModal input[name="destino_ruta"]').val(solicitud.destino_ruta);
            $('#detallesModal textarea[name="comentario"]').val(solicitud.comentario);
            $('#detallesModal input[name="solicitud_id"]').val(solicitud.id);
            $('#detallesModal').modal('show');
        }

        function openDeleteModal(id) {
            $('#deleteModal input[name="solicitud_id"]').val(id);
            $('#deleteModal').modal('show');
        }

        function setEstado(estado) {
            $('#detallesForm input[name="estado"]').val(estado);
            $('#detallesForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('searchInput');
            var table = document.getElementById('solicitudesTable');
            searchInput.addEventListener('keyup', function () {
                var filter = searchInput.value.toLowerCase();
                var rows = table.getElementsByTagName('tr');
                for (var i = 1; i < rows.length; i++) {
                    var cells = rows[i].getElementsByTagName('td');
                    var match = false;
                    for (var j = 0; j < cells.length; j++) {
                        if (cells[j].innerText.toLowerCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }
                    rows[i].style.display = match ? '' : 'none';
                }
            });
        });
    </script>

    <!-- Modal de detalles -->
    <div class="modal fade" id="detallesModal" tabindex="-1" role="dialog" aria-labelledby="detallesModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detallesModalLabel">Detalles de la Solicitud</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="gestion_solicitudes.php" id="detallesForm">
                        <input type="hidden" name="accion" value="actualizar_estado">
                        <input type="hidden" name="solicitud_id">
                        <input type="hidden" name="estado">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="tipo_cliente">Tipo Cliente</label>
                                <input type="text" name="tipo_cliente" class="form-control" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="ruc_dni">RUC / DNI</label>
                                <input type="text" name="ruc_dni" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="razon_social_o_nombre">Razón Social o Nombre</label>
                            <input type="text" name="razon_social_o_nombre" class="form-control" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" name="direccion" class="form-control" disabled>
                        </div>

                        <div class="form-group">
                            <label for="correo">Correo</label>
                            <input type="text" name="correo" class="form-control" disabled>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="telefono">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" disabled>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="num_pasajeros">Número de Pasajeros</label>
                                <input type="number" name="num_pasajeros" class="form-control" disabled>
                            </div>

                        </div>
                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label for="fecha">Fecha</label>
                                <input type="date" name="fecha" class="form-control" disabled>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="h_inicio">Hora Inicio</label>
                                <input type="time" name="h_inicio" class="form-control" disabled>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="h_final">Hora Final</label>
                                <input type="time" name="h_final" class="form-control" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="destino_ruta">Destino/Ruta</label>
                            <input type="text" name="destino_ruta" class="form-control" disabled>
                        </div>

                        <div class="form-group">
                            <label for="comentario">Comentario</label>
                            <textarea name="comentario" class="form-control" rows="3" disabled></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" onclick="setEstado('ACEPTADO')">Aceptar</button>
                            <button type="button" class="btn btn-danger" onclick="setEstado('RECHAZADO')">Rechazar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-confirm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">¿Estás seguro?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Realmente deseas eliminar esta solicitud? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="gestion_solicitudes.php">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="solicitud_id">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
