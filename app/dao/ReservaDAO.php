<?php

require_once __DIR__ . '/../model/Reserva.php';

class ReservaDAO
{
    /**
     * @return Reserva[]
     */
    public function listar(?string $estado = null): array
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarPorId(int $id): ?Reserva
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Devuelve el ID generado.
     */
    public function crear(Reserva $reserva): int
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Requiere el ID de una reserva existente.
     */
    public function actualizar(Reserva $reserva): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Solo considera reservas pendientes o activas. Las estadías contiguas se permiten.
     */
    public function hayCruce(int $habitacionId, string $entrada, string $salida, ?int $excluirReservaId = null): bool
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function existenPorHabitacion(int $habitacionId): bool
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function existenPorHuesped(int $huespedId): bool
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function existeActivaPorHabitacion(int $habitacionId): bool
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }
}
