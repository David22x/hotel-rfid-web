<?php

class Habitacion
{
    public function __construct(
        public string $numero,
        public int $piso,
        public string $tipo,
        public string $estado = 'LIBRE',
        public ?int $id = null,
    ) {
    }
}
