<?php
// Página del módulo Huéspedes. Ejemplos de URL:
//   huespedes.php                      -> listado
//   huespedes.php?buscar=Torres        -> listado filtrado
//   huespedes.php?accion=crear         -> formulario nuevo
//   huespedes.php?accion=editar&id=3   -> formulario de edición

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controller/HuespedController.php';

// La sesión guarda los mensajes entre una redirección y la siguiente página
session_start();

$controller = new HuespedController();
$controller->atender();
