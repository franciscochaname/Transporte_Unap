<?php
$conn = new mysqli("localhost", "root", "", "transporte5");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT i.id, r.ubicacionInicial, r.ubicacionFinal, i.fecha, i.h_inicio, i.h_final, p.nombre, p.apellido, v.marca, v.placa 
                        FROM itinerarios i 
                        JOIN vehiculos v ON i.vehiculo_id = v.id 
                        JOIN personal p ON i.personal_id = p.id
                        JOIN rutas r ON i.ruta_id = r.ruta_id");

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'title' => $row['ubicacionInicial'] . ' - ' . $row['ubicacionFinal'],
        'start' => $row['fecha'] . 'T' . $row['h_inicio'],
        'end' => $row['fecha'] . 'T' . $row['h_final'],
        'description' => 'Chofer: ' . $row['nombre'] . ' ' . $row['apellido'] . ' - Unidad: ' . $row['marca'] . ' - ' . $row['placa']
    ];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($events);
?>
