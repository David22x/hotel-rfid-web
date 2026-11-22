<?php
// Página del módulo Habitaciones. Ejemplos de URL:
//   habitaciones.php                         -> listado
//   habitaciones.php?accion=crear            -> formulario nuevo
//   habitaciones.php?accion=editar&id=3      -> formulario de edición
//   habitaciones.php?accion=cambiarEstado&id=3

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controller/HabitacionController.php';

// La sesión guarda los mensajes entre una redirección y la siguiente página
session_start();

$controller = new HabitacionController();
$controller->atender();
