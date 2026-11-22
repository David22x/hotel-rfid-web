<?php

require_once __DIR__ . '/../model/Llave.php';

class LlaveDAO
{
    /**
     * Prioriza la llave activa. Si no existe, devuelve la última del historial.
     */
    public function buscarPorUid(string $uid): ?Llave
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarActivaPorUid(string $uid): ?Llave
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarActivaPorReserva(int $reservaId): ?Llave
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Devuelve las llaves activas cuya vigencia termina antes de la fecha indicada.
     * @return Llave[]
     */
    public function listarActivasVencidas(string $ahora): array
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Devuelve el ID generado.
     */
    public function crear(Llave $llave): int
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }
}
