<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include '../config/config.php'; // Asegúrate de ajustar la ruta según tu estructura de directorios

$conn = getDBConnection();

// Manejar la solicitud de búsqueda de vehículos
if (isset($_GET['placa'])) {
    $placa = $_GET['placa'];
    $stmt = $conn->prepare("SELECT id, marca, placa FROM vehiculos WHERE placa LIKE ?");
    $searchParam = "%" . $placa . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table class='table'><tr><th>Marca</th><th>Placa</th><th>Acciones</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['marca']) . "</td>";
            echo "<td>" . htmlspecialchars($row['placa']) . "</td>";
            echo "<td><button class='btn btn-primary' onclick='abrirModal(" . $row['id'] . ", \"" . htmlspecialchars($row['marca']) . "\", \"" . htmlspecialchars($row['placa']) . "\")'>Mantener</button></td></tr>";
        }
        echo "</table>";
    } else {
        echo "No se encontraron vehículos.";
    }
    $stmt->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insertar nuevo reporte de mantenimiento
    $fecha_mantenimiento = $_POST['fecha_mantenimiento'];
    $tipo_servicio = $_POST['tipo_servicio'];
    $vehiculo_id = $_POST['vehiculo_id'];
    $repuestos_ids = isset($_POST['repuestos']) ? $_POST['repuestos'] : [];
    $sugerencias = $_POST['sugerencias'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $estado = $_POST['estado'];
    $nombre_mecanico = $_POST['nombre_mecanico'];

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Insertar reporte de mantenimiento
        $query = "INSERT INTO reporte (mecanico_id, fecha_mantenimiento, tipo_servicio, vehiculo_id, repuestos, sugerencias, fecha_ingreso, estado, nombre_mecanico) 
                  VALUES ((SELECT id FROM usuarios WHERE username = ?), ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $repuestos_str = implode(", ", $repuestos_ids);
        $stmt->bind_param("sssssssss", $_SESSION['username'], $fecha_mantenimiento, $tipo_servicio, $vehiculo_id, $repuestos_str, $sugerencias, $fecha_ingreso, $estado, $nombre_mecanico);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar el mantenimiento: " . $stmt->error);
        }

        // Actualizar cantidad de repuestos
        foreach ($repuestos_ids as $repuesto_id) {
            $query = "UPDATE repuestos SET cantidad = cantidad - 1 WHERE id = ? AND cantidad > 0";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $repuesto_id);
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar la cantidad de repuestos: " . $stmt->error);
            }

            if ($stmt->affected_rows == 0) {
                throw new Exception("El repuesto con ID $repuesto_id ya no está disponible.");
            }
        }

        // Confirmar transacción
        $conn->commit();
        echo "Mantenimiento registrado exitosamente.";

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $stmt->close();
}

