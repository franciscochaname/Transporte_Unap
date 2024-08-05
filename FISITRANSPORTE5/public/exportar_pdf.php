<?php
require_once('../config/config.php');
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php'); // Asegúrate de que esta ruta es correcta

// Verificar si se ha pasado un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$conn = getDBConnection();
$id = intval($_GET['id']);

$query = $conn->prepare("SELECT * FROM solicitudes_alquiler WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die("No se encontró la solicitud.");
}

$solicitud = $result->fetch_assoc();

// Crear una instancia de TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('UNAP');
$pdf->SetTitle('Solicitud de Alquiler');
$pdf->SetSubject('Detalle de la Solicitud de Alquiler');
$pdf->SetKeywords('TCPDF, PDF, solicitud, alquiler, UNAP');

// Configurar la información del documento
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Agregar una página
$pdf->AddPage();

// Establecer la fuente
$pdf->SetFont('helvetica', '', 12);

// Contenido del PDF
$html = '
<h1>Solicitud de Alquiler</h1>
<table border="1" cellpadding="4">
    <tr>
        <th>RUC</th>
        <td>' . htmlspecialchars($solicitud['ruc']) . '</td>
    </tr>
    <tr>
        <th>Razón Social o Nombre</th>
        <td>' . htmlspecialchars($solicitud['razon_social_o_nombre']) . '</td>
    </tr>
    <tr>
        <th>Dirección</th>
        <td>' . htmlspecialchars($solicitud['direccion']) . '</td>
    </tr>
    <tr>
        <th>Teléfono</th>
        <td>' . htmlspecialchars($solicitud['telefono']) . '</td>
    </tr>
    <tr>
        <th>Tiempo de Alquiler</th>
        <td>' . htmlspecialchars($solicitud['tiempo_alquiler']) . '</td>
    </tr>
    <tr>
        <th>Fecha</th>
        <td>' . htmlspecialchars($solicitud['fecha']) . '</td>
    </tr>
    <tr>
        <th>Visto</th>
        <td>' . ($solicitud['visto'] ? 'Sí' : 'No') . '</td>
    </tr>
</table>
';

// Imprimir el contenido
$pdf->writeHTML($html, true, false, true, false, '');

// Cerrar y generar el PDF
$pdf->Output('solicitud_alquiler_' . $id . '.pdf', 'I');
?>
