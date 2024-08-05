<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once '../config/config.php';

$mensaje = ""; // Variable para almacenar mensajes
$mensaje_tipo = ""; // Variable para almacenar el tipo de mensaje (exito o error)

// Insertar o editar vehículo en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    $marca = $_POST['marca'];
    $placa = $_POST['placa'];
    $capacidad = $_POST['capacidad'];
    $accion = $_POST['accion'];

    // Asegurar que la capacidad esté dentro del rango permitido
    $capacidad = max(1, min(50, $capacidad));

    // Verificar si la placa ya existe
    $stmt = $conn->prepare("SELECT id FROM vehiculos WHERE placa = ?");
    $stmt->bind_param("s", $placa);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0 && $accion == 'agregar') {
        // La placa ya existe, mostrar un mensaje de error
        $mensaje = "El vehículo con la placa " . htmlspecialchars($placa) . " ya está registrado.";
        $mensaje_tipo = "error";
    } elseif ($stmt->num_rows > 0 && $accion == 'editar') {
        // La placa ya existe, verificar si pertenece al mismo vehículo
        $stmt->bind_result($vehiculo_id_existente);
        $stmt->fetch();
        if ($vehiculo_id_existente != $_POST['vehiculo_id']) {
            $mensaje = "El vehículo con la placa " . htmlspecialchars($placa) . " ya está registrado.";
            $mensaje_tipo = "error";
        } else {
            // Editar vehículo existente
            $stmt = $conn->prepare("UPDATE vehiculos SET marca = ?, placa = ?, capacidad = ? WHERE id = ?");
            $stmt->bind_param("sssi", $marca, $placa, $capacidad, $_POST['vehiculo_id']);
            if ($stmt->execute()) {
                $mensaje = "Vehículo actualizado exitosamente.";
                $mensaje_tipo = "exito";
            } else {
                $mensaje = "Error al actualizar el vehículo: " . $stmt->error;
                $mensaje_tipo = "error";
            }
        }
    } else {
        // La placa no existe, proceder con la inserción
        $stmt = $conn->prepare("INSERT INTO vehiculos (marca, placa, capacidad) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $marca, $placa, $capacidad);
        if ($stmt->execute()) {
            $mensaje = "Vehículo agregado exitosamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al agregar el vehículo: " . $stmt->error;
            $mensaje_tipo = "error";
        }
    }

    $stmt->close();
    $conn->close();
}

// Eliminar vehículo de la base de datos
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $conn = getDBConnection();
    $vehiculo_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM vehiculos WHERE id = ?");
    $stmt->bind_param("i", $vehiculo_id);

    if ($stmt->execute()) {
        $mensaje = "Vehículo eliminado exitosamente.";
        $mensaje_tipo = "exito";
    } else {
        $mensaje = "Error al eliminar el vehículo: " . $stmt->error;
        $mensaje_tipo = "error";
    }

    $stmt->close();
    $conn->close();
}

// Obtener vehículos de la base de datos
$conn = getDBConnection();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT v.* FROM vehiculos v";
$result = $conn->query($sql);
$vehiculos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vehiculos[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <script src="js/index.js" defer></script>
        <title>Gestión de Unidades Móviles</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/tabla.css">
        <link rel="stylesheet" href="css/boton.css">
        <link rel="stylesheet" href="css/gestion_unidades.css">
        <!-- <link rel="stylesheet" href="css/estilos.css"> -->
        <script src="js/gestion_unidades.js"></script>
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
                        <h2 class="text-center mb-4">Gestión de Unidades Móviles</h2>
                        <?php
                        if (!empty($mensaje)) {
                            $clase_mensaje = $mensaje_tipo == "exito" ? "mensaje-exito" : "mensaje-error";
                            echo '<p class="text-center ' . $clase_mensaje . '">' . $mensaje . '</p>';
                        }
                        ?>
                        <div class="d-flex justify-content-between mb-4">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vehicleModal" onclick="openAddModal()">
                                Agregar Vehículo
                            </button>
                            <input type="text" id="search" onkeyup="filterTable()" class="form-control w-50" placeholder="Filtrar registros">
                        </div>

                        <!-- Modal de Bootstrap para agregar/editar vehículo -->
                        <div class="modal fade" id="vehicleModal" tabindex="-1" role="dialog" aria-labelledby="vehicleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="vehicleModalLabel">Agregar Vehículo</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Formulario para agregar/editar vehículo -->
                                        <form action="gestion_unidades.php" method="POST">
                                            <input type="hidden" id="accion" name="accion" value="agregar">
                                            <input type="hidden" id="vehiculo_id" name="vehiculo_id">
                                            
                                            <div class="form-group">
                                                <select id="marca" name="marca" class="form-control" required>
                                                    <option value="" selected disabled>Selecciona un vehiculo</option>
                                                    <option value="Hyundai">Hyundai</option>
                                                    <option value="Toyota">Toyota</option>
                                                    <option value="Mercedes Benz">Mercedes Benz</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <input type="text" id="placa" name="placa" class="form-control" required placeholder="Número de Placa:">
                                            </div>

                                            <div class="form-group">
                                                <input type="number" id="capacidad" name="capacidad" class="form-control" required placeholder="Capacidad:" min="1" max="50">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Guardar Vehículo</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($vehiculos)): ?>
                            <p class="no-results text-center">No se encontró similitud</p>
                        <?php else: ?>
                            <table class="table table-striped" id="vehicleTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Marca</th>
                                        <th>Placa</th>
                                        <th>Capacidad</th>
                                        <th>Disponibilidad</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehiculos as $vehiculo): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($vehiculo['marca']); ?></td>
                                            <td><?php echo htmlspecialchars($vehiculo['placa']); ?></td>
                                            <td><?php echo htmlspecialchars($vehiculo['capacidad']); ?></td>
                                            <td><?php echo htmlspecialchars($vehiculo['disponibilidad']); ?></td>
                                            <td><?php echo htmlspecialchars($vehiculo['estado']); ?></td>
                                            <td>
                                                <button onclick="openEditModal('<?php echo $vehiculo['id']; ?>', '<?php echo htmlspecialchars($vehiculo['marca']); ?>', '<?php echo htmlspecialchars($vehiculo['placa']); ?>', '<?php echo htmlspecialchars($vehiculo['capacidad']); ?>')" class="btn btn-warning btn-sm">Editar</button>
                                                <button onclick="openDeleteModal('<?php echo $vehiculo['id']; ?>')" class="btn btn-danger btn-sm">Eliminar</button>
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
                        <p>¿Realmente deseas eliminar este vehículo? Esta acción no se puede deshacer.</p>
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
