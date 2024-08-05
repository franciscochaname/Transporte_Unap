<?php
include '../config/config.php'; // Incluir el archivo de configuración

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $proveedor_id = $_POST['proveedor_id'];
    $nombre_pieza = $_POST['nombre_pieza'];
    $costo = $_POST['costo'];
    $cantidad = $_POST['cantidad'];

    $conn = getDBConnection(); // Obtener la conexión a la base de datos
    $query = "UPDATE repuestos SET proveedor_id=?, nombre_pieza=?, costo=?, cantidad=? WHERE id=?";
    $stmt = $conn->prepare($query);

    // Corregir la cadena de tipos a "issdi" y ajustar las variables de enlace
    $stmt->bind_param("issdi", $proveedor_id, $nombre_pieza, $costo, $cantidad, $id);

    if ($stmt->execute()) {
        echo "<div class='message'>Pieza de repuesto actualizada exitosamente.</div>";
    } else {
        echo "<div class='message'>Error al actualizar la pieza de repuesto: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();
    header("Location: inventario_refacciones.php");
    exit();
}

$id = $_GET['id'];
$conn = getDBConnection(); // Obtener la conexión a la base de datos
$query = "SELECT * FROM repuestos WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$repuesto = $result->fetch_assoc();

$proveedores = $conn->query("SELECT id, nombre FROM proveedores");
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Pieza de Repuesto</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <a href="inventario_refacciones.php" class="back-button">Regresar al Inventario de Refacciones</a>
    <main>
        <section class="section">
            <h2>Editar Pieza de Repuesto</h2>
            <form method="post" action="" class="form-container">
                <input type="hidden" name="id" value="<?= $repuesto['id'] ?>">

                <label for="proveedor_id">Nombre del Proveedor:</label>
                <select name="proveedor_id" id="proveedor_id" required>
                    <?php while ($proveedor = $proveedores->fetch_assoc()) { ?>
                        <option value="<?= $proveedor['id'] ?>" <?= $repuesto['proveedor_id'] == $proveedor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($proveedor['nombre']) ?></option>
                    <?php } ?>
                </select>

                <label for="nombre_pieza">Nombre de la Pieza:</label>
                <input type="text" name="nombre_pieza" id="nombre_pieza" value="<?= htmlspecialchars($repuesto['nombre_pieza']) ?>" required>

                <label for="costo">Costo (S/):</label>
                <input type="number" step="0.01" name="costo" id="costo" value="<?= htmlspecialchars($repuesto['costo']) ?>" required>

                <label for="cantidad">Cantidad:</label>
                <input type="number" name="cantidad" id="cantidad" value="<?= htmlspecialchars($repuesto['cantidad']) ?>" required>

                <button type="submit">Actualizar Pieza</button>
            </form>
        </section>
    </main>
    <?php include '../src/views/footer.php'; ?>
</body>
</html>
