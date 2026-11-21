<?php

require_once __DIR__ . '/EstadoLlave.php';

class Llave
{
    public function __construct(
        public int $reservaId,
        public string $uid,
        public string $validaDesde,
        public string $validaHasta,
        public string $estado = EstadoLlave::ACTIVA,
        public ?int $id = null,
    ) {
    }
}
