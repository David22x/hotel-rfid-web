<?php

require_once __DIR__ . '/../model/Huesped.php';

class HuespedDAO
{
    /**
     * @return Huesped[]
     */
    public function listar(): array
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Busca por nombre o documento.
     * @return Huesped[]
     */
    public function buscar(string $criterio): array
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarPorId(int $id): ?Huesped
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarPorDocumento(string $documento): ?Huesped
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Devuelve el ID generado.
     */
    public function crear(Huesped $huesped): int
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Requiere el ID de un huésped existente.
     */
    public function actualizar(Huesped $huesped): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function eliminar(int $id): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }
}
