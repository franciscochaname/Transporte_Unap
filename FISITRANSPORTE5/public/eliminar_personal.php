<?php
include '../config/config.php'; // Incluir el archivo de configuración

$id = $_GET['id'];
$conn = getDBConnection(); // Obtener la conexión a la base de datos

$query = "DELETE FROM personal WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo "<div class='message'>Personal eliminado exitosamente.</div>";
} else {
    echo "<div class='message'>Error al eliminar el personal: " . $stmt->error . "</div>";
}
$stmt->close();
$conn->close();
header("Location: gestion_personal.php");
exit();
?>
