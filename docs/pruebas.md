# Pruebas manuales

## Base de datos y DAO

Usar una base local de pruebas con `database/schema.sql` y `database/datos_demo.sql` importados. Cargar la configuración local y ejecutar las llamadas desde PHP. Usar registros temporales para las pruebas de escritura.

| Caso | Datos o acción | Resultado esperado | Resultado obtenido |
| --- | --- | --- | --- |
| Consultar habitación | Buscar el número `101` y después un número inexistente | Devuelve un objeto `Habitacion` y después `null` | Pendiente en MySQL desde PHP |
| Gestionar habitación | Crear una habitación temporal, editar su tipo, cambiar su estado y eliminarla | La creación devuelve el ID, las consultas reflejan los cambios y luego devuelve `null` | Pendiente en MySQL desde PHP |
| Buscar huésped | Buscar por `Torres`, por `0000000001` y por `%` | Los dos primeros criterios encuentran a Ana Torres. El tercero busca un signo literal | Pendiente en MySQL desde PHP |
| Gestionar huésped | Crear un huésped temporal con documento único, editar su correo y eliminarlo | La creación devuelve el ID, el correo cambia y el huésped deja de aparecer | Pendiente en MySQL desde PHP |
| Gestionar usuario | Verificar el hash de `admin`. Crear otro administrador, cambiar su nombre y desactivarlo. Luego actualizar su hash | El hash inicial es válido, editar el nombre conserva el hash, el conteo de administradores activos disminuye al desactivar y el nuevo hash verifica la nueva contraseña | Pendiente en MySQL desde PHP |
| Proteger registros con reservas | Consultar las dependencias de la habitación `101` y de Ana Torres. Intentar eliminarlos | Las consultas devuelven `true` y MySQL rechaza ambas eliminaciones por clave foránea | Pendiente en MySQL desde PHP |
| Detectar cruce | Usar el mismo intervalo de la reserva pendiente de la habitación `101` | `hayCruce()` devuelve `true` | Pendiente en MySQL desde PHP |
| Permitir reservas contiguas | Usar como entrada la fecha de salida de la reserva de la habitación `101` y como salida una fecha posterior | `hayCruce()` devuelve `false` | Pendiente en MySQL desde PHP |
| Excluir la reserva editada | Usar el intervalo de la reserva de la habitación `101` y pasar su ID como exclusión | `hayCruce()` devuelve `false` | Pendiente en MySQL desde PHP |
| Detectar reserva activa | Consultar `existeActivaPorHabitacion()` antes y después de cambiar a `ACTIVA` una reserva de prueba | Devuelve `false` y después `true` | Pendiente en MySQL desde PHP |
| Probar reservas y operaciones RFID | Ejecutar `C:\xampp\php\php.exe tests/reservas_checkin_test.php` desde el directorio del proyecto | Rechaza pasados, detecta cruces, permite contigüidad, completa check-in/check-out y verifica reversión ante habitación no disponible | Ejecutado localmente; todos los casos pasaron |
