# Despliegue de Hotel RFID Web

## Preparar el alojamiento

1. Publicar la versión aprobada del repositorio en el alojamiento con PHP 8.x, la extensión `pdo_mysql` y MySQL. Configurar la raíz web del dominio para que apunte a `hotel-rfid-web/public/`. Mantener `app/`, `database/` y `cron/` fuera de la raíz web.
2. Crear una base de datos y un usuario MySQL para la aplicación. En una instalación nueva, seleccionar esa base e importar `database/schema.sql`. Reservar `database/datos_demo.sql` para una base de pruebas y utilizar cuentas propias del entorno en el dominio.
3. Copiar `app/config/config.example.php` a `app/config/config.php` en el servidor. Ajustar `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` con los valores del alojamiento. Mantener `ZONA_HORARIA` en `America/Guayaquil`, fijar `BASE_URL` en `''` si se sirve en la raíz del dominio y establecer `MOSTRAR_ERRORES` en `false`. Guardar las credenciales únicamente en el archivo privado del servidor.
4. Habilitar HTTPS para el dominio y redirigir HTTP a HTTPS. Abrir `/login.php` mediante HTTPS, iniciar sesión y comprobar que los enlaces del menú y los estilos cargan con la ruta configurada.

## Programar la expiración de llaves

Activar la tarea cuando `cron/expirar_llaves.php` esté integrado en la versión desplegada. Sustituir las dos rutas del ejemplo por las rutas absolutas del ejecutable PHP CLI y del proyecto que indique el alojamiento.

Ejecutar primero el script de forma manual en un entorno de pruebas.

```sh
/ruta/a/php /ruta/a/hotel-rfid-web/cron/expirar_llaves.php
```

En el panel de tareas programadas, elegir una ejecución cada minuto y pegar el comando anterior. Si el panel acepta una línea completa de crontab, usar este formato.

```cron
* * * * * /ruta/a/php /ruta/a/hotel-rfid-web/cron/expirar_llaves.php
```

## Verificar en el dominio

1. En una base de pruebas, preparar una llave `ACTIVA` que ya esté fuera de vigencia y otra `ACTIVA` que siga vigente. Anotar cuántos eventos `LLAVE_VENCIDA` existen para el UID de la primera llave.
2. Ejecutar el comando una vez. Confirmar que la primera llave pasa a `VENCIDA`, que la segunda sigue `ACTIVA` y que la bitácora contiene un nuevo evento `LLAVE_VENCIDA` para el UID de prueba.
3. Ejecutarlo otra vez con los mismos datos y confirmar que el conteo de eventos para ese UID no aumenta.
4. Con la tarea programada habilitada, preparar otra llave vencida y esperar a la siguiente ejecución. Comprobar su estado y su evento en la bitácora para verificar que el programador del alojamiento ejecuta el comando.
