<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controller/BitacoraController.php';

$controller = new BitacoraController();
$controller->atender();
