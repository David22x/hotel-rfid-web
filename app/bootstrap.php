<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/sesion.php';

date_default_timezone_set(ZONA_HORARIA);
ini_set('display_errors', MOSTRAR_ERRORES ? '1' : '0');
error_reporting(E_ALL);

// Protección CSRF: la cookie de sesión no se envía desde otros sitios
if (PHP_SAPI !== 'cli') {
    session_start(['cookie_samesite' => 'Strict']);
}
