<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['mantenimientos'])) {
    $_SESSION['mantenimientos'] = [];
}

$_SESSION['mantenimientos'][] = $data;

echo json_encode(['message' => 'Mantenimiento guardado en la sesión']);
?>
