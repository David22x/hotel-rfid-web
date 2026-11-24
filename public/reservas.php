<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controller/ReservaController.php';

$controller = new ReservaController();
$controller->atender();
