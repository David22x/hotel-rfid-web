# Hotel RFID Web

Control de acceso a habitaciones de hotel con tarjetas RFID (RC522 + Arduino).
PHP, MySQL, HTML, CSS y JS. Patrón MVC + DAO, sin frameworks.

## Cómo ejecutarlo en local

1. Instalar XAMPP o Laragon y activar Apache y MySQL.
2. Clonar el repo dentro de `htdocs` (XAMPP) o `www` (Laragon).
3. Copiar `app/config/config.example.php` como `app/config/config.php` y ajustar los datos.
4. Crear la base `hotel_rfid` en phpMyAdmin e importar `database/schema.sql` y `database/datos_demo.sql`.
5. Abrir `http://localhost/hotel-rfid-web/public/`.

## Ramas

- `main`: solo versiones estables.
- `develop`: integración.
- `feature/nombre-modulo`: una por integrante. Todo entra por Pull Request.
