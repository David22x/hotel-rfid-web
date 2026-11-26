<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/service/CerraduraService.php';
require_once __DIR__ . '/../../app/service/BitacoraService.php';

header('Content-Type: application/json; charset=utf-8');

$usuario = usuarioActual();
if ($usuario === null || !tienePermiso($usuario['rol'], 'CERRADURA_RFID')) {
    echo json_encode(['ok' => false, 'error' => 'No tiene permiso para usar la cerradura.']);
    exit;
}

try {
    $uid = $_POST['uid'] ?? '';
    $habitacionId = (int) ($_POST['habitacion_id'] ?? 0);

    $cerradura = new CerraduraService();
    $respuesta = $cerradura->validar($uid, $habitacionId);

    $bitacora = new BitacoraService();
    $respuesta['ok'] = true;
    $respuesta['uid'] = strtoupper(trim($uid));
    $respuesta['ultimoEvento'] = $bitacora->describirUltimo($habitacionId);

    echo json_encode($respuesta);
} catch (Exception $e) {
    $error = get_class($e) === 'Exception' ? $e->getMessage() : 'Error al validar la tarjeta.';
    echo json_encode(['ok' => false, 'error' => $error]);
}
