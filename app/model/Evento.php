<?php

class Evento
{
    public function __construct(
        public string $fechaHora,
        public string $tipo,
        public ?int $habitacionId = null,
        public ?string $uid = null,
        public ?string $resultado = null,
        public ?string $detalle = null,
        public ?int $id = null,
    ) {
    }
}
