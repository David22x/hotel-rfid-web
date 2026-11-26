<?php
// Marca como VENCIDA cada llave activa cuya hora de salida ya pasó y lo anota en la bitácora.
// Se ejecuta cada minuto con la tarea cron del hosting, por ejemplo:
//   * * * * * php /ruta/del/proyecto/cron/expirar_llaves.php
// En local se puede probar con: php cron/expirar_llaves.php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/dao/LlaveDAO.php';
require_once __DIR__ . '/../app/dao/ReservaDAO.php';
require_once __DIR__ . '/../app/dao/EventoDAO.php';
require_once __DIR__ . '/../app/model/TipoEvento.php';

date_default_timezone_set(ZONA_HORARIA);

$llaveDao = new LlaveDAO();
$reservaDao = new ReservaDAO();
$eventoDao = new EventoDAO();

$ahora = date('Y-m-d H:i:s');
$vencidas = $llaveDao->listarActivasVencidas($ahora);
$total = 0;

foreach ($vencidas as $llave) {
    // marcarVencida devuelve false si otra ejecución ya la cambió, así no se duplica el evento
    if (!$llaveDao->marcarVencida($llave->id, $ahora)) {
        continue;
    }
    $total++;

    // La bitácora guarda la habitación de la reserva a la que pertenecía la llave
    $reserva = $reservaDao->buscarPorId($llave->reservaId);
    $habitacionId = $reserva === null ? null : $reserva->habitacionId;

    $eventoDao->registrar(
        TipoEvento::LLAVE_VENCIDA,
        $habitacionId,
        $llave->uid,
        'OK',
        'Llave vencida automáticamente al terminar la estadía'
    );
}

echo $ahora . ' - llaves vencidas: ' . $total . PHP_EOL;
