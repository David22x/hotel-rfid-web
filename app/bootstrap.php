<?php

// Configuración local del proyecto
require_once __DIR__ . '/config/config.php';

// Zona horaria utilizada por el hotel
date_default_timezone_set(ZONA_HORARIA);

// Configuración de errores
if (MOSTRAR_ERRORES) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

// Iniciar la sesión una sola vez
if (!defined('SIN_SESION') && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clases comunes
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/ReglaNegocioException.php';
require_once __DIR__ . '/core/Sesion.php';
require_once __DIR__ . '/core/Csrf.php';

// Modelos relacionados con usuarios
require_once __DIR__ . '/model/Usuario.php';
require_once __DIR__ . '/model/RolUsuario.php';
require_once __DIR__ . '/model/EstadoReserva.php';
require_once __DIR__ . '/model/EstadoHabitacion.php';
require_once __DIR__ . '/model/EstadoLlave.php';
require_once __DIR__ . '/model/TipoEvento.php';
require_once __DIR__ . '/model/Reserva.php';
require_once __DIR__ . '/model/Llave.php';
require_once __DIR__ . '/model/Habitacion.php';
require_once __DIR__ . '/model/Huesped.php';
require_once __DIR__ . '/model/Evento.php';

// Persistencia de usuarios
require_once __DIR__ . '/dao/UsuarioDAO.php';

require_once __DIR__ . '/service/PermisoService.php';
