<?php
include '../config/config.php';
$conn = getDBConnection();

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $query = "SELECT id, vehiculo_id, estado FROM reporte WHERE fecha_mantenimiento = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<label for='reporte_id'>Seleccione el reporte:</label>";
        echo "<select name='reporte_id' id='reporte_id'>";
        while ($row = $result->fetch_assoc()) {
            $vehiculo = $conn->query("SELECT placa FROM vehiculos WHERE id = " . $row['vehiculo_id'])->fetch_assoc();
            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($vehiculo['placa']) . " - " . htmlspecialchars($row['estado']) . "</option>";
        }
        echo "</select>";
    } else {
        echo "No se encontraron reportes para la fecha seleccionada.";
    }

    $stmt->close();
}

$conn->close();
?>
