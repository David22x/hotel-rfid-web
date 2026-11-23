<?php

class PermisoService
{
    public static function tienePermiso(
        string $rol,
        string $modulo
    ): bool {
        if ($rol === RolUsuario::ADMIN) {
            return true;
        }

        if ($rol !== RolUsuario::RECEPCIONISTA) {
            return false;
        }

        $modulosPermitidos = [
            'HABITACIONES',
            'HUESPEDES',
            'RESERVAS',
            'CHECK_IN_OUT'
        ];

        return in_array($modulo, $modulosPermitidos, true);
    }
}