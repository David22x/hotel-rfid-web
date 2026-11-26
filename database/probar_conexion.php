<?php
// Uso por consola: php database/probar_conexion.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../app/core/db.php';

echo 'Driver: ' . driverBd() . PHP_EOL;
echo 'PDO drivers disponibles: ' . implode(', ', pdo_drivers()) . PHP_EOL;
try {
    $conexion = conectar();
    echo 'Conexión OK. Servidor: ' . $conexion->query('SELECT version()')->fetchColumn() . PHP_EOL;
    $sql = driverBd() === 'pgsql'
        ? "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY 1"
        : 'SHOW TABLES';
    $tablas = [];
    foreach ($conexion->query($sql) as $fila) {
        $tablas[] = $fila[0];
    }
    echo 'Tablas: ' . ($tablas ? implode(', ', $tablas) : '(ninguna, falta importar el esquema)') . PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
