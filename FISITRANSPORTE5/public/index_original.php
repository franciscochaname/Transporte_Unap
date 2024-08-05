<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/src/controllers/TransporteController.php';

$controller = new TransporteController();
$controller->index();
?>

