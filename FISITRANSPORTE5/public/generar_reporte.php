<?php
require('../vendor/fpdf186/fpdf.php'); // Ajusta la ruta según tu estructura de carpetas
require_once '../config/config.php';

// Clase personalizada para generar el PDF
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Título
        $this->Cell(0, 10, 'Reporte General de Tablas', 0, 1, 'C');
        // Salto de línea
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        // Posición a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Función para generar una tabla
    function BasicTable($header, $data)
    {
        // Cabecera
        foreach ($header as $col)
            $this->Cell(40, 7, $col, 1);
        $this->Ln();
        // Datos
        foreach ($data as $row) {
            foreach ($row as $col)
                $this->Cell(40, 6, $col, 1);
            $this->Ln();
        }
    }
}

// Crear el objeto PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$conn = getDBConnection();
$tables = ['reporte', 'vehiculos', 'personal', 'solicitudes_alquiler'];

foreach ($tables as $table) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, strtoupper($table), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12);

    $result = $conn->query("SELECT * FROM $table");
    if ($result->num_rows > 0) {
        $header = array_keys($result->fetch_assoc());
        $result->data_seek(0);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $pdf->BasicTable($header, $data);
    } else {
        $pdf->Cell(0, 10, 'No data available', 0, 1, 'L');
    }

    $pdf->Ln(10); // Agregar un salto de línea entre tablas
}

$conn->close();
$pdf->Output();
?>
