<?php

// Tarea programada: marca como vencidas las llaves caducadas
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/dao/LlaveDAO.php';
require_once __DIR__ . '/../app/dao/ReservaDAO.php';
require_once __DIR__ . '/../app/dao/EventoDAO.php';

date_default_timezone_set(ZONA_HORARIA);

$llaveDao = new LlaveDAO();
$reservaDao = new ReservaDAO();
$eventoDao = new EventoDAO();

$ahora = date('Y-m-d H:i:s');
$vencidas = $llaveDao->listarActivasVencidas($ahora);
$total = 0;

foreach ($vencidas as $llave) {
    if (!$llaveDao->marcarVencida($llave->id, $ahora)) {
        continue;
    }
    $total++;

    $reserva = $reservaDao->buscarPorId($llave->reservaId);
    $habitacionId = $reserva === null ? null : $reserva->habitacionId;

    $eventoDao->registrar(
        'LLAVE_VENCIDA',
        $habitacionId,
        $llave->uid,
        'OK',
        'Llave vencida automáticamente al terminar la estadía'
    );
}

echo $ahora . ' - llaves vencidas: ' . $total . PHP_EOL;
