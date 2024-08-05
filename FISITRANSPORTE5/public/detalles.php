<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/config.php';

$tipo = $_GET['tipo'] ?? '';
$conn = getDBConnection();
$data = [];

switch ($tipo) {
    case 'reportes':
        $query = "SELECT r.id, r.nombre_mecanico AS mecanico, r.fecha_mantenimiento, s.nombre AS tipo_servicio, 
                         v.marca AS vehiculo, v.placa AS placa,
                         GROUP_CONCAT(rep.nombre_pieza SEPARATOR ', ') AS repuestos,
                         SUM(rep.costo) AS precio_total,
                         r.sugerencias, r.fecha_ingreso, r.estado 
                  FROM transporte.reporte r
                  LEFT JOIN transporte.servicios s ON r.tipo_servicio = s.id
                  LEFT JOIN transporte.vehiculos v ON r.vehiculo_id = v.id
                  LEFT JOIN transporte.repuestos rep ON FIND_IN_SET(rep.id, r.repuestos)
                  GROUP BY r.id";
        break;
    case 'vehículos':
        $query = "SELECT * FROM transporte.vehiculos";
        break;
    case 'personal':
        $query = "SELECT id, nombre, apellido, dni, rol, vehiculo_id, horario_id FROM transporte.personal"; // Excluir usuario y password
        break;
    case 'solicitudes':
        $query = "SELECT * FROM transporte.solicitudes_alquiler";
        break;
    default:
        $query = "";
        break;
}

if ($query) {
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de <?= ucfirst($tipo) ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="vendor/fpdf186/fpdf.css">
    <style>
        .search-container {
            margin-bottom: 20px;
            text-align: right;
        }
        .search-container input {
            width: 300px;
            padding: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3">Regresar al Dashboard</a>
        <h2 class="text-center">Detalle de <?= ucfirst($tipo) ?></h2>
        <div class="search-container">
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Buscar en la tabla...">
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if (!empty($data)): ?>
                            <?php foreach (array_keys($data[0]) as $key): ?>
                                <th><?= ucfirst(str_replace("_", " ", $key)) ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="dataTable">
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($row as $key => $value): ?>
                                <?php if ($key == 'precio_total'): ?>
                                    <td>S/ <?= number_format($value, 2) ?></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($value ?? '') ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="chart-container mt-5">
            <canvas id="detailChart"></canvas>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('detailChart').getContext('2d');
            var detailChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['<?= ucfirst($tipo) ?>'],
                    datasets: [{
                        label: 'Cantidad de <?= ucfirst($tipo) ?>',
                        data: [<?= count($data) ?>],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        function filterTable() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("dataTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                tr[i].style.display = "none"; // Ocultar todas las filas inicialmente
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = ""; // Mostrar la fila si encuentra coincidencias
                            break; // No necesita continuar revisando otras celdas
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
