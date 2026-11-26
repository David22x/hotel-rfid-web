<?php
// Copia este archivo como config.php y completa tus datos.
// config.php NO se sube a Git (está en el .gitignore).

define('DB_DRIVER', 'mysql');   // 'mysql' o 'pgsql' (Azure PostgreSQL)
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel_rfid');
define('DB_USER', 'root');
define('DB_PASS', '');            // en XAMPP suele estar vacía
// Opcionales: define('DB_PORT', 5432);  define('DB_SSLMODE', 'require'); // solo pgsql

define('ZONA_HORARIA', 'America/Guayaquil');

// Ruta base del sitio. Local (XAMPP): '/Proy_Visual_Final/hotel-rfid-web/public'
// En el dominio: ''
define('BASE_URL', '/Proy_Visual_Final/hotel-rfid-web/public');

define('MOSTRAR_ERRORES', true);  // en el dominio: false

// Token que usa el lector RFID para llamar a /api/. Generar con: openssl rand -hex 32
define('RFID_TOKEN', '');
