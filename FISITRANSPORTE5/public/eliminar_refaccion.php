<?php
include '../config/config.php'; // Incluir el archivo de configuración

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $conn = getDBConnection(); // Obtener la conexión a la base de datos

    $query = "DELETE FROM repuestos WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<div class='message'>Pieza de repuesto eliminada exitosamente.</div>";
    } else {
        echo "<div class='message'>Error al eliminar la pieza de repuesto: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $conn->close();
    
    // Redirigir a la página de inventario
    header("Location: inventario_refacciones.php");
    exit();
} else {
    echo "<div class='message'>ID de la pieza no proporcionado.</div>";
}
?>
