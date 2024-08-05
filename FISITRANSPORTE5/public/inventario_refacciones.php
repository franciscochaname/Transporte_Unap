<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include '../config/config.php'; // Incluir el archivo de configuración

$conn = getDBConnection(); // Obtener la conexión a la base de datos
$proveedores = $conn->query("SELECT id, nombre FROM proveedores");
$repuestos = $conn->query("SELECT r.*, p.nombre AS proveedor_nombre FROM repuestos r LEFT JOIN proveedores p ON r.proveedor_id = p.id");
$repuestosArray = $repuestos->fetch_all(MYSQLI_ASSOC); // Guardar los datos en una variable
$conn->close();

$mensaje = "";
$mensaje_tipo = "";

// Procesar el formulario de agregar/editar refacción
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    $nombre_pieza = $_POST['nombre_pieza'];
    $proveedor_id = $_POST['proveedor_id'];
    $costo = $_POST['costo'];
    $cantidad = $_POST['cantidad'];
    $accion = $_POST['accion'];
    $pieza_id = $_POST['id'];

    // Ajustar valores fuera de los límites
    $costo = max(1, min(5000, $costo));
    $cantidad = max(1, min(500, $cantidad));

    if ($accion == 'editar' && !empty($pieza_id)) {
        // Editar refacción existente
        $stmt = $conn->prepare("UPDATE repuestos SET proveedor_id = ?, nombre_pieza = ?, costo = ?, cantidad = ? WHERE id = ?");
        $stmt->bind_param("isdii", $proveedor_id, $nombre_pieza, $costo, $cantidad, $pieza_id);
        if ($stmt->execute()) {
            $mensaje = "Refacción actualizada exitosamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al actualizar la refacción: " . $stmt->error;
            $mensaje_tipo = "error";
        }
    } else {
        // Agregar nueva refacción
        $stmt = $conn->prepare("INSERT INTO repuestos (proveedor_id, nombre_pieza, costo, cantidad) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isdi", $proveedor_id, $nombre_pieza, $costo, $cantidad);
        if ($stmt->execute()) {
            $mensaje = "Refacción agregada exitosamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al agregar la refacción: " . $stmt->error;
            $mensaje_tipo = "error";
        }
    }
    $stmt->close();
    $conn->close();
    header("Location: inventario_refacciones.php");
    exit();
}

