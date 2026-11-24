<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../model/Reserva.php';
require_once __DIR__ . '/../model/EstadoReserva.php';

class ReservaDAO
{
    public function listar(): array
    {
        $consulta = Database::conectar()->prepare(
            'SELECT r.id, r.huesped_id, r.habitacion_id, r.fecha_entrada, r.fecha_salida, r.estado,
                    h.nombre AS huesped_nombre, hab.numero AS habitacion_numero
             FROM reserva r
             JOIN huesped h ON h.id = r.huesped_id
             JOIN habitacion hab ON hab.id = r.habitacion_id
             ORDER BY r.fecha_entrada DESC, r.id DESC'
        );
        $consulta->execute();
        return array_map([$this, 'crearDesdeFila'], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?Reserva
    {
        $consulta = Database::conectar()->prepare(
            'SELECT r.id, r.huesped_id, r.habitacion_id, r.fecha_entrada, r.fecha_salida, r.estado,
                    h.nombre AS huesped_nombre, hab.numero AS habitacion_numero
             FROM reserva r
             JOIN huesped h ON h.id = r.huesped_id
             JOIN habitacion hab ON hab.id = r.habitacion_id
             WHERE r.id = ?'
        );
        $consulta->execute([$id]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $this->crearDesdeFila($fila);
    }

    public function buscarPendientes(): array
    {
        $consulta = Database::conectar()->prepare(
            'SELECT r.id, r.huesped_id, r.habitacion_id, r.fecha_entrada, r.fecha_salida, r.estado,
                    h.nombre AS huesped_nombre, hab.numero AS habitacion_numero
             FROM reserva r
             JOIN huesped h ON h.id = r.huesped_id
             JOIN habitacion hab ON hab.id = r.habitacion_id
             WHERE r.estado = ?
             ORDER BY r.fecha_entrada'
        );
        $consulta->execute([EstadoReserva::PENDIENTE]);
        return array_map([$this, 'crearDesdeFila'], $consulta->fetchAll());
    }

    public function crear(Reserva $reserva): int
    {
        $conexion = Database::conectar();
        $consulta = $conexion->prepare(
            'INSERT INTO reserva (huesped_id, habitacion_id, fecha_entrada, fecha_salida, estado)
             VALUES (?, ?, ?, ?, ?)'
        );
        $consulta->execute([
            $reserva->huespedId,
            $reserva->habitacionId,
            $reserva->fechaEntrada,
            $reserva->fechaSalida,
            $reserva->estado,
        ]);
        return (int) $conexion->lastInsertId();
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        $consulta = Database::conectar()->prepare('UPDATE reserva SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }

    public function hayCruce(
        int $habitacionId,
        string $fechaEntrada,
        string $fechaSalida,
        ?int $excluirId = null
    ): bool {
        $sql = 'SELECT COUNT(*) FROM reserva
                WHERE habitacion_id = ?
                  AND estado IN (?, ?)
                  AND fecha_entrada < ?
                  AND fecha_salida > ?';
        $parametros = [
            $habitacionId,
            EstadoReserva::PENDIENTE,
            EstadoReserva::ACTIVA,
            $fechaSalida,
            $fechaEntrada,
        ];

        if ($excluirId !== null) {
            $sql .= ' AND id <> ?';
            $parametros[] = $excluirId;
        }

        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute($parametros);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existenPorHabitacion(int $habitacionId): bool
    {
        $consulta = Database::conectar()->prepare(
            'SELECT COUNT(*) FROM reserva WHERE habitacion_id = ?'
        );
        $consulta->execute([$habitacionId]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existenPorHuesped(int $huespedId): bool
    {
        $consulta = Database::conectar()->prepare(
            'SELECT COUNT(*) FROM reserva WHERE huesped_id = ?'
        );
        $consulta->execute([$huespedId]);
        return (int) $consulta->fetchColumn() > 0;
    }

    private function crearDesdeFila(array $fila): Reserva
    {
        return new Reserva(
            huespedId: (int) $fila['huesped_id'],
            habitacionId: (int) $fila['habitacion_id'],
            fechaEntrada: $fila['fecha_entrada'],
            fechaSalida: $fila['fecha_salida'],
            estado: $fila['estado'],
            id: (int) $fila['id'],
            huespedNombre: $fila['huesped_nombre'] ?? null,
            habitacionNumero: $fila['habitacion_numero'] ?? null
        );
    }
}
