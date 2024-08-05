<?php
include '../config/config.php'; // Incluir el archivo de configuración

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection(); // Obtener la conexión a la base de datos

    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $rol = $_POST['rol'];
    $usuario = null;
    $password = null;
    $vehiculo_id = null;
    $horario_id = $_POST['horario_id'];

    if ($rol == 'mecanico') {
        $usuario = $_POST['usuario'];
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $query = "UPDATE personal SET nombre=?, apellido=?, dni=?, rol=?, usuario=?, password=?, vehiculo_id=?, horario_id=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssiii", $nombre, $apellido, $dni, $rol, $usuario, $password, $vehiculo_id, $horario_id, $id);
        } else {
            $query = "UPDATE personal SET nombre=?, apellido=?, dni=?, rol=?, usuario=?, vehiculo_id=?, horario_id=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssii", $nombre, $apellido, $dni, $rol, $usuario, $vehiculo_id, $horario_id, $id);
        }
    } elseif ($rol == 'chofer') {
        $vehiculo_id = $_POST['vehiculo_id'];
        $query = "UPDATE personal SET nombre=?, apellido=?, dni=?, rol=?, vehiculo_id=?, horario_id=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssiii", $nombre, $apellido, $dni, $rol, $vehiculo_id, $horario_id, $id);
    }

    if ($stmt->execute()) {
        echo "<div class='message'>Personal actualizado exitosamente.</div>";
    } else {
        echo "<div class='message'>Error al actualizar el personal: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();
    header("Location: gestion_personal.php");
    exit();
}

$id = $_GET['id'];
$conn = getDBConnection(); // Obtener la conexión a la base de datos
$query = "SELECT * FROM personal WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$persona = $result->fetch_assoc();

$vehiculos = $conn->query("SELECT id, marca, placa FROM vehiculos");
$horarios = $conn->query("SELECT id, nombre, hora_inicio, hora_fin FROM horarios");
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Personal</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <a href="gestion_personal.php" class="back-button">Regresar a Gestión de Personal</a>
    <main>
        <section class="section">
            <h2>Editar Personal</h2>
            <form method="post" action="" class="form-container">
                <input type="hidden" name="id" value="<?= $persona['id'] ?>">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($persona['nombre']) ?>" required>

                <label for="apellido">Apellido:</label>
                <input type="text" name="apellido" id="apellido" value="<?= htmlspecialchars($persona['apellido']) ?>" required>

                <label for="dni">DNI:</label>
                <input type="text" name="dni" id="dni" value="<?= htmlspecialchars($persona['dni']) ?>" required>

                <label for="rol">Rol:</label>
                <select name="rol" id="rol" required onchange="toggleFields()">
                    <option value="">Seleccione...</option>
                    <option value="mecanico" <?= $persona['rol'] == 'mecanico' ? 'selected' : '' ?>>Mecánico</option>
                    <option value="chofer" <?= $persona['rol'] == 'chofer' ? 'selected' : '' ?>>Chofer</option>
                </select>

                <div id="mecanico_fields" style="display:<?= $persona['rol'] == 'mecanico' ? 'block' : 'none' ?>;">
                    <label for="usuario">Usuario:</label>
                    <input type="text" name="usuario" id="usuario" value="<?= htmlspecialchars($persona['usuario'] ?? '') ?>">

                    <label for="password">Contraseña:</label>
                    <input type="password" name="password" id="password">
                </div>

                <div id="chofer_fields" style="display:<?= $persona['rol'] == 'chofer' ? 'block' : 'none' ?>;">
                    <label for="vehiculo_id">Asignar Vehículo:</label>
                    <select name="vehiculo_id" id="vehiculo_id">
                        <?php while ($vehiculo = $vehiculos->fetch_assoc()) { ?>
                            <option value="<?= $vehiculo['id'] ?>" <?= $persona['vehiculo_id'] == $vehiculo['id'] ? 'selected' : '' ?>><?= htmlspecialchars($vehiculo['marca']) ?> - <?= htmlspecialchars($vehiculo['placa']) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <label for="horario_id">Asignar Horario:</label>
                <select name="horario_id" id="horario_id" required>
                    <?php while ($horario = $horarios->fetch_assoc()) { ?>
                        <option value="<?= $horario['id'] ?>" <?= $persona['horario_id'] == $horario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($horario['nombre']) ?> (<?= htmlspecialchars($horario['hora_inicio']) ?> - <?= htmlspecialchars($horario['hora_fin']) ?>)</option>
                    <?php } ?>
                </select>

                <button type="submit">Actualizar</button>
            </form>
        </section>
    </main>
    <script>
        function toggleFields() {
            var rol = document.getElementById('rol').value;
            document.getElementById('mecanico_fields').style.display = (rol === 'mecanico') ? 'block' : 'none';
            document.getElementById('chofer_fields').style.display = (rol === 'chofer') ? 'block' : 'none';
        }
    </script>
    <?php include '../src/views/footer.php'; ?>
</body>
</html>