// Obtener servicios, repuestos y mecánicos disponibles
$servicios = $conn->query("SELECT id, nombre FROM servicios");
$repuestos = $conn->query("SELECT id, nombre_pieza, cantidad FROM repuestos ORDER BY nombre_pieza ASC");
$mecanicos = $conn->query("SELECT p.id, p.nombre, p.apellido FROM personal p WHERE p.rol = 'mecanico'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Mantenimiento</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/mantenimiento.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/chart.js" defer></script>
    <script src="js/index.js" defer></script>
    <script>
        function validateForm() {
            var checkboxes = document.querySelectorAll('input[name="repuestos[]"]');
            var isChecked = Array.prototype.slice.call(checkboxes).some(x => x.checked);
            if (!isChecked) {
                alert('Debe seleccionar al menos un repuesto.');
                return false;
            }
            return true;
        }

        function toggleForm() {
            var isEdit = document.getElementById('editCheckbox').checked;
            document.getElementById('editForm').classList.toggle('hidden', !isEdit);
            document.getElementById('addForm').classList.toggle('hidden', isEdit);
        }

        function buscarVehiculo() {
            var placa = document.getElementById('placa_buscar').value;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '?placa=' + placa, true);
            xhr.onload = function () {
                if (this.status == 200) {
                    document.getElementById('vehiculoResultados').innerHTML = this.responseText;
                }
            };
            xhr.send();
        }

        function abrirModal(id, marca, placa) {
            document.getElementById('vehiculo_id_modal').value = id;
            document.getElementById('vehiculo_marca_modal').innerText = marca;
            document.getElementById('vehiculo_placa_modal').innerText = placa;
            var modal = new bootstrap.Modal(document.getElementById('mantenimientoModal'));
            modal.show();
        }

        window.onload = function() {
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_mantenimiento').setAttribute('min', today);
        };
    </script>
    <style>
        .low-stock {
            background-color: yellow;
        }
        .out-of-stock {
            background-color: red;
            color: white;
        }
        .hidden {
            display: none;
        }
        .search-container {
            display: flex;
            align-items: center;
            margin-top: 20px;
        }
        .search-container input[type="text"] {
            flex-grow: 1;
            margin-right: 10px;
            padding: 5px;
            font-size: 16px;
        }
        .search-container button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search-container button:hover {
            background-color: #0056b3;
        }
        .search-container button:focus {
            outline: none;
        }
        .search-container button i {
            font-size: 16px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <main>
        <section class="section">
            <h2>Registrar Mantenimiento</h2>
            <label for="placa_buscar">Buscar vehículo por placa:</label>
            <div class="search-container">
                <input type="text" id="placa_buscar" name="placa_buscar">
                <button type="button" onclick="buscarVehiculo()"><i class="fas fa-search"></i></button>
            </div>
            
            <div id="vehiculoResultados"></div>
            
            <div id="detallesVehiculo"></div>

            <!-- Modal -->
            <div class="modal fade" id="mantenimientoModal" tabindex="-1" role="dialog" aria-labelledby="mantenimientoModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mantenimientoModalLabel">Registrar Mantenimiento</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="post" action="" id="mantenimientoForm" onsubmit="return validateForm()">
                                <input type="hidden" id="vehiculo_id_modal" name="vehiculo_id">
                                <div>
                                    <label>Marca:</label>
                                    <span id="vehiculo_marca_modal"></span>
                                </div>
                                <div>
                                    <label>Placa:</label>
                                    <span id="vehiculo_placa_modal"></span>
                                </div>
                                <div class="date-container">

                                    <label for="nombre_mecanico">Nombre del Mecánico:</label>
                                    <select name="nombre_mecanico" id="nombre_mecanico" required>
                                        <?php while ($mecanico = $mecanicos->fetch_assoc()) { ?>
                                            <option value="<?= htmlspecialchars($mecanico['nombre'] . ' ' . $mecanico['apellido']) ?>"><?= htmlspecialchars($mecanico['nombre'] . ' ' . $mecanico['apellido']) ?></option>
                                        <?php } ?>
                                    </select>
                                    
                                    <div>
                                        <label for="fecha_ingreso">Fecha de Ingreso:</label>
                                        <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <div>
                                        <label for="fecha_mantenimiento">Fecha de Mantenimiento:</label>
                                        <input type="date" name="fecha_mantenimiento" id="fecha_mantenimiento" required>
                                    </div>
                                   
                                </div>

                                <label for="tipo_servicio">Tipo de Servicio:</label>
                                <select name="tipo_servicio" id="tipo_servicio" required>
                                    <?php while ($servicio = $servicios->fetch_assoc()) { ?>
                                        <option value="<?= htmlspecialchars($servicio['id']) ?>"><?= htmlspecialchars($servicio['nombre']) ?></option>
                                    <?php } ?>
                                </select>

                                <label for="repuestos">Repuestos:</label>
                                <div class="checkbox-container">
                                    <?php while ($repuesto = $repuestos->fetch_assoc()) {
                                        $class = "";
                                        if ($repuesto['cantidad'] == 0) {
                                            $class = "out-of-stock";
                                        } elseif ($repuesto['cantidad'] <= 2) { // Ajusta el valor según tus necesidades
                                            $class = "low-stock";
                                        }
                                    ?>
                                        <div class="checkbox-item <?= $class ?>">
                                            <input type="checkbox" name="repuestos[]" value="<?= htmlspecialchars($repuesto['id']) ?>">
                                            <label><?= htmlspecialchars($repuesto['nombre_pieza']) ?> (Disponible: <?= htmlspecialchars($repuesto['cantidad']) ?>)</label>
                                        </div>
                                    <?php } ?>
                                </div>

                                <label for="estado">Estado:</label>
                                <select name="estado" id="estado" required>
                                    <option value="Operativo">Operativo</option>
                                    <option value="Inoperativo">Inoperativo</option>
                                </select>

                               

                                <label for="sugerencias">Sugerencias:</label>
                                <textarea name="sugerencias" id="sugerencias"></textarea>

                                <button type="submit">Registrar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin Modal -->
            
            <div id="tablaTemporal"></div>
        </section>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
