<?php

require_once __DIR__ . '/../model/Usuario.php';

class UsuarioDAO
{
    /**
     * @return Usuario[]
     */
    public function listar(): array
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function buscarPorId(int $id): ?Usuario
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Incluye usuarios inactivos para que el servicio decida si permite el login.
     */
    public function buscarPorUsuario(string $usuario): ?Usuario
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function contarAdministradoresActivos(): int
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Recibe la contraseña hasheada y devuelve el ID generado.
     */
    public function crear(Usuario $usuario): int
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    /**
     * Requiere el ID de un usuario existente.
     */
    public function actualizar(Usuario $usuario): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function actualizarActivo(int $id, bool $activo): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function actualizarPasswordHash(int $id, string $passwordHash): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }

    public function eliminar(int $id): void
    {
        throw new LogicException(__METHOD__ . ' todavía no está implementado.');
    }
}
