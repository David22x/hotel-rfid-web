<?php

require_once __DIR__ . '/EstadoHabitacion.php';

class Habitacion
{
    public function __construct(
        public string $numero,
        public int $piso,
        public string $tipo,
        public string $estado = EstadoHabitacion::LIBRE,
        public ?int $id = null,
    ) {
    }
}
