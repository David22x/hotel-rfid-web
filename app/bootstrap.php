<?php

require_once __DIR__ . '/config/config.php';

date_default_timezone_set(ZONA_HORARIA);
ini_set('display_errors', MOSTRAR_ERRORES ? '1' : '0');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/ReglaNegocioException.php';
require_once __DIR__ . '/core/sesion.php';
require_once __DIR__ . '/model/Usuario.php';
require_once __DIR__ . '/model/Reserva.php';
require_once __DIR__ . '/model/Llave.php';
require_once __DIR__ . '/model/Habitacion.php';
require_once __DIR__ . '/model/Huesped.php';
require_once __DIR__ . '/model/Evento.php';
