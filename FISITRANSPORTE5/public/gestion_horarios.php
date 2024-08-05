<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/config.php';

$mensaje = ""; // Variable para almacenar mensajes
$mensaje_tipo = ""; // Variable para almacenar el tipo de mensaje (éxito o error)

// Función para verificar si el nombre de horario ya existe
function checkNombreHorarioExists($conn, $nombre, $horario_id = null) {
    $query = "SELECT COUNT(*) FROM horarios WHERE nombre = ?";
    if ($horario_id) {
        $query .= " AND id != ?";
    }
    $stmt = $conn->prepare($query);
    if ($horario_id) {
        $stmt->bind_param("si", $nombre, $horario_id);
    } else {
        $stmt->bind_param("s", $nombre);
    }
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Insertar horario en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $nombre = $_POST['nombre'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    $conn = getDBConnection();
    
    if (checkNombreHorarioExists($conn, $nombre)) {
        $mensaje = "El nombre del horario ya existe. Por favor, elija otro.";
        $mensaje_tipo = "error";
    } elseif ($hora_inicio >= $hora_fin) {
        $mensaje = "La hora de inicio debe ser menor que la hora de fin. Por favor, corrige los datos.";
        $mensaje_tipo = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO horarios (nombre, hora_inicio, hora_fin) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $hora_inicio, $hora_fin);
        if ($stmt->execute()) {
            $mensaje = "Horario agregado exitosamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al agregar el horario: " . $stmt->error;
            $mensaje_tipo = "error";
        }
        $stmt->close();
    }
    $conn->close();
}

// Actualizar horario en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    $conn = getDBConnection();
    
    if (checkNombreHorarioExists($conn, $nombre, $id)) {
        $mensaje = "El nombre del horario ya existe. Por favor, elija otro.";
        $mensaje_tipo = "error";
    } elseif ($hora_inicio >= $hora_fin) {
        $mensaje = "La hora de inicio debe ser menor que la hora de fin. Por favor, corrige los datos.";
        $mensaje_tipo = "error";
    } else {
        $stmt = $conn->prepare("UPDATE horarios SET nombre = ?, hora_inicio = ?, hora_fin = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $hora_inicio, $hora_fin, $id);
        if ($stmt->execute()) {
            $mensaje = "Horario actualizado exitosamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al actualizar el horario: " . $stmt->error;
            $mensaje_tipo = "error";
        }
        $stmt->close();
    }
    $conn->close();
}

// Eliminar horario de la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
    $id = $_POST['id'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM horarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensaje = "Horario eliminado exitosamente.";
        $mensaje_tipo = "exito";
    } else {
        $mensaje = "Error al eliminar el horario: " . $stmt->error;
        $mensaje_tipo = "error";
    }
    $stmt->close();
    $conn->close();
}

// Obtener horarios de la base de datos
$conn = getDBConnection();
$sql = "SELECT * FROM horarios";
$result = $conn->query($sql);
$horarios = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $horarios[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programación de Horario - UNAP</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/boton.css">
    <link rel="stylesheet" href="css/gestion_unidades.css">
    <script src="js/gestion_horarios.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="d-flex">
        <div class="container-fluid mt-4" id="main-content">
            <main>
                <section class="section">
                    <h2 class="text-center mb-4">Programación de Horario</h2>
                    <?php
                    if (isset($mensaje) && $mensaje_tipo == 'error') {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error:</strong> ' . $mensaje . '
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                              </div>';
                    } elseif (isset($mensaje) && $mensaje_tipo == 'exito') {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Éxito:</strong> ' . $mensaje . '
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                              </div>';
                    }
                    ?>
                    <div class="d-flex justify-content-between mb-4">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#horarioModal" onclick="openAddModal()">
                            Agregar Horario
                        </button>
                        <input type="text" id="search" onkeyup="filterTable()" class="form-control w-50" placeholder="Filtrar registros">
                    </div>

                    <!-- Modal de Bootstrap para agregar/editar horario -->
                    <div class="modal fade" id="horarioModal" tabindex="-1" role="dialog" aria-labelledby="horarioModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="horarioModalLabel">Agregar Horario</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para agregar/editar horario -->
                                    <form id="horarioForm" action="gestion_horarios.php" method="POST">
                                        <input type="hidden" id="accion" name="accion" value="agregar">
                                        <input type="hidden" id="horario_id" name="id">

                                        <div class="form-group">
                                            <input type="text" id="nombre" name="nombre" class="form-control" required placeholder="Nombre del Horario">
                                        </div>
                                        <div class="form-group">
                                            <label for="hora_inicio">Hora de Inicio:</label>
                                            <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="hora_fin">Hora de Fin:</label>
                                            <input type="time" id="hora_fin" name="hora_fin" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Guardar Horario</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($horarios)): ?>
                        <p class="no-results text-center">No se encontró similitud</p>
                    <?php else: ?>
                        <table class="table table-striped" id="horarioTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Hora de Inicio</th>
                                    <th>Hora de Fin</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($horarios as $horario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($horario['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($horario['hora_inicio']); ?></td>
                                        <td><?php echo htmlspecialchars($horario['hora_fin']); ?></td>
                                        <td>
                                            <button onclick="openEditModal('<?php echo $horario['id']; ?>', '<?php echo htmlspecialchars($horario['nombre']); ?>', '<?php echo htmlspecialchars($horario['hora_inicio']); ?>', '<?php echo htmlspecialchars($horario['hora_fin']); ?>')" class="btn btn-warning btn-sm">Editar</button>
                                            <form action="gestion_horarios.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo $horario['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
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
                    <p>¿Realmente deseas eliminar este horario? Esta acción no se puede deshacer.</p>
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
