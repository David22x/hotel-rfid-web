<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controller/CheckInOutController.php';

$controller = new CheckInOutController();
$controller->atender();
