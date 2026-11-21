<?php

require_once __DIR__ . '/../model/Reserva.php';
require_once __DIR__ . '/../core/Database.php';

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
        $consulta = Database::conectar()->prepare(
            "SELECT EXISTS (
                 SELECT 1 FROM reserva
                 WHERE habitacion_id = ? AND estado IN (?, ?)
                   AND fecha_entrada < ? AND fecha_salida > ?
                   AND (? IS NULL OR id <> ?)
             )"
        );
        $consulta->execute([
            $habitacionId,
            EstadoReserva::PENDIENTE,
            EstadoReserva::ACTIVA,
            $salida,
            $entrada,
            $excluirReservaId,
            $excluirReservaId,
        ]);

        return (bool) $consulta->fetchColumn();
    }

    public function existenPorHabitacion(int $habitacionId): bool
    {
        $consulta = Database::conectar()->prepare('SELECT EXISTS (SELECT 1 FROM reserva WHERE habitacion_id = ?)');
        $consulta->execute([$habitacionId]);

        return (bool) $consulta->fetchColumn();
    }

    public function existenPorHuesped(int $huespedId): bool
    {
        $consulta = Database::conectar()->prepare('SELECT EXISTS (SELECT 1 FROM reserva WHERE huesped_id = ?)');
        $consulta->execute([$huespedId]);

        return (bool) $consulta->fetchColumn();
    }

    public function existeActivaPorHabitacion(int $habitacionId): bool
    {
        $consulta = Database::conectar()->prepare('SELECT EXISTS (SELECT 1 FROM reserva WHERE habitacion_id = ? AND estado = ?)');
        $consulta->execute([$habitacionId, EstadoReserva::ACTIVA]);

        return (bool) $consulta->fetchColumn();
    }
}
