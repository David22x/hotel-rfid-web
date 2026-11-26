<?php

class PermisoService
{
    public static function tienePermiso(
        string $rol,
        string $modulo
    ): bool {
        $modulos = [
            'HABITACIONES',
            'HUESPEDES',
            'RESERVAS',
            'CHECK_IN_OUT',
            'CERRADURA_RFID',
            'BITACORA',
            'GESTION_USUARIOS'
        ];

        if (!in_array($modulo, $modulos, true)) {
            return false;
        }

        if ($rol === RolUsuario::ADMIN) {
            return true;
        }

        if ($rol !== RolUsuario::RECEPCIONISTA) {
            return false;
        }

        return in_array($modulo, [
            'HABITACIONES',
            'HUESPEDES',
            'RESERVAS',
            'CHECK_IN_OUT'
        ], true);
    }
}
