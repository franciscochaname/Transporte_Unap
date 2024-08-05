<?php
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $visto = $_POST['visto'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE solicitudes_alquiler SET visto = ? WHERE id = ?");
    $stmt->bind_param("ii", $visto, $id);

    if ($stmt->execute()) {
        echo "Estado actualizado.";
    } else {
        echo "Error al actualizar el estado: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
