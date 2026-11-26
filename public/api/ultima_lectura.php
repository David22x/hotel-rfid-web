<?php
// GET /api/ultima_lectura.php   (sesión iniciada con permiso de check-in)
// Entrega una sola vez el UID leído en los últimos 60 s, o {"uid":null}.
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/core/ApiRfid.php';
require_once __DIR__ . '/../../app/dao/LecturaRfidDAO.php';

ApiRfid::exigirMetodo('GET');

$usuario = Sesion::usuario();
if ($usuario === null) {
    ApiRfid::responder(401, ['error' => 'Sesión no iniciada.']);
}
if (!PermisoService::tienePermiso($usuario['rol'], 'CHECK_IN_OUT')) {
    ApiRfid::responder(403, ['error' => 'Sin permiso.']);
}
session_write_close(); // no bloquear otras peticiones mientras la página consulta cada segundo

try {
    $desde = (new DateTimeImmutable())->modify('-60 seconds')->format('Y-m-d H:i:s');
    ApiRfid::responder(200, ['uid' => (new LecturaRfidDAO())->tomarPendiente($desde)]);
} catch (Throwable $e) {
    error_log('api/ultima_lectura: ' . $e->getMessage());
    ApiRfid::responder(500, ['error' => 'No se pudo consultar la lectura.']);
}
