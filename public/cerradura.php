<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controller/CerraduraController.php';

$controller = new CerraduraController();
$controller->atender();
