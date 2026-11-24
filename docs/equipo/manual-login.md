# Manual de inicio de sesión y casos de uso — Hotel RFID Web

**Responsable:** Sandra  
**Alcance:** Inicio de sesión, cierre de sesión, menú por rol y administración de usuarios.  
**Versión:** Aplicación web con PHP 8 y MySQL.

## 1. Objetivo

Explicar el acceso al sistema y las operaciones de gestión de usuarios. La aplicación utiliza sesiones PHP, verificación de contraseñas mediante hashes BCrypt y validaciones de permisos en el servidor.

## 2. Preparación

1. Iniciar Apache y MySQL desde XAMPP o Laragon.
2. Colocar el proyecto en la carpeta local del servidor.
3. Crear `app/config/config.php` usando `config.example.php` y configurar la conexión MySQL. **No subir este archivo a Git**.
4. Importar `database/schema.sql` y `database/datos_demo.sql` en la base de pruebas, si no están cargados.
5. Abrir `http://localhost/hotel-rfid-web/public/` (ajustar la ruta si el proyecto se instaló en otra ubicación).

## 3. Iniciar sesión

1. En el formulario escribir nombre de usuario y contraseña.
2. Pulsar **Ingresar**.
3. El sistema valida el token CSRF, busca la cuenta mediante `UsuarioDAO` y verifica su contraseña con `password_verify()`.
4. Si la cuenta es válida y activa, inicia una sesión PHP y dirige al menú.
5. Si faltan datos o las credenciales no son correctas, muestra un mensaje sin revelar cuál de los datos falló.

**Seguridad:** la contraseña se comprueba contra su hash almacenado; no se guarda en `$_SESSION`.

**Evidencia:** insertar captura del formulario y otra del menú tras ingresar correctamente.

## 4. Permisos según el rol

| Módulo | ADMIN | RECEPCIONISTA |
| --- | --- | --- |
| Habitaciones | Acceso completo según reglas del módulo | Solo consulta |
| Huéspedes | Sí | Sí |
| Reservas | Sí | Sí |
| Check-In / Check-Out | Sí | Sí |
| Cerradura RFID | Sí | No |
| Bitácora | Sí | No |
| Gestión de Usuarios | Sí | No |

El menú muestra las opciones permitidas y disponibles en la versión actual. **Ocultar enlaces no reemplaza la validación del servidor.** La protección de operaciones internas de Habitaciones, Huéspedes y otros módulos corresponde a sus responsables.

**Evidencia:** insertar capturas del menú con ADMIN y RECEPCIONISTA.

## 5. Gestión de Usuarios (ADMIN)

1. Iniciar sesión como administrador.
2. Abrir **Usuarios** desde el menú lateral.
3. Para **crear**, ingresar nombre de usuario, contraseña de al menos ocho caracteres y rol; pulsar **Crear usuario**.
4. Para **editar**, modificar nombre o rol en la fila del usuario y pulsar **Guardar**.
5. Para **restablecer contraseña**, ingresar una contraseña nueva y pulsar **Restablecer**.
6. Para **activar o desactivar**, utilizar el botón de estado del usuario correspondiente.
7. Para **eliminar**, utilizar **Eliminar** y confirmar la acción.

### Restricciones importantes

- No se aceptan nombres de usuario duplicados.
- Las contraseñas nuevas se guardan mediante `password_hash()` con BCrypt.
- Un administrador no puede desactivar ni eliminar su propia cuenta ni quitarse su propio rol ADMIN.
- No se permite eliminar, desactivar o cambiar el rol al último administrador activo.
- Las solicitudes que modifican datos utilizan POST y validación CSRF.
- Una cuenta RECEPCIONISTA no puede acceder a `usuarios.php`, incluso introduciendo la URL directamente.

**Evidencias:** insertar capturas del listado, creación y mensajes de validación.

## 6. Cerrar sesión

1. Pulsar **Cerrar sesión** en el menú lateral.
2. Se envía una solicitud POST con token CSRF.
3. El sistema limpia los datos de sesión y regresa al formulario de acceso.
4. Al visitar una página protegida se exige iniciar sesión nuevamente.

## 7. Casos de uso

### CU-01. Iniciar sesión

- **Actor:** ADMIN o RECEPCIONISTA.
- **Precondición:** cuenta existente y activa.
- **Flujo:** acceder al formulario → escribir credenciales → validar datos y contraseña → abrir sesión → mostrar menú.
- **Alternativas:** datos vacíos o credenciales incorrectas → mostrar mensaje de error y permanecer en el formulario.
- **Postcondición:** sesión de usuario autenticado.

### CU-02. Consultar módulos permitidos

- **Actor:** ADMIN o RECEPCIONISTA.
- **Precondición:** sesión activa.
- **Flujo:** obtener usuario y rol → comprobar permisos → presentar enlaces autorizados en el menú.
- **Alternativa:** intentar acceder a un módulo prohibido → el servidor responde con acceso denegado si el controlador correspondiente usa la verificación requerida.
- **Postcondición:** navegación acorde al rol.

### CU-03. Cerrar sesión

- **Actor:** usuario autenticado.
- **Precondición:** sesión activa.
- **Flujo:** seleccionar Cerrar sesión → validar CSRF → destruir sesión → regresar al formulario.
- **Postcondición:** sesión finalizada.

### CU-04. Gestionar usuarios

- **Actor:** ADMIN.
- **Precondición:** sesión ADMIN válida.
- **Flujo:** abrir Usuarios → escoger creación, edición, estado, contraseña o eliminación → enviar formulario con CSRF → validar reglas del servicio → persistir mediante `UsuarioDAO` → mostrar confirmación.
- **Alternativas:** nombre repetido, contraseña corta, acción sobre la propia cuenta o último ADMIN activo → rechazar con mensaje; rol RECEPCIONISTA → HTTP 403.
- **Postcondición:** operación válida registrada en la base de datos.

## 8. Alcance y pendientes de integración

- El registro del evento `INICIO_SESION` se añadirá cuando David complete y pruebe `EventoDAO::registrar()`.
- Los controladores de Habitaciones y Huéspedes necesitan integrar verificaciones propias de sesión, operaciones y CSRF en la rama de Jair.
- Las pruebas de revocación de sesiones abiertas después de modificar o desactivar cuentas se documentarán como aprobadas solo cuando se ejecuten y comprueben.
