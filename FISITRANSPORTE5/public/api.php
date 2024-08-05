<?php
header('Content-Type: application/json');

if (isset($_GET['ruc'])) {
    $ruc = $_GET['ruc'];
    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImpvcmdlbHVpczI0NzNAZ21haWwuY29tIn0.Qk12bmlO0FHRGkOau8j6FBdYqcGJRc0sWpi6g73wRto';

    // Iniciar llamada a API
    $curl = curl_init();

    // Buscar ruc sunat
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://dniruc.apisperu.com/api/v1/ruc/' . $ruc . '?token=' . $token,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    // Datos de empresa según padron reducido
    $empresa = json_decode($response, true);

    if (isset($empresa['ruc'])) {
        echo json_encode(['success' => true, 'data' => $empresa]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos para el RUC ingresado.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'RUC no especificado.']);
}
?>
