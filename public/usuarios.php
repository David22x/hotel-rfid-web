<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controller/UsuarioController.php';

$controlador = new UsuarioController();
$controlador->manejar();
