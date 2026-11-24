<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../model/Llave.php';

class LlaveDAO
{
    public function uidTieneLlaveActiva(string $uid): bool
    {
        $consulta = Database::conectar()->prepare(
            'SELECT COUNT(*) FROM llave WHERE uid = ? AND estado = ?'
        );
        $consulta->execute([$uid, EstadoLlave::ACTIVA]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existeActivaPorReserva(int $reservaId): bool
    {
        $consulta = Database::conectar()->prepare(
            'SELECT COUNT(*) FROM llave WHERE reserva_id = ? AND estado = ?'
        );
        $consulta->execute([$reservaId, EstadoLlave::ACTIVA]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function crear(Llave $llave): int
    {
        $conexion = Database::conectar();
        $consulta = $conexion->prepare(
            'INSERT INTO llave (reserva_id, uid, valida_desde, valida_hasta, estado)
             VALUES (?, ?, ?, ?, ?)'
        );
        $consulta->execute([
            $llave->reservaId,
            $llave->uid,
            $llave->validaDesde,
            $llave->validaHasta,
            $llave->estado,
        ]);
        return (int) $conexion->lastInsertId();
    }

    public function revocarActivasPorReserva(int $reservaId): void
    {
        $consulta = Database::conectar()->prepare(
            'UPDATE llave SET estado = ? WHERE reserva_id = ? AND estado = ?'
        );
        $consulta->execute([
            EstadoLlave::REVOCADA,
            $reservaId,
            EstadoLlave::ACTIVA,
        ]);
    }
}
