<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include '../config/config.php'; // Incluir el archivo de configuración

$mensaje = ""; // Variable para almacenar mensajes
$mensaje_tipo = ""; // Variable para almacenar el tipo de mensaje (éxito o error)

// Insertar o editar PERSONAL en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection(); // Obtener la conexión a la base de datos

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $horario_id = $_POST['horario_id'];
    $rol = $_POST['rol'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $accion = $_POST['accion'];
    $personal_id = isset($_POST['personal_id']) ? $_POST['personal_id'] : null;

    proceedWithPersonalAction($conn, $accion, $nombre, $apellido, $dni, $horario_id, $rol, $telefono, $email, $personal_id, $mensaje, $mensaje_tipo);

    $conn->close();
}

function proceedWithPersonalAction($conn, $accion, $nombre, $apellido, $dni, $horario_id, $rol, $telefono, $email, $personal_id, &$mensaje, &$mensaje_tipo) {
    $dni_existente = false;
    $telefono_existente = false;
    $email_existente = false;

    // Verificar si el DNI ya existe en registros distintos al actual (si se está editando)
    $stmt = $conn->prepare("SELECT id FROM personal WHERE dni = ? AND id != ?");
    $stmt->bind_param("si", $dni, $personal_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $dni_existente = true;
    }
    $stmt->close();

    // Verificar si el teléfono ya existe en registros distintos al actual (si se está editando)
    $stmt = $conn->prepare("SELECT id FROM personal WHERE telefono = ? AND id != ?");
    $stmt->bind_param("si", $telefono, $personal_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $telefono_existente = true;
    }
    $stmt->close();

    // Verificar si el email ya existe en registros distintos al actual (si se está editando)
    $stmt = $conn->prepare("SELECT id FROM personal WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $personal_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $email_existente = true;
    }
    $stmt->close();

    if ($dni_existente) {
        $mensaje = "El DNI " . htmlspecialchars($dni) . " ya está registrado.";
        $mensaje_tipo = "error";
    } elseif ($telefono_existente) {
        $mensaje = "El teléfono " . htmlspecialchars($telefono) . " ya está registrado.";
        $mensaje_tipo = "error";
    } elseif ($email_existente) {
        $mensaje = "El email " . htmlspecialchars($email) . " ya está registrado.";
        $mensaje_tipo = "error";
    } else {
        if ($accion == 'agregar') {
            // el DNI, teléfono y email no existen, proceder con la inserción
            $stmt = $conn->prepare("INSERT INTO personal (nombre, apellido, dni, horario_id, rol, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nombre, $apellido, $dni, $horario_id, $rol, $telefono, $email);
            if ($stmt->execute()) {
                $mensaje = "Personal agregado exitosamente.";
                $mensaje_tipo = "éxito";
            } else {
                $mensaje = "Error al agregar el personal: " . $stmt->error;
                $mensaje_tipo = "error";
            }
        } else {
            // Editar personal existente
            $stmt = $conn->prepare("UPDATE personal SET nombre = ?, apellido = ?, dni = ?, horario_id = ?, rol = ?, telefono = ?, email = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $nombre, $apellido, $dni, $horario_id, $rol, $telefono, $email, $personal_id);
            if ($stmt->execute()) {
                $mensaje = "Personal actualizado exitosamente.";
                $mensaje_tipo = "éxito";
            } else {
                $mensaje = "Error al actualizar el personal: " . $stmt->error;
                $mensaje_tipo = "error";
            }
        }
    }
}

// Eliminar personal de la base de datos y deshabilitar usuario asociado
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $conn = getDBConnection();
    $personal_id = $_GET['id'];

    // Deshabilitar usuario asociado
    $stmt = $conn->prepare("UPDATE usuarios SET estadoCuenta = 0 WHERE personal_id = ?");
    $stmt->bind_param("i", $personal_id);
    $stmt->execute();
    $stmt->close();

    // Eliminar personal
    $stmt = $conn->prepare("DELETE FROM personal WHERE id = ?");
    $stmt->bind_param("i", $personal_id);
    if ($stmt->execute()) {
        $mensaje = "Personal eliminado exitosamente y usuario asociado deshabilitado.";
        $mensaje_tipo = "éxito";
    } else {
        $mensaje = "Error al eliminar el personal: " . $stmt->error;
        $mensaje_tipo = "error";
    }
    $stmt->close();
    $conn->close();
}

// Obtener personal de la base de datos
$conn = getDBConnection();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT p.*, h.nombre AS horario_nombre, h.hora_inicio, h.hora_fin
        FROM personal p
        LEFT JOIN horarios h ON p.horario_id = h.id";
$result = $conn->query($sql);
$personal = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $personal[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="js/index.js" defer></script>
    <title>Gestión del personal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/boton.css">
    <link rel="stylesheet" href="css/gestion_unidades.css">
    <link rel="stylesheet" href="css/gestion_personal.css">
    <script src="js/gestion_personal.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
   
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="d-flex">
        <!-- Contenido principal -->
        <div class="container-fluid mt-4" id="main-content">
            <main>
                <section class="section">
                    <h2 class="text-center mb-4">Gestión del personal</h2>
                    <?php
                    if (!empty($mensaje)) {
                        $clase_mensaje = $mensaje_tipo == "éxito" ? "mensaje-exito" : "mensaje-error";
                        echo '<p class="text-center ' . $clase_mensaje . '">' . $mensaje . '</p>';
                    }
                    ?>
                    <div class="d-flex justify-content-between mb-4">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vehicleModal" onclick="openAddModal()">
                            Agregar Personal
                        </button>
                        <input type="text" id="search" onkeyup="filterTable()" class="form-control w-50" placeholder="Filtrar registros">
                    </div>

                    <!-- Modal de Bootstrap para agregar/editar personal -->
                    <div class="modal fade" id="vehicleModal" tabindex="-1" role="dialog" aria-labelledby="vehicleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="vehicleModalLabel">Agregar personal</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para agregar/editar personal -->
                                    <form action="gestion_personal.php" method="POST">
                                        <input type="hidden" id="accion" name="accion" value="agregar">
                                        <input type="hidden" id="personal_id" name="personal_id">

                                        <!-- NOMBRES -->
                                        <div class="form-group">
                                            <input type="text" id="nombre" name="nombre" class="form-control" required placeholder="Nombres">
                                        </div>
                                        <!-- APELLIDOS -->
                                        <div class="form-group">
                                            <input type="text" id="apellido" name="apellido" class="form-control" required placeholder="Apellidos">
                                        </div>
                                        <!-- DNI -->
                                        <div class="form-group">
                                            <input type="text" id="dni" name="dni" class="form-control" required placeholder="DNI" maxlength="8" pattern="\d{8}" title="Debe contener exactamente 8 dígitos">
                                        </div>
                                        <!-- TELEFONO -->
                                        <div class="form-group">
                                            <input type="text" id="telefono" name="telefono" class="form-control" required placeholder="Teléfono" maxlength="9" pattern="\d{9}" title="Debe contener exactamente 9 dígitos">
                                        </div>
                                        <!-- EMAIL -->
                                        <div class="form-group">
                                            <input type="email" id="email" name="email" class="form-control" required placeholder="Correo Electrónico">
                                        </div>
                                        <!-- ROL -->
                                        <div class="form-group">
                                            <select id="rol" name="rol" class="form-control" required>
                                                <option value="" selected>Selecciona un rol</option> 
                                                <option value="mecanico">Mecánico</option>
                                                <option value="chofer">Chofer</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="horario_id">Horario:</label>
                                            <select id="horario_id" name="horario_id" class="form-control" required>
                                                <option value="" selected>Selecciona un horario</option> 
                                                <?php
                                                $conn = getDBConnection();
                                                $horarios = $conn->query("SELECT id, nombre, hora_inicio, hora_fin FROM horarios");
                                                while ($horario = $horarios->fetch_assoc()) {
                                                    echo '<option value="' . $horario['id'] . '">' . htmlspecialchars($horario['nombre']) . ' (' . htmlspecialchars($horario['hora_inicio']) . ' - ' . htmlspecialchars($horario['hora_fin']) . ')</option>';
                                                }
                                                $conn->close();
                                                ?>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Guardar Personal</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($personal)): ?>
                        <p class="no-results text-center">No se encontró similitud</p>
                    <?php else: ?>
                        <table class="table table-striped" id="vehicleTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>DNI</th>
                                    <th>Horario</th>
                                    <th>Rol</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($personal as $persona): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['dni']); ?></td>
                                        <td><?php echo htmlspecialchars(($persona['horario_nombre'] ?? '') . ' (' . ($persona['hora_inicio'] ?? '') . ' - ' . ($persona['hora_fin'] ?? '') . ')'); ?></td>
                                        <td><?php echo htmlspecialchars($persona['rol']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['email']); ?></td>
                                        <td>
                                            <button onclick="openEditModal('<?php echo $persona['id']; ?>', '<?php echo htmlspecialchars($persona['nombre']); ?>', '<?php echo htmlspecialchars($persona['apellido']); ?>', '<?php echo htmlspecialchars($persona['dni']); ?>', '<?php echo htmlspecialchars($persona['rol']); ?>', '<?php echo htmlspecialchars($persona['horario_id']); ?>', '<?php echo htmlspecialchars($persona['telefono']); ?>', '<?php echo htmlspecialchars($persona['email']); ?>')" class="btn btn-warning btn-sm">Editar</button>
                                            <button onclick="openDeleteModal('<?php echo $persona['id']; ?>')" class="btn btn-danger btn-sm">Eliminar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
    <?php include '../src/views/footer.php'; ?>

    <!-- Modal de confirmación de eliminación -->
    <div id="deleteModal" class="modal fade">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">¿Estás seguro?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <p>¿Realmente deseas eliminar este registro? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
