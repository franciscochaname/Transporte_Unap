<?php
require_once '../config/config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$conn = getDBConnection();

// Eliminar vehículo de la base de datos
$stmt = $conn->prepare("DELETE FROM vehiculos WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    $mensaje = "Vehículo eliminado exitosamente.";
} else {
    $mensaje = "Error al eliminar el vehículo: " . $stmt->error;
}
$stmt->close();
$conn->close();

header("Location: gestion_unidades.php?mensaje=" . urlencode($mensaje));
exit();
?>
