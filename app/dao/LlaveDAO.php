<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../model/Llave.php';

class LlaveDAO
{
    public function buscarPorUid(string $uid): ?Llave
    {
        $consulta = conectar()->prepare(
            'SELECT id, reserva_id, uid, valida_desde, valida_hasta, estado
             FROM llave
             WHERE uid = ?
             ORDER BY (estado = ?) DESC, id DESC
             LIMIT 1'
        );
        $consulta->execute([$uid, 'ACTIVA']);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->crearDesdeFila($fila);
    }

    public function listarActivasVencidas(string $ahora): array
    {
        $consulta = conectar()->prepare(
            'SELECT id, reserva_id, uid, valida_desde, valida_hasta, estado
             FROM llave
             WHERE estado = ? AND valida_hasta < ?
             ORDER BY valida_hasta, id'
        );
        $consulta->execute(['ACTIVA', $ahora]);

        return array_map([$this, 'crearDesdeFila'], $consulta->fetchAll());
    }

    public function marcarVencida(int $id, string $ahora): bool
    {
        $consulta = conectar()->prepare(
            'UPDATE llave SET estado = ? WHERE id = ? AND estado = ? AND valida_hasta < ?'
        );
        $consulta->execute(['VENCIDA', $id, 'ACTIVA', $ahora]);

        return $consulta->rowCount() > 0;
    }

    public function uidTieneLlaveActiva(string $uid): bool
    {
        $consulta = conectar()->prepare(
            'SELECT COUNT(*) FROM llave WHERE uid = ? AND estado = ?'
        );
        $consulta->execute([$uid, 'ACTIVA']);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existeActivaPorReserva(int $reservaId): bool
    {
        $consulta = conectar()->prepare(
            'SELECT COUNT(*) FROM llave WHERE reserva_id = ? AND estado = ?'
        );
        $consulta->execute([$reservaId, 'ACTIVA']);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function crear(Llave $llave): int
    {
        $conexion = conectar();
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
        return ultimoId($conexion, 'llave');
    }

    public function revocarActivasPorReserva(int $reservaId): void
    {
        $consulta = conectar()->prepare(
            'UPDATE llave SET estado = ? WHERE reserva_id = ? AND estado = ?'
        );
        $consulta->execute([
            'REVOCADA',
            $reservaId,
            'ACTIVA',
        ]);
    }

    private function crearDesdeFila(array $fila): Llave
    {
        return new Llave(
            (int) $fila['reserva_id'],
            $fila['uid'],
            $fila['valida_desde'],
            $fila['valida_hasta'],
            $fila['estado'],
            (int) $fila['id']
        );
    }
}
