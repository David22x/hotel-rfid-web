<?php
// POST /api/acceso.php  {"uid":"A3F1C20B","habitacion_id":1}   (Authorization: Bearer <RFID_TOKEN>)
// Cerradura de una habitación: valida la tarjeta, registra el evento y responde OK/NO para el Arduino.
define('SIN_SESION', true);
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/core/ApiRfid.php';
require_once __DIR__ . '/../../app/service/CerraduraService.php';
require_once __DIR__ . '/../../app/dao/HabitacionDAO.php';

ApiRfid::exigirMetodo('POST');
ApiRfid::exigirToken();

$datos = ApiRfid::datos();
$uid = ApiRfid::uidValido($datos['uid'] ?? null);
$habitacionId = filter_var($datos['habitacion_id'] ?? null, FILTER_VALIDATE_INT);
if ($uid === null || $habitacionId === false || $habitacionId < 1) {
    ApiRfid::responder(400, ['error' => 'Se requieren uid (hexadecimal) y habitacion_id (entero).']);
}

try {
    if ((new HabitacionDAO())->buscarPorId($habitacionId) === null) {
        ApiRfid::responder(404, ['error' => 'La habitación no existe.']);
    }
    $resultado = (new CerraduraService())->validar($uid, $habitacionId);
} catch (Throwable $e) {
    error_log('api/acceso: ' . $e->getMessage());
    // Ante un fallo interno la puerta se mantiene cerrada.
    ApiRfid::responder(500, ['acceso' => false, 'respuesta' => 'NO', 'error' => 'Error al validar la tarjeta.']);
}

ApiRfid::responder(200, [
    'acceso' => $resultado === ResultadoAcceso::CONCEDIDO,
    'resultado' => $resultado,
    'mensaje' => ResultadoAcceso::mensaje($resultado),
    'respuesta' => ResultadoAcceso::respuestaArduino($resultado),
]);
