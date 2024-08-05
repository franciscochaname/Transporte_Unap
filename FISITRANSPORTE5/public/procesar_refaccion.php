<?php
include '../config/config.php'; // Incluir el archivo de configuración

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $proveedor_id = $_POST['proveedor_id'];
    $nombre_pieza = $_POST['nombre_pieza'];
    $costo = $_POST['costo'];
    $cantidad = $_POST['cantidad'];

    // Verificar que los datos no están vacíos
    if (empty($proveedor_id) || empty($nombre_pieza) || empty($costo) || empty($cantidad)) {
        echo "<div class='message'>Todos los campos son obligatorios.</div>";
        exit();
    }

    $conn = getDBConnection(); // Obtener la conexión a la base de datos

    // Verificar si la pieza ya existe
    $query = "SELECT id, cantidad FROM repuestos WHERE nombre_pieza = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombre_pieza);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // La pieza ya existe, sumar la cantidad
        $stmt->bind_result($id, $existing_cantidad);
        $stmt->fetch();
        $nueva_cantidad = $existing_cantidad + $cantidad;

        $query = "UPDATE repuestos SET cantidad = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $nueva_cantidad, $id);
    } else {
        // La pieza no existe, insertarla
        $query = "INSERT INTO repuestos (proveedor_id, nombre_pieza, costo, cantidad) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issd", $proveedor_id, $nombre_pieza, $costo, $cantidad);
    }

    if ($stmt->execute()) {
        echo "<div class='message'>Pieza de repuesto agregada exitosamente.</div>";
    } else {
        echo "<div class='message'>Error al agregar la pieza de repuesto: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();
    header("Location: inventario_refacciones.php");
    exit();
}
?>
