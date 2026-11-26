<?php
// Uso: php database/probar_conexion.php
// Prueba la conexión definida en app/config/config.php (solo CLI).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../app/core/Database.php';

echo 'Driver: ' . Database::driver() . PHP_EOL;
echo 'PDO drivers disponibles: ' . implode(', ', PDO::getAvailableDrivers()) . PHP_EOL;
try {
    $c = Database::conectar();
    echo 'Conexión OK. Servidor: ' . $c->query('SELECT version()')->fetchColumn() . PHP_EOL;
    $t = Database::driver() === 'pgsql'
        ? "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY 1"
        : 'SHOW TABLES';
    $tablas = $c->query($t)->fetchAll(PDO::FETCH_COLUMN);
    echo 'Tablas: ' . ($tablas ? implode(', ', $tablas) : '(ninguna, falta importar el esquema)') . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
