<?php

require_once __DIR__ . '/../config/config.php';

class Database
{
    private static ?PDO $conexion = null;

    /** Driver activo: 'mysql' (por defecto) o 'pgsql'. */
    public static function driver(): string
    {
        return defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
    }

    public static function conectar(): PDO
    {
        if (self::$conexion === null) {
            // DB_HOST puede venir como "host" o "host:puerto".
            $host = DB_HOST;
            $puerto = defined('DB_PORT') ? (string) DB_PORT : '';
            if (preg_match('/^(.+):(\d+)$/', $host, $m)) {
                $host = $m[1];
                $puerto = $puerto !== '' ? $puerto : $m[2];
            }

            if (self::driver() === 'pgsql') {
                $dsn = 'pgsql:host=' . $host
                    . ';port=' . ($puerto !== '' ? $puerto : '5432')
                    . ';dbname=' . DB_NAME
                    . ';sslmode=' . (defined('DB_SSLMODE') ? DB_SSLMODE : 'require');
            } else {
                $dsn = 'mysql:host=' . $host
                    . ($puerto !== '' ? ';port=' . $puerto : '')
                    . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            }

            $conexion = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            if (self::driver() === 'pgsql') {
                $conexion->exec("SET TIME ZONE '-05:00'");
            } else {
                $conexion->exec("SET time_zone = '-05:00'");
            }
            self::$conexion = $conexion;
        }
        return self::$conexion;
    }

    /**
     * Último id insertado. En PostgreSQL lastInsertId() exige el nombre de la
     * secuencia, que para una columna id serial es "<tabla>_id_seq".
     */
    public static function ultimoId(PDO $conexion, string $tabla): int
    {
        if (self::driver() === 'pgsql') {
            return (int) $conexion->lastInsertId($tabla . '_id_seq');
        }
        return (int) $conexion->lastInsertId();
    }
}