// Eliminar refacción de la base de datos
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $conn = getDBConnection();
    $pieza_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM repuestos WHERE id = ?");
    $stmt->bind_param("i", $pieza_id);

    if ($stmt->execute()) {
        $mensaje = "Refacción eliminada exitosamente.";
        $mensaje_tipo = "exito";
    } else {
        $mensaje = "Error al eliminar la refacción: " . $stmt->error;
        $mensaje_tipo = "error";
    }

    $stmt->close();
    $conn->close();
    header("Location: inventario_refacciones.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Refacciones</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/boton.css">
    <link rel="stylesheet" href="css/gestion_unidades.css">
    <style>
        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
        }

        .mensaje-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
        }

        .btn-toggle {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            color: #ffffff;
            display: inline-block;
        }

        .btn-toggle.habilitado {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-toggle.deshabilitado {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .disabled {
            pointer-events: none;
            opacity: 0.6;
        }

        .estado-operativo::after {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-left: 5px;
            background-color: #006400; /* Verde oscuro */
            border-radius: 50%;
        }

        .estado-inoperativo::after {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-left: 5px;
            background-color: red;
            border-radius: 50%;
        }

        .estado-null::after {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-left: 5px;
            background-color: green;
            border-radius: 50%;
        }

        .no-results {
            color: red;
            font-weight: bold;
        }

        .message {
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .back-button {
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #FFD700; /* Amarillo dorado */
            color: #006400; /* Verde oscuro */
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .back-button:hover {
            background-color: #e0c200;
        }

        .section {
            margin: 20px;
        }

        .btn-primary {
            background-color: #006400;
            border-color: #006400;
        }

        .btn-primary:hover {
            background-color: #004d00;
            border-color: #004d00;
        }

        .btn-warning {
            background-color: #FFD700;
            border-color: #FFD700;
            color: #006400;
        }

        .btn-warning:hover {
            background-color: #e0c200;
            border-color: #e0c200;
            color: #004d00;
        }

        .btn-danger {
            background-color: #ff4c4c;
            border-color: #ff4c4c;
        }

        .btn-danger:hover {
            background-color: #e04343;
            border-color: #e04343;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 100, 0, 0.05);
        }

        .thead-dark th {
            background-color: #006400;
            color: white;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .form-control {
            border-radius: 5px;
        }

        input#buscarPieza {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="d-flex">
        <div class="container-fluid mt-4" id="main-content">
            <main>
                <section class="section">
                    <h2 class="text-center mb-4">Inventario de Refacciones</h2>
                    <?php if (!empty($mensaje)) { ?>
                        <p class="text-center <?php echo $mensaje_tipo == 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>"><?php echo $mensaje; ?></p>
                    <?php } ?>
                    <div class="d-flex justify-content-between mb-4">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#piezaModal" onclick="openAddModal()">
                            Agregar Refacción
                        </button>
                        <input type="text" id="buscarPieza" onkeyup="filtrarPiezas()" class="form-control w-50" placeholder="Buscar por cualquier campo...">
                    </div>

                    <!-- Modal de Bootstrap para agregar/editar refacción -->
                    <div class="modal fade" id="piezaModal" tabindex="-1" role="dialog" aria-labelledby="piezaModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="piezaModalLabel">Agregar Refacción</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="piezaForm" action="inventario_refacciones.php" method="POST">
                                        <input type="hidden" id="accion" name="accion" value="agregar">
                                        <input type="hidden" id="pieza_id" name="id">

                                        <div class="form-group">
                                            <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                                                <option value="" disabled selected>Seleccione un proveedor</option>
                                                <?php while ($proveedor = $proveedores->fetch_assoc()) { ?>
                                                    <option value="<?= $proveedor['id'] ?>"><?= htmlspecialchars($proveedor['nombre']) ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="nombre_pieza" id="nombre_pieza" class="form-control" placeholder="Nombre de la Refacción" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="number" step="0.01" name="costo" id="costo" class="form-control" placeholder="Precio unitario (S/)" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="number" name="cantidad" id="cantidad" class="form-control" placeholder="Cantidad" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Guardar Refacción</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($repuestosArray)): ?>
                        <p class="no-results text-center">No se encontraron refacciones.</p>
                    <?php else: ?>
                        <table class="table table-striped" id="tablaPiezas">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Nombre de la Refacción</th>
                                    <th>Precio unitario (S/)</th>
                                    <th>Cantidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($repuestosArray as $repuesto) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($repuesto['proveedor_nombre']) ?></td>
                                        <td><?= htmlspecialchars($repuesto['nombre_pieza']) ?></td>
                                        <td><?= htmlspecialchars($repuesto['costo']) ?></td>
                                        <td><?= htmlspecialchars($repuesto['cantidad']) ?></td>
                                        <td>
                                            <button onclick="openEditModal('<?= $repuesto['id'] ?>', '<?= htmlspecialchars($repuesto['proveedor_id']) ?>', '<?= htmlspecialchars($repuesto['nombre_pieza']) ?>', '<?= htmlspecialchars($repuesto['costo']) ?>', '<?= htmlspecialchars($repuesto['cantidad']) ?>')" class="btn btn-warning btn-sm">Editar</button>
                                            <button onclick="openDeleteModal('<?= $repuesto['id'] ?>')" class="btn btn-danger btn-sm">Eliminar</button>
                                        </td>
                                    </tr>
                                <?php } ?>
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
                    <p>¿Realmente deseas eliminar esta refacción? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('piezaForm').action = 'inventario_refacciones.php';
            document.getElementById('accion').value = 'agregar';
            document.getElementById('pieza_id').value = '';
            document.getElementById('proveedor_id').value = '';
            document.getElementById('nombre_pieza').value = '';
            document.getElementById('costo').value = '';
            document.getElementById('cantidad').value = '';
            document.getElementById('piezaModalLabel').innerText = 'Agregar Refacción';
            $('#piezaModal').modal('show');
        }

        function openEditModal(id, proveedorId, nombrePieza, costo, cantidad) {
            document.getElementById('piezaForm').action = 'inventario_refacciones.php';
            document.getElementById('accion').value = 'editar';
            document.getElementById('pieza_id').value = id;
            document.getElementById('proveedor_id').value = proveedorId;
            document.getElementById('nombre_pieza').value = nombrePieza;
            document.getElementById('costo').value = costo;
            document.getElementById('cantidad').value = cantidad;
            document.getElementById('piezaModalLabel').innerText = 'Editar Refacción';
            $('#piezaModal').modal('show');
        }

        function openDeleteModal(id) {
            $('#deleteModal').modal('show');
            document.getElementById('confirmDeleteBtn').onclick = function () {
                window.location.href = 'inventario_refacciones.php?accion=eliminar&id=' + id;
            };
        }

        function filtrarPiezas() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById('buscarPieza');
            filter = input.value.toUpperCase();
            table = document.getElementById('tablaPiezas');
            tr = table.getElementsByTagName('tr');
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = 'none';
                td = tr[i].getElementsByTagName('td');
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = '';
                            break;
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
