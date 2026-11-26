<?php

require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../core/db.php';

class UsuarioDAO
{
    public function listar(): array
    {
        $consulta = conectar()->prepare('SELECT id, usuario, password_hash, rol, activo FROM usuario ORDER BY usuario');
        $consulta->execute();

        return array_map([$this, 'hidratar'], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?Usuario
    {
        $consulta = conectar()->prepare('SELECT id, usuario, password_hash, rol, activo FROM usuario WHERE id = ?');
        $consulta->execute([$id]);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    public function buscarPorUsuario(string $usuario): ?Usuario
    {
        $consulta = conectar()->prepare('SELECT id, usuario, password_hash, rol, activo FROM usuario WHERE usuario = ?');
        $consulta->execute([$usuario]);
        $fila = $consulta->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    public function contarAdministradoresActivos(): int
    {
        $consulta = conectar()->prepare('SELECT COUNT(*) FROM usuario WHERE rol = ? AND activo = 1');
        $consulta->execute(['ADMIN']);

        return (int) $consulta->fetchColumn();
    }

    public function crear(Usuario $usuario): int
    {
        $conexion = conectar();
        $consulta = $conexion->prepare('INSERT INTO usuario (usuario, password_hash, rol, activo) VALUES (?, ?, ?, ?)');
        $consulta->execute([$usuario->usuario, $usuario->passwordHash, $usuario->rol, (int) $usuario->activo]);

        return ultimoId($conexion, 'usuario');
    }

    public function actualizar(Usuario $usuario): void
    {
        if ($usuario->id === null) {
            throw new InvalidArgumentException('Se requiere un ID para actualizar el registro.');
        }

        $consulta = conectar()->prepare('UPDATE usuario SET usuario = ?, rol = ?, activo = ? WHERE id = ?');
        $consulta->execute([$usuario->usuario, $usuario->rol, (int) $usuario->activo, $usuario->id]);
    }

    public function actualizarActivo(int $id, bool $activo): void
    {
        $consulta = conectar()->prepare('UPDATE usuario SET activo = ? WHERE id = ?');
        $consulta->execute([(int) $activo, $id]);
    }

    public function actualizarPasswordHash(int $id, string $passwordHash): void
    {
        $consulta = conectar()->prepare('UPDATE usuario SET password_hash = ? WHERE id = ?');
        $consulta->execute([$passwordHash, $id]);
    }

    public function eliminar(int $id): void
    {
        $consulta = conectar()->prepare('DELETE FROM usuario WHERE id = ?');
        $consulta->execute([$id]);
    }

    private function hidratar(array $fila): Usuario
    {
        return new Usuario(
            $fila['usuario'],
            $fila['password_hash'],
            $fila['rol'],
            (bool) $fila['activo'],
            (int) $fila['id'],
        );
    }
}
