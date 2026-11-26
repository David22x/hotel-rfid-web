<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../model/Huesped.php';

class HuespedDAO
{
    public function listar(): array
    {
        $sql = 'SELECT id, nombre, documento, correo, telefono FROM huesped ORDER BY nombre';
        $consulta = conectar()->prepare($sql);
        $consulta->execute();
        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    public function buscar(string $termino): array
    {
        $patron = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($termino)) . '%';
        $sql = 'SELECT id, nombre, documento, correo, telefono FROM huesped
                WHERE LOWER(nombre) LIKE LOWER(?) ESCAPE \'!\' OR LOWER(documento) LIKE LOWER(?) ESCAPE \'!\' OR LOWER(correo) LIKE LOWER(?) ESCAPE \'!\'
                ORDER BY nombre';
        $consulta = conectar()->prepare($sql);
        $consulta->execute([$patron, $patron, $patron]);
        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?Huesped
    {
        $consulta = conectar()->prepare(
            'SELECT id, nombre, documento, correo, telefono FROM huesped WHERE id = ?'
        );
        $consulta->execute([$id]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $this->hidratar($fila);
    }

    public function buscarPorDocumento(string $documento): ?Huesped
    {
        $consulta = conectar()->prepare(
            'SELECT id, nombre, documento, correo, telefono FROM huesped WHERE documento = ?'
        );
        $consulta->execute([$documento]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $this->hidratar($fila);
    }

    public function crear(Huesped $huesped): int
    {
        $conexion = conectar();
        $consulta = $conexion->prepare(
            'INSERT INTO huesped (nombre, documento, correo, telefono) VALUES (?, ?, ?, ?)'
        );
        $consulta->execute([
            $huesped->nombre,
            $huesped->documento,
            $huesped->correo,
            $huesped->telefono,
        ]);
        return ultimoId($conexion, 'huesped');
    }

    public function actualizar(Huesped $huesped): void
    {
        $consulta = conectar()->prepare(
            'UPDATE huesped SET nombre = ?, documento = ?, correo = ?, telefono = ? WHERE id = ?'
        );
        $consulta->execute([
            $huesped->nombre,
            $huesped->documento,
            $huesped->correo,
            $huesped->telefono,
            $huesped->id,
        ]);
    }

    public function eliminar(int $id): void
    {
        $consulta = conectar()->prepare('DELETE FROM huesped WHERE id = ?');
        $consulta->execute([$id]);
    }

    private function hidratar(array $fila): Huesped
    {
        return new Huesped(
            $fila['nombre'],
            $fila['documento'],
            $fila['correo'] ?? '',
            $fila['telefono'] ?? '',
            (int) $fila['id']
        );
    }
}
