<?php

require_once __DIR__ . '/../model/Huesped.php';
require_once __DIR__ . '/../core/Database.php';

class HuespedDAO
{
    /**
     * @return Huesped[]
     */
    public function listar(): array
    {
        $consulta = Database::conectar()->prepare('SELECT id, nombre, documento, correo, telefono FROM huesped ORDER BY nombre, id');
        $consulta->execute();

        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    /**
     * Busca por nombre o documento.
     * @return Huesped[]
     */
    public function buscar(string $criterio): array
    {
        $patron = '%' . strtr($criterio, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $consulta = Database::conectar()->prepare(
            "SELECT id, nombre, documento, correo, telefono FROM huesped
             WHERE nombre LIKE ? ESCAPE '!' OR documento LIKE ? ESCAPE '!'
             ORDER BY nombre, id"
        );
        $consulta->execute([$patron, $patron]);

        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?Huesped
    {
        $consulta = Database::conectar()->prepare('SELECT id, nombre, documento, correo, telefono FROM huesped WHERE id = ?');
        $consulta->execute([$id]);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    public function buscarPorDocumento(string $documento): ?Huesped
    {
        $consulta = Database::conectar()->prepare('SELECT id, nombre, documento, correo, telefono FROM huesped WHERE documento = ?');
        $consulta->execute([$documento]);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    /**
     * Devuelve el ID generado.
     */
    public function crear(Huesped $huesped): int
    {
        $conexion = Database::conectar();
        $consulta = $conexion->prepare('INSERT INTO huesped (nombre, documento, correo, telefono) VALUES (?, ?, ?, ?)');
        $consulta->execute([$huesped->nombre, $huesped->documento, $huesped->correo, $huesped->telefono]);

        return (int) $conexion->lastInsertId();
    }

    /**
     * Requiere el ID de un huésped existente.
     */
    public function actualizar(Huesped $huesped): void
    {
        if ($huesped->id === null) {
            throw new InvalidArgumentException('Se requiere un ID para actualizar el registro.');
        }

        $consulta = Database::conectar()->prepare('UPDATE huesped SET nombre = ?, documento = ?, correo = ?, telefono = ? WHERE id = ?');
        $consulta->execute([$huesped->nombre, $huesped->documento, $huesped->correo, $huesped->telefono, $huesped->id]);
    }

    public function eliminar(int $id): void
    {
        $consulta = Database::conectar()->prepare('DELETE FROM huesped WHERE id = ?');
        $consulta->execute([$id]);
    }

    private function hidratar(array $fila): Huesped
    {
        return new Huesped(
            $fila['nombre'],
            $fila['documento'],
            $fila['correo'],
            $fila['telefono'],
            (int) $fila['id'],
        );
    }
}
