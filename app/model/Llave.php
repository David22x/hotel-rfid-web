<?php

class Llave
{
    public function __construct(
        public int $reservaId,
        public string $uid,
        public string $validaDesde,
        public string $validaHasta,
        public string $estado = 'ACTIVA',
        public ?int $id = null,
    ) {
    }
}
