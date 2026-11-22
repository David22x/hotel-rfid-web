<?php

require_once __DIR__ . '/../model/Evento.php';

class EventoDAO
{
    /**
     * Devuelve el ID generado.
     */
    public function registrar(
        string $tipo,
        ?int $habitacionId,
        ?string $uid,
        ?string $resultado,
        ?string $detalle,
    ): int {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Filtra por fecha y hora y por habitación cuando se indican.
     * @return Evento[]
     */
    public function listar(?string $desde = null, ?string $hasta = null, ?int $habitacionId = null): array
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }
}
