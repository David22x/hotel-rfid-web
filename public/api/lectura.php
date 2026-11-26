<?php
// POST /api/lectura.php  {"uid":"A3F1C20B"}   (Authorization: Bearer <RFID_TOKEN>)
// Lector de recepción: deja la lectura pendiente para el formulario de check-in.
define('SIN_SESION', true);
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/core/ApiRfid.php';
require_once __DIR__ . '/../../app/dao/LecturaRfidDAO.php';

ApiRfid::exigirMetodo('POST');
ApiRfid::exigirToken();

$uid = ApiRfid::uidValido(ApiRfid::datos()['uid'] ?? null);
if ($uid === null) {
    ApiRfid::responder(400, ['error' => 'UID inválido: se esperan de 4 a 10 bytes en hexadecimal.']);
}

try {
    (new LecturaRfidDAO())->guardar($uid, date('Y-m-d H:i:s'));
} catch (Throwable $e) {
    error_log('api/lectura: ' . $e->getMessage());
    ApiRfid::responder(500, ['error' => 'No se pudo guardar la lectura.']);
}
ApiRfid::responder(201, ['ok' => true, 'uid' => $uid]);
