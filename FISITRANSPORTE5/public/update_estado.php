<?php
include '../config/config.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $estado = $_POST['estado'];

    $stmt = $conn->prepare("UPDATE solicitudes_alquiler SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id);

    if ($stmt->execute()) {
        echo "Estado actualizado exitosamente.";
    } else {
        echo "Error al actualizar el estado: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
