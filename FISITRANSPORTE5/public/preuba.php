<?php
include '../config/config.php';

$conn = getDBConnection();

// Obtener horarios disponibles
$horarios = $conn->query("SELECT id, nombre, hora_inicio, hora_fin FROM horarios");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_persona = $_POST['tipo_persona'];
    $razon_social_o_nombre = ($tipo_persona === 'persona') ? $_POST['nombre'] . ' ' . $_POST['apellido'] : $_POST['razon_social'];
    $ruc_dni = ($tipo_persona === 'persona') ? $_POST['dni'] : $_POST['ruc'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['email'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $num_pasajeros = $_POST['num_pasajeros'];
    $destino = $_POST['destino'];
    $comentarios = $_POST['comentarios'];

    $query = "INSERT INTO solicitudes_alquiler (tipo_cliente, razon_social_o_nombre, ruc_dni, direccion, telefono, correo, fecha, h_inicio, h_final, num_pasajeros, destino_ruta, comentario) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssssss", $tipo_persona, $razon_social_o_nombre, $ruc_dni, $direccion, $telefono, $correo, $fecha, $hora_inicio, $hora_fin, $num_pasajeros, $destino, $comentarios);

    if ($stmt->execute()) {
        echo "<script>
                $(document).ready(function(){
                    showModal('Éxito', 'La solicitud de alquiler se ha enviado con éxito.');
                });
              </script>";
    } else {
        echo "<script>
                $(document).ready(function(){
                    showModal('Error', 'Error al registrar la solicitud: " . $stmt->error . "');
                });
              </script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Alquiler de Buses</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: #f7f9fc;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }
        .rental-section {
            text-align: center;
        }
        .rental-section header h1 {
            margin-bottom: 20px;
        }
        .rental-section .form-control,
        .rental-section .btn {
            border-radius: 5px;
        }
        .rental-section .btn {
            background-color: #0078D4;
            color: white;
            cursor: pointer;
        }
        .rental-section .btn:hover {
            background-color: #005a9e;
        }
        .loading {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #3498db;
            width: 50px;
            height: 50px;
            z-index: 9999;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .modal-header {
            background-color: #0078D4;
            color: white;
        }
    </style>
</head>
<body>
    <div class="loading" id="loading"></div>
    <div class="container">
        <div class="rental-section" id="rental-section">
            <header>
                <h1>Solicitud de alquiler</h1>
            </header>
            <main>
                <form method="post" action="" id="alquilerForm">
                    <div class="form-group">
                        <label for="tipo_persona">Tipo de persona</label>
                        <select id="tipo_persona" name="tipo_persona" class="form-control" required>
                            <option value="empresa">Empresa</option>
                            <option value="persona">Persona</option>
                        </select>
                    </div>

                    <div id="empresa_fields">
                        <div class="form-group row">
                            <div class="col-10">
                                <input type="text" id="ruc" name="ruc" class="form-control" placeholder="RUC">
                            </div>
                            <div class="col-2">
                                <button type="button" id="buscarRuc" class="btn btn-info btn-sm">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" id="razon_social" name="razon_social" class="form-control" placeholder="Razón social">
                        </div>
                    </div>

                    <div id="persona_fields" style="display:none;">
                        <div class="form-group row">
                            <div class="col-10">
                                <input type="text" id="dni" name="dni" class="form-control" placeholder="DNI">
                            </div>
                            <div class="col-2">
                                <button type="button" id="buscarDni" class="btn btn-info btn-sm">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombres">
                            </div>
                            <div class="col">
                                <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Apellidos">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Dirección" required>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Teléfono" required>
                        </div>
                        <div class="col">
                            <input type="email" id="email" name="email" class="form-control" placeholder="Correo Electrónico" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-10">
                            <input type="date" id="fecha" name="fecha" class="form-control" required>
                        </div>
                        <div class="col-2">
                            <button type="button" id="consultarHorarios" class="btn btn-info btn-sm">
                                <i class="fa fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col">
                            <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required>
                        </div>
                        <div class="col">
                            <input type="time" id="hora_fin" name="hora_fin" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col">
                            <input type="number" id="num_pasajeros" name="num_pasajeros" class="form-control" placeholder="Número de Pasajeros" required>
                        </div>
                        <div class="col">
                            <input type="text" id="destino" name="destino" class="form-control" placeholder="Destino o Ruta" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <textarea id="comentarios" name="comentarios" class="form-control" placeholder="Comentarios Adicionales" rows="4"></textarea>
                    </div>

                    <div class="form-group row">
                        <div class="col">
                            <button type="button" class="btn btn-secondary btn-block" onclick="window.history.back()">Volver</button>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-primary btn-block">Enviar solicitud</button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <div id="modalHorarios" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Horarios Disponibles</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="horariosTable" class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Horario</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $horarios->data_seek(0); // reset the pointer to the start
                            while ($horario = $horarios->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($horario['nombre']) ?></td>
                                    <td><?= htmlspecialchars($horario['hora_inicio']) ?></td>
                                    <td><?= htmlspecialchars($horario['hora_fin']) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tipoPersona = document.getElementById('tipo_persona').value;
            updateFields(tipoPersona);

            document.getElementById('tipo_persona').addEventListener('change', function () {
                updateFields(this.value);
            });

            document.getElementById('buscarRuc').addEventListener('click', function () {
                var ruc = document.getElementById('ruc').value;
                var loading = document.getElementById('loading');
                if (ruc) {
                    loading.style.display = 'block';
                    fetch('api.php?ruc=' + ruc)
                        .then(response => response.json())
                        .then(data => {
                            loading.style.display = 'none';
                            if (data.success) {
                                document.getElementById('razon_social').value = data.data.razonSocial || '';
                                document.getElementById('direccion').value = data.data.direccion || '';
                                document.getElementById('telefono').value = data.data.telefono || '';
                                document.getElementById('email').value = data.data.email || '';
                            } else {
                                showModal('Error', 'No se encontraron datos para el RUC ingresado.');
                            }
                        })
                        .catch(error => {
                            loading.style.display = 'none';
                            console.error('Error:', error);
                        });
                } else {
                    showModal('Error', 'Por favor ingrese un RUC.');
                }
            });

            document.getElementById('buscarDni').addEventListener('click', function () {
                var dni = document.getElementById('dni').value;
                var loading = document.getElementById('loading');
                if (dni) {
                    loading.style.display = 'block';
                    fetch('https://apiperu.dev/api/dni/' + dni, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer 7542e3bd15b287c44653b581ea63eae1ece9b1d700753aece45319e7a0752695'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            loading.style.display = 'none';
                            if (data.success) {
                                document.getElementById('nombre').value = data.data.nombres || '';
                                document.getElementById('apellido').value = (data.data.apellido_paterno + ' ' + data.data.apellido_materno).trim();
                                document.getElementById('direccion').value = data.data.direccion || '';  // Si la API no devuelve la dirección, dejar el campo vacío
                            } else {
                                showModal('Error', 'No se encontraron datos para el DNI ingresado.');
                            }
                        })
                        .catch(error => {
                            loading.style.display = 'none';
                            console.error('Error:', error);
                        });
                } else {
                    showModal('Error', 'Por favor ingrese un DNI.');
                }
            });

            $('#consultarHorarios').click(function () {
                $('#modalHorarios').modal('show');
            });

            $('.close').click(function () {
                $('#modalHorarios').modal('hide');
            });

            window.onclick = function (event) {
                var modal = document.getElementById('modalHorarios');
                if (event.target == modal) {
                    $('#modalHorarios').modal('hide');
                }
            };

            // Validación de RUC y DNI
            document.getElementById('ruc').addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });

            document.getElementById('dni').addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
            });

            // Validación de teléfono
            document.getElementById('telefono').addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
            });

            // Validación de número de pasajeros
            document.getElementById('num_pasajeros').addEventListener('input', function () {
                const value = parseInt(this.value, 10);
                if (value < 1) {
                    this.value = 1;
                } else if (value > 50) {
                    this.value = 50;
                }
            });

            document.getElementById('fecha').setAttribute('min', new Date().toISOString().split('T')[0]);

            document.getElementById('alquilerForm').addEventListener('submit', function (event) {
                var tipoPersona = document.getElementById('tipo_persona').value;
                var ruc = document.getElementById('ruc').value;
                var dni = document.getElementById('dni').value;
                var telefono = document.getElementById('telefono').value;
                var fecha = document.getElementById('fecha').value;
                var horaInicio = document.getElementById('hora_inicio').value;
                var horaFin = document.getElementById('hora_fin').value;
                var numPasajeros = document.getElementById('num_pasajeros').value;

                if (tipoPersona === 'empresa' && ruc.length !== 11) {
                    showModal('Error', 'El RUC debe tener 11 dígitos.');
                    event.preventDefault();
                    return;
                }

                if (tipoPersona === 'persona' && dni.length !== 8) {
                    showModal('Error', 'El DNI debe tener 8 dígitos.');
                    event.preventDefault();
                    return;
                }

                if (telefono.length !== 9) {
                    showModal('Error', 'El teléfono debe tener 9 dígitos.');
                    event.preventDefault();
                    return;
                }

                const today = new Date().toISOString().split('T')[0];
                if (fecha < today) {
                    showModal('Error', 'La fecha debe ser la actual o una fecha futura.');
                    event.preventDefault();
                    return;
                }

                const horaInicioTime = parseInt(horaInicio.replace(':', ''), 10);
                const horaFinTime = parseInt(horaFin.replace(':', ''), 10);
                if (horaInicioTime < 500 || horaInicioTime > 2300 || horaFinTime < 500 || horaFinTime > 2300) {
                    showModal('Error', 'Las horas deben estar entre las 5:00 AM y las 11:00 PM.');
                    event.preventDefault();
                    return;
                }

                if (horaInicioTime >= horaFinTime) {
                    showModal('Error', 'La hora de fin debe ser posterior a la hora de inicio.');
                    event.preventDefault();
                    return;
                }

                if (numPasajeros < 1 || numPasajeros > 50) {
                    showModal('Error', 'El número de pasajeros debe ser entre 1 y 50.');
                    event.preventDefault();
                    return;
                }
            });

            function updateFields(tipoPersona) {
                var empresaFields = document.getElementById('empresa_fields');
                var personaFields = document.getElementById('persona_fields');

                if (tipoPersona === 'persona') {
                    empresaFields.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
                    personaFields.querySelectorAll('input').forEach(input => input.setAttribute('required', true));
                    empresaFields.style.display = 'none';
                    personaFields.style.display = 'block';
                } else {
                    personaFields.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
                    empresaFields.querySelectorAll('input').forEach(input => input.setAttribute('required', true));
                    personaFields.style.display = 'none';
                    empresaFields.style.display = 'block';
                }
            }

            function showModal(title, message) {
                var modal = `
                    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="messageModalLabel">${title}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    ${message}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modal);
                $('#messageModal').modal('show');
                $('#messageModal').on('hidden.bs.modal', function () {
                    $(this).remove();
                });
            }
        });
    </script>
</body>
</html>
