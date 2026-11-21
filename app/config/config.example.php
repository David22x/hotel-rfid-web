<?php
// Copia este archivo como config.php y completa tus datos.
// config.php NO se sube a Git (está en el .gitignore).

define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel_rfid');
define('DB_USER', 'root');
define('DB_PASS', '');            // en XAMPP suele estar vacía

define('ZONA_HORARIA', 'America/Guayaquil');

// Ruta base del sitio. Local (XAMPP): '/hotel-rfid-web/public'
// En el dominio: ''
define('BASE_URL', '/hotel-rfid-web/public');

define('MOSTRAR_ERRORES', true);  // en el dominio: false