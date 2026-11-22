<?php

require_once __DIR__ . '/../model/Habitacion.php';
require_once __DIR__ . '/../core/Database.php';

class HabitacionDAO
{
    /**
     * @return Habitacion[]
     */
    public function listar(): array
    {
        $consulta = Database::conectar()->prepare('SELECT id, numero, piso, tipo, estado FROM habitacion ORDER BY numero');
        $consulta->execute();

        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?Habitacion
    {
        $consulta = Database::conectar()->prepare('SELECT id, numero, piso, tipo, estado FROM habitacion WHERE id = ?');
        $consulta->execute([$id]);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    public function buscarPorNumero(string $numero): ?Habitacion
    {
        $consulta = Database::conectar()->prepare('SELECT id, numero, piso, tipo, estado FROM habitacion WHERE numero = ?');
        $consulta->execute([$numero]);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    /**
     * Devuelve el ID generado.
     */
    public function crear(Habitacion $habitacion): int
    {
        $conexion = Database::conectar();
        $consulta = $conexion->prepare('INSERT INTO habitacion (numero, piso, tipo, estado) VALUES (?, ?, ?, ?)');
        $consulta->execute([$habitacion->numero, $habitacion->piso, $habitacion->tipo, $habitacion->estado]);

        return (int) $conexion->lastInsertId();
    }

    /**
     * Requiere el ID de una habitación existente.
     */
    public function actualizar(Habitacion $habitacion): void
    {
        if ($habitacion->id === null) {
            throw new InvalidArgumentException('Se requiere un ID para actualizar el registro.');
        }

        $consulta = Database::conectar()->prepare('UPDATE habitacion SET numero = ?, piso = ?, tipo = ?, estado = ? WHERE id = ?');
        $consulta->execute([$habitacion->numero, $habitacion->piso, $habitacion->tipo, $habitacion->estado, $habitacion->id]);
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
            (int) $fila['id'],
        );
    }
}
