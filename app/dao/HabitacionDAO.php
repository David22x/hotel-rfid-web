<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../model/Habitacion.php';

class HabitacionDAO
{
    public function listar(): array
    {
        $consulta = conectar()->prepare(
            'SELECT id, numero, piso, tipo, estado FROM habitacion ORDER BY LENGTH(numero), numero'
        );
        $consulta->execute();
        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?Habitacion
    {
        $consulta = conectar()->prepare(
            'SELECT id, numero, piso, tipo, estado FROM habitacion WHERE id = ?'
        );
        $consulta->execute([$id]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $this->hidratar($fila);
    }

    public function buscarPorNumero(string $numero): ?Habitacion
    {
        $consulta = conectar()->prepare(
            'SELECT id, numero, piso, tipo, estado FROM habitacion WHERE numero = ?'
        );
        $consulta->execute([$numero]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $this->hidratar($fila);
    }

    public function crear(Habitacion $habitacion): int
    {
        $conexion = conectar();
        $consulta = $conexion->prepare(
            'INSERT INTO habitacion (numero, piso, tipo, estado) VALUES (?, ?, ?, ?)'
        );
        $consulta->execute([
            $habitacion->numero,
            $habitacion->piso,
            $habitacion->tipo,
            $habitacion->estado,
        ]);
        return ultimoId($conexion, 'habitacion');
    }

    public function actualizar(Habitacion $habitacion): void
    {
        $consulta = conectar()->prepare(
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
        $consulta = conectar()->prepare('UPDATE habitacion SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }

    public function eliminar(int $id): void
    {
        $consulta = conectar()->prepare('DELETE FROM habitacion WHERE id = ?');
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
