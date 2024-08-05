<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/config.php';

// Conexión a la base de datos
$conn = getDBConnection();

// Consultas a la base de datos
$queries = [
    "reporte" => "SELECT COUNT(*) AS total FROM reporte",
    "vehiculos" => "SELECT COUNT(*) AS total FROM vehiculos",
    "personal" => "SELECT COUNT(*) AS total FROM personal",
    "solicitudes" => "SELECT COUNT(*) AS total FROM solicitudes_alquiler",
    "costos" => "SELECT SUM(costo) AS total FROM repuestos",
    "fechas" => "SELECT COUNT(*) AS total FROM reporte WHERE fecha_mantenimiento IS NOT NULL",
    "materiales" => "SELECT nombre_pieza, SUM(cantidad) AS total FROM repuestos GROUP BY nombre_pieza",
    "servicios" => "SELECT tipo_servicio, COUNT(*) AS total FROM reporte GROUP BY tipo_servicio"
];

$results = [];
foreach ($queries as $key => $query) {
    try {
        $result = $conn->query($query);
        if ($result) {
            if ($key == 'materiales' || $key == 'servicios') {
                $results[$key] = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $results[$key] = $result->fetch_assoc()['total'];
            }
        } else {
            $results[$key] = 0; // Asignar un valor por defecto si la consulta falla
        }
    } catch (Exception $e) {
        $results[$key] = 0; // Asignar un valor por defecto si ocurre una excepción
        error_log("Error en la consulta $key: " . $e->getMessage());
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Gestión de Transporte</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/index.js" defer></script>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="container mt-5">
        <a href="area_administrativa.php" class="btn btn-secondary mb-3">Regresar al Área Administrativa</a>
        <h2 class="text-center">Dashboard de Gestión de Transporte</h2>
        <div class="dashboard-container">
            <?php 
            $cards = [
                "Reportes" => "primary",
                "Vehículos" => "success",
                "Personal" => "warning",
                "Solicitudes" => "danger"
            ];
            foreach ($cards as $key => $color) { ?>
                <div class="card text-white bg-<?= $color ?>" onclick="window.location.href='detalles.php?tipo=<?= strtolower($key) ?>'">
                    <div class="card-body">
                        <h5 class="card-title"><?= $key ?></h5>
                        
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="chart-container mb-4">
            <canvas id="reportesChart" data-value="<?= $results['reporte'] ?>"></canvas>
        </div>
        <div class="chart-container mb-4">
            <canvas id="vehiculosChart" data-value="<?= $results['vehiculos'] ?>"></canvas>
        </div>
        <div class="chart-container mb-4">
            <canvas id="personalChart" data-value="<?= $results['personal'] ?>"></canvas>
        </div>
        <div class="chart-container mb-4">
            <canvas id="solicitudesChart" data-value="<?= $results['solicitudes'] ?>"></canvas>
        </div>
        <div class="chart-container mb-4">
            <canvas id="generalStatsChart" data-values='<?= json_encode($results) ?>'></canvas>
        </div>
        <div class="text-center mt-4">
            <a href="generar_reporte.php" class="btn btn-primary">Descargar Reporte General en PDF</a>
        </div>
    </div>
</body>
</html>
