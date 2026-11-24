<?php

class Reserva
{
    public function __construct(
        public int $huespedId,
        public int $habitacionId,
        public string $fechaEntrada,
        public string $fechaSalida,
        public string $estado = EstadoReserva::PENDIENTE,
        public ?int $id = null,
        public ?string $huespedNombre = null,
        public ?string $habitacionNumero = null,
    ) {
    }
}
