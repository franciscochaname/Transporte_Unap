<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include '../config/config.php'; // Asegúrate de ajustar la ruta según tu estructura de directorios

$conn = getDBConnection();

// Obtener los reportes de mantenimiento
$result = $conn->query("SELECT r.*, s.nombre AS servicio_nombre, v.marca, v.placa, GROUP_CONCAT(rep.nombre_pieza SEPARATOR ', ') AS repuestos, r.nombre_mecanico
                        FROM reporte r 
                        LEFT JOIN servicios s ON r.tipo_servicio = s.id 
                        LEFT JOIN vehiculos v ON r.vehiculo_id = v.id 
                        LEFT JOIN repuestos rep ON FIND_IN_SET(rep.id, r.repuestos) 
                        GROUP BY r.id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Mantenimiento</title>
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/mantenimiento.css">
    <style>
        .estado-operativo::after {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-left: 5px;
            background-color: green;
            border-radius: 50%;
        }

        .estado-inoperativo::after {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-left: 5px;
            background-color: red;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <main>
        <section class="section">
            <h2>Reporte de Mantenimiento</h2>
            <div class="search-container">
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Buscar por nombre de mecánico...">
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mecánico</th>
                            <th>Fecha de Mantenimiento</th>
                            <th>Tipo de Servicio</th>
                            <th>Vehículo</th>
                            <th>Repuestos</th>
                            <th>Sugerencias</th>
                            <th>Fecha de Ingreso</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="reporteTable">
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nombre_mecanico']) ?></td>
                                <td><?= htmlspecialchars($row['fecha_mantenimiento']) ?></td>
                                <td><?= htmlspecialchars($row['servicio_nombre']) ?></td>
                                <td><?= htmlspecialchars($row['marca'] . " - " . $row['placa']) ?></td>
                                <td><?= htmlspecialchars($row['repuestos']) ?></td>
                                <td><?= htmlspecialchars($row['sugerencias']) ?></td>
                                <td><?= htmlspecialchars($row['fecha_ingreso']) ?></td>
                                <td class="<?php 
                                    if ($row['estado'] == 'Operativo') echo 'estado-operativo';
                                    elseif ($row['estado'] == 'Inoperativo') echo 'estado-inoperativo';
                                ?>">
                                    <?= htmlspecialchars($row['estado']) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        function filterTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("reporteTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
