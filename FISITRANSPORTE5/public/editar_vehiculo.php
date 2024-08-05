<?php
require_once '../config/config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$conn = getDBConnection();

// Actualizar vehículo en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $marca = $_POST['marca'];
    $placa = $_POST['placa'];
    $ruta = $_POST['ruta'];
    $horario = $_POST['horario'];

    $stmt = $conn->prepare("UPDATE vehiculos SET marca = ?, placa = ?, ruta = ?, horario = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $marca, $placa, $ruta, $horario, $id);
    if ($stmt->execute()) {
        header("Location: gestion_unidades.php?mensaje=Vehículo actualizado exitosamente.");
        exit();
    } else {
        $mensaje = "Error al actualizar el vehículo: " . $stmt->error;
    }
    $stmt->close();
}

// Obtener datos del vehículo
$stmt = $conn->prepare("SELECT * FROM vehiculos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$vehiculo = $result->fetch_assoc();
$stmt->close();

// Obtener horarios de la base de datos
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
    <title>Editar Vehículo - UNAP</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <main>
        <section class="section">
            <h2>Editar Vehículo</h2>
            <?php
            if (isset($mensaje)) {
                echo '<p class="message">' . $mensaje . '</p>';
            }
            ?>
            <form action="editar_vehiculo.php?id=<?php echo $id; ?>" method="POST" class="form-container">
                <label for="marca">Marca de Carro:</label>
                <select id="marca" name="marca" required>
                    <option value="Hyundai" <?php if ($vehiculo['marca'] == 'Hyundai') echo 'selected'; ?>>Hyundai</option>
                    <option value="Toyota" <?php if ($vehiculo['marca'] == 'Toyota') echo 'selected'; ?>>Toyota</option>
                    <option value="Mercedes Benz" <?php if ($vehiculo['marca'] == 'Mercedes Benz') echo 'selected'; ?>>Mercedes Benz</option>
                </select>

                <label for="placa">Número de Placa:</label>
                <input type="text" id="placa" name="placa" value="<?php echo htmlspecialchars($vehiculo['placa']); ?>" required>

                <label for="ruta">Ruta:</label>
                <select id="ruta" name="ruta" required>
                    <option value="Plaza Ponce-Zungarococha" <?php if ($vehiculo['ruta'] == 'Plaza Ponce-Zungarococha') echo 'selected'; ?>>Plaza Ponce-Zungarococha</option>
                    <option value="Plaza Ponce-Rectorado" <?php if ($vehiculo['ruta'] == 'Plaza Ponce-Rectorado') echo 'selected'; ?>>Plaza Ponce-Rectorado</option>
                    <option value="Ruta libre" <?php if ($vehiculo['ruta'] == 'Ruta libre') echo 'selected'; ?>>Ruta libre</option>
                    <option value="Ruta especial" <?php if ($vehiculo['ruta'] == 'Ruta especial') echo 'selected'; ?>>Ruta especial</option>
                </select>

                <label for="horario">Horario:</label>
                <select id="horario" name="horario" required>
                    <?php foreach ($horarios as $horario): ?>
                        <option value="<?php echo htmlspecialchars($horario['nombre']); ?>" <?php if ($vehiculo['horario'] == $horario['nombre']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($horario['nombre'] . ' (' . $horario['hora_inicio'] . ' - ' . $horario['hora_fin'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Actualizar Vehículo</button>
            </form>
            <a href="gestion_unidades.php" class="back-button">Regresar</a>
        </section>
    </main>
    <?php include '../src/views/footer.php'; ?>
</body>
</html>
