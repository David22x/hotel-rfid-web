<?php

require_once __DIR__ . '/../config/config.php';

function driverBd(): string
{
    return defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
}

function conectar(): PDO
{
    static $conexion = null;

    if ($conexion === null) {
        $partes = explode(':', DB_HOST);
        $host = $partes[0];
        $puerto = $partes[1] ?? (driverBd() === 'pgsql' ? '5432' : '3306');
        $dsn = driverBd() . ':host=' . $host . ';port=' . $puerto . ';dbname=' . DB_NAME;

        if (driverBd() === 'pgsql') {
            $dsn .= ';sslmode=' . (defined('DB_SSLMODE') ? DB_SSLMODE : 'require');
            $zona = "SET TIME ZONE '-05:00'";
        } else {
            $dsn .= ';charset=utf8mb4';
            $zona = "SET time_zone = '-05:00'";
        }

        $conexion = new PDO($dsn, DB_USER, DB_PASS);
        $conexion->exec($zona);
    }

    return $conexion;
}

function ultimoId(PDO $conexion, string $tabla): int
{
    return (int) $conexion->lastInsertId(driverBd() === 'pgsql' ? $tabla . '_id_seq' : null);
}
