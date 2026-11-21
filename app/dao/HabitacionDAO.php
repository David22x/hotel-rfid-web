<?php

require_once __DIR__ . '/../model/Habitacion.php';

class HabitacionDAO
{
    /**
     * @return Habitacion[]
     */
    public function listar(): array
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarPorId(int $id): ?Habitacion
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarPorNumero(string $numero): ?Habitacion
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Devuelve el ID generado.
     */
    public function crear(Habitacion $habitacion): int
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Requiere el ID de una habitación existente.
     */
    public function actualizar(Habitacion $habitacion): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function eliminar(int $id): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }
}
