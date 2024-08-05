<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM horarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $horario = $result->fetch_assoc();
    } else {
        $horario = null;
        $mensaje = "Horario no encontrado.";
    }
    $stmt->close();
    $conn->close();
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE horarios SET nombre = ?, hora_inicio = ?, hora_fin = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nombre, $hora_inicio, $hora_fin, $id);
    if ($stmt->execute()) {
        $mensaje = "Horario actualizado exitosamente.";
        header("Location: programacion_horario.php");
        exit();
    } else {
        $mensaje = "Error al actualizar el horario: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: programacion_horario.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Horario - UNAP</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <a href="programacion_horario.php" class="back-button">Regresar</a>
    <main>
        <section class="section">
            <h2>Editar Horario</h2>
            <?php
            if (isset($mensaje)) {
                echo '<p class="message">' . $mensaje . '</p>';
            }
            ?>
            <?php if ($horario): ?>
                <form action="editar_horario.php" method="POST" class="form-container">
                    <input type="hidden" name="id" value="<?php echo $horario['id']; ?>">
                    <label for="nombre">Nombre del Horario:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($horario['nombre']); ?>" required>

                    <label for="hora_inicio">Hora de Inicio:</label>
                    <input type="time" id="hora_inicio" name="hora_inicio" value="<?php echo htmlspecialchars($horario['hora_inicio']); ?>" required>

                    <label for="hora_fin">Hora de Fin:</label>
                    <input type="time" id="hora_fin" name="hora_fin" value="<?php echo htmlspecialchars($horario['hora_fin']); ?>" required>

                    <button type="submit">Actualizar Horario</button>
                </form>
            <?php else: ?>
                <p class="message">Horario no encontrado.</p>
            <?php endif; ?>
        </section>
    </main>
    <?php include '../src/views/footer.php'; ?>
</body>
</html>
