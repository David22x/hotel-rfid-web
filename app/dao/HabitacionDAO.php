<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../model/Habitacion.php';

class HabitacionDAO
{
    public function listar(): array
    {
        $consulta = Database::conectar()->prepare(
            'SELECT id, numero, piso, tipo, estado FROM habitacion ORDER BY CAST(numero AS UNSIGNED), numero'
        );
        $consulta->execute();
        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?Habitacion
    {
        $consulta = Database::conectar()->prepare(
            'SELECT id, numero, piso, tipo, estado FROM habitacion WHERE id = ?'
        );
        $consulta->execute([$id]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $this->hidratar($fila);
    }

    public function buscarPorNumero(string $numero): ?Habitacion
    {
        $consulta = Database::conectar()->prepare(
            'SELECT id, numero, piso, tipo, estado FROM habitacion WHERE numero = ?'
        );
        $consulta->execute([$numero]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $this->hidratar($fila);
    }

    public function crear(Habitacion $habitacion): int
    {
        $conexion = Database::conectar();
        $consulta = $conexion->prepare(
            'INSERT INTO habitacion (numero, piso, tipo, estado) VALUES (?, ?, ?, ?)'
        );
        $consulta->execute([
            $habitacion->numero,
            $habitacion->piso,
            $habitacion->tipo,
            $habitacion->estado,
        ]);
        return (int) $conexion->lastInsertId();
    }

    public function actualizar(Habitacion $habitacion): void
    {
        $consulta = Database::conectar()->prepare(
            'UPDATE habitacion SET numero = ?, piso = ?, tipo = ?, estado = ? WHERE id = ?'
        );
        $consulta->execute([
            $habitacion->numero,
            $habitacion->piso,
            $habitacion->tipo,
            $habitacion->estado,
            $habitacion->id,
        ]);
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        $consulta = Database::conectar()->prepare('UPDATE habitacion SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }

    public function eliminar(int $id): void
    {
        $consulta = Database::conectar()->prepare('DELETE FROM habitacion WHERE id = ?');
        $consulta->execute([$id]);
    }

    private function hidratar(array $fila): Habitacion
    {
        return new Habitacion(
            $fila['numero'],
            (int) $fila['piso'],
            $fila['tipo'],
            $fila['estado'],
            (int) $fila['id']
        );
    }
}
