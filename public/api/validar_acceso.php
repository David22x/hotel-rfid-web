<?php
// Recibe por POST: csrf_token, habitacion_id y uid.
// Responde en JSON si la cerradura concede o niega el acceso.

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/service/CerraduraService.php';
require_once __DIR__ . '/../../app/service/BitacoraService.php';

header('Content-Type: application/json; charset=utf-8');

// La respuesta es JSON, por eso no se usa Sesion::exigirPermiso, que redirige a otra página
if (!Sesion::estaAutenticado() || !PermisoService::tienePermiso(Sesion::usuario()['rol'], 'CERRADURA_RFID')) {
    echo json_encode(['ok' => false, 'error' => 'No tiene permiso para usar la cerradura.']);
    exit;
}

try {
    Csrf::validar();

    $uid = $_POST['uid'] ?? '';
    $habitacionId = (int) ($_POST['habitacion_id'] ?? 0);

    $cerradura = new CerraduraService();
    $respuesta = $cerradura->validar($uid, $habitacionId);

    $bitacora = new BitacoraService();
    $respuesta['ok'] = true;
    $respuesta['uid'] = strtoupper(trim($uid));
    $respuesta['ultimoEvento'] = $bitacora->describirUltimo($habitacionId);

    echo json_encode($respuesta);
} catch (ReglaNegocioException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Error al validar la tarjeta.']);
}
