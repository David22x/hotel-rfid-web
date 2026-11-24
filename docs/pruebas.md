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

## Flujo integrado con datos de demostración

Preparar una base MySQL vacía de pruebas, importar `database/schema.sql` y luego `database/datos_demo.sql`. Configurar `app/config/config.php` para apuntar a esa base y abrir `public/login.php` en XAMPP. Conviene probar el check-in de la habitación 101 el mismo día de la importación, porque su reserva vence 24 horas después.

| Caso | Pasos | Resultado esperado | Resultado obtenido |
| --- | --- | --- | --- |
| Cargar datos | Importar ambos scripts en MySQL 8.0.43 y consultar los totales | 2 usuarios, 3 habitaciones, 2 huéspedes y 2 reservas | Aprobado en una instancia aislada |
| Iniciar sesión con ambos roles | Usar las cuentas de `datos_demo.sql` y cerrar sesión entre intentos | Cada cuenta accede con su rol correspondiente | Hashes verificados con PHP. Inicio de sesión de esta corrida pendiente |
| Crear una reserva | Seleccionar un huésped de la demo y la habitación 102. Usar un intervalo que empiece cuatro días después de la importación | La nueva reserva aparece como `PENDIENTE` | Pendiente |
| Hacer check-in | Abrir la reserva de la habitación 101 y simular el UID `A3F1C20B` | Reserva y llave `ACTIVA`, habitación `OCUPADA` | Pendiente |
| Hacer check-out | Finalizar la misma reserva | Reserva `FINALIZADA`, llave `REVOCADA`, habitación `POR_LIMPIAR` | Pendiente |
| Consultar la bitácora | Abrir la bitácora como administrador después del check-out | Aparecen `CHECK_IN`, `LLAVE_EMITIDA`, `CHECK_OUT` y `LLAVE_REVOCADA` para la habitación 101 | Pendiente |
| Probar la cerradura simulada | Después del check-in, leer el UID en la habitación 101 y en otra habitación. Repetir en la 101 después del check-out | Acceso concedido en la habitación correcta durante la vigencia. Acceso denegado en la otra habitación y después del check-out | Pendiente de integrar la cerradura |

La carga SQL funcionó desde el cliente MySQL. En esta corrida, el entorno impidió conectar PHP con la instancia aislada mediante PDO, por lo que los resultados de las operaciones de la aplicación quedan por confirmar.

## Autenticación, permisos y gestión de usuarios - Sandra

**Entorno:** XAMPP local, PHP/MySQL, cuentas de prueba ADMIN y RECEPCIONISTA.  
**Registro:** resultados comunicados por Sandra tras ejecutar las pruebas manuales. No se incluyen capturas aún.

| Caso | Datos o acción | Resultado esperado | Resultado obtenido |
| --- | --- | --- | --- |
| SU-01 Acceso ADMIN | Iniciar sesión como ADMIN y abrir `usuarios.php` | Acceso permitido | Aprobada |
| SU-02 Acceso RECEPCIONISTA | Abrir `usuarios.php` mediante URL | Respuesta 403, sin gestión de cuentas | Aprobada |
| SU-03 Crear RECEPCIONISTA | Registrar usuario válido | Cuenta creada con contraseña hasheada | Aprobada |
| SU-04 Usuario duplicado | Registrar nombre existente | Se rechaza | Aprobada |
| SU-05 Contraseña corta | Contraseña menor de 8 caracteres | Se rechaza | Aprobada |
| SU-06 Editar nombre | Cambiar nombre de usuario | Actualización correcta | Aprobada |
| SU-07 Cambiar rol | Modificar el rol de otra cuenta | Se guarda un rol válido | Aprobada |
| SU-08 Restablecer contraseña | Establecer nueva contraseña y acceder | La nueva contraseña funciona | Aprobada |
| SU-09 Desactivar usuario | Desactivar cuenta y probar inicio de sesión | Acceso denegado | Aprobada |
| SU-10 Activar usuario | Reactivar cuenta y probar inicio de sesión | Acceso permitido | Aprobada |
| SU-11 Desactivar cuenta propia | Intentar desactivar el propio ADMIN | Se rechaza | Aprobada |
| SU-12 Eliminar cuenta propia | Intentar eliminar el propio ADMIN | Se rechaza | Aprobada |
| SU-13 Quitar rol último ADMIN | Intentar degradar al último ADMIN activo | Se rechaza | Aprobada |
| SU-14 Eliminar último ADMIN | Intentar eliminar último ADMIN activo | Se rechaza | Aprobada |
| SU-15 Eliminar otro usuario | Eliminar cuenta distinta permitida | Cuenta eliminada | Aprobada |

**Otras pruebas reportadas:** inicio de sesión, cierre de sesión, validación de campos y contraseñas incorrectas, menú diferenciado por rol, navegación en el layout y acceso directo restringido a Gestión de Usuarios: funcionamiento correcto en el entorno local.

**Pendiente de evidencia:** añadir capturas representativas de login, ambos roles, gestión de usuarios y rechazo de permisos. Las pruebas de cambio de rol/desactivación **durante una sesión ya abierta** deberán documentarse con su resultado real cuando se completen.
