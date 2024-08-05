<?php
$conn = new mysqli("localhost", "root", "", "transporte5");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT i.*, v.marca, v.placa, p.nombre, p.apellido, p.dni, r.ubicacionInicial, r.ubicacionFinal 
                        FROM itinerarios i 
                        JOIN vehiculos v ON i.vehiculo_id = v.id 
                        JOIN personal p ON i.personal_id = p.id
                        JOIN rutas r ON i.ruta_id = r.ruta_id");

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['marca'] . ' - ' . $row['placa'],
        'start' => $row['fecha'] . 'T' . $row['h_inicio'],
        'end' => $row['fecha'] . 'T' . $row['h_final'],
        'description' => $row['ubicacionInicial'] . ' - ' . $row['ubicacionFinal'],
        'extendedProps' => [
            'chofer' => $row['nombre'] . ' ' . $row['apellido'] . ' - ' . $row['dni']
        ]
    ];
}

$conn->close();
echo json_encode($events);
?>
