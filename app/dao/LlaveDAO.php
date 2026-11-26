<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../model/Llave.php';

class LlaveDAO
{
    public function buscarPorUid(string $uid): ?Llave
    {
        $consulta = Database::conectar()->prepare(
            'SELECT id, reserva_id, uid, valida_desde, valida_hasta, estado
             FROM llave
             WHERE uid = ?
             ORDER BY (estado = ?) DESC, id DESC
             LIMIT 1'
        );
        $consulta->execute([$uid, EstadoLlave::ACTIVA]);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->crearDesdeFila($fila);
    }

    /** @return Llave[] */
    public function listarActivasVencidas(string $ahora): array
    {
        $consulta = Database::conectar()->prepare(
            'SELECT id, reserva_id, uid, valida_desde, valida_hasta, estado
             FROM llave
             WHERE estado = ? AND valida_hasta < ?
             ORDER BY valida_hasta, id'
        );
        $consulta->execute([EstadoLlave::ACTIVA, $ahora]);

        return array_map([$this, 'crearDesdeFila'], $consulta->fetchAll());
    }

    // Solo devuelve true si esta ejecución cambió el estado.
    public function marcarVencida(int $id, string $ahora): bool
    {
        $consulta = Database::conectar()->prepare(
            'UPDATE llave SET estado = ? WHERE id = ? AND estado = ? AND valida_hasta < ?'
        );
        $consulta->execute([EstadoLlave::VENCIDA, $id, EstadoLlave::ACTIVA, $ahora]);

        return $consulta->rowCount() > 0;
    }

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
        return Database::ultimoId($conexion, 'llave');
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
