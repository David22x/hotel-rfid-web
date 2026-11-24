# Hotel RFID Web

Control de acceso a habitaciones de hotel con tarjetas RFID (RC522 + Arduino).
PHP, MySQL, HTML, CSS y JS. Patrón MVC + DAO, sin frameworks.

## Cómo ejecutarlo en local

1. Instalar XAMPP o Laragon y activar Apache y MySQL.
2. Clonar el repo dentro de `htdocs` (XAMPP) o `www` (Laragon).
3. Copiar `app/config/config.example.php` como `app/config/config.php` y ajustar los datos.
4. Crear la base `hotel_rfid` en phpMyAdmin e importar `database/schema.sql` y `database/datos_demo.sql`.
5. Abrir `http://localhost/Proy_Visual_Final/hotel-rfid-web/public/`.

## Reservas y control de acceso

- El módulo Reservas permite crear, listar y cancelar reservas pendientes.
- El módulo Check-In / Check-Out activa y revoca llaves dentro de transacciones.
- Mientras no exista una conexión serial/USB configurada con el lector RC522/Arduino,
  la interfaz incluye una lectura RFID simulada mediante un UID hexadecimal. Para
  integrar el hardware físico se requiere definir el puerto COM, el baud rate, el
  protocolo de lectura y exponer una API local que devuelva el UID al formulario.

## Despliegue

La [guía de despliegue](docs/despliegue.md) cubre la configuración del dominio, HTTPS y la ejecución programada de la expiración de llaves.

## Ramas

- `main`: solo versiones estables.
- `develop`: integración.
- `feature/nombre-modulo`: una por integrante. Todo entra por Pull Request.
