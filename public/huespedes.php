<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controller/HuespedController.php';

$controller = new HuespedController();
$controller->atender();
