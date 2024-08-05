<?php
include '../config/config.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM solicitudes_alquiler WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Solicitud eliminada exitosamente.";
    } else {
        echo "Error al eliminar la solicitud: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
