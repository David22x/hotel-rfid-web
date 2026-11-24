# Sandra — Hotel RFID Web

**Rol:** Autenticación, seguridad de sesiones, interfaz común y gestión de usuarios.  
**Proyecto:** Hotel RFID Web (PHP 8, MySQL, HTML, CSS y JavaScript).  
**Ramas de trabajo:** `feature/sandra-login` y `feature/sandra-layout`.

## Responsabilidades según la planificación

- Preparar `app/bootstrap.php`, `app/core/Sesion.php`, `app/core/Csrf.php` y el manejo común de errores de negocio.
- Implementar `AuthService`, las páginas de inicio y cierre de sesión y el menú según el rol.
- Implementar `PermisoService` con acceso diferenciado para ADMIN y RECEPCIONISTA.
- Integrar el menú con el layout y los estilos compartidos del sistema.
- Implementar `UsuarioService`, `UsuarioController` y las vistas de gestión de usuarios.
- Verificar permisos en el servidor, documentar pruebas, casos de uso y manual de login.

## Desarrollo realizado

| Componente | Función |
| --- | --- |
| `app/bootstrap.php` | Inicialización de sesión, configuración y clases comunes. |
| `app/core/Sesion.php` | Inicio y cierre de sesión; exige autenticación y permisos; revisión del estado y rol vigentes en MySQL en peticiones posteriores. |
| `app/core/Csrf.php` | Generación y validación de tokens CSRF. |
| `app/core/ReglaNegocioException.php` | Errores legibles de las reglas de negocio. |
| `app/service/AuthService.php` | Consulta a `UsuarioDAO` y comprobación de contraseñas con `password_verify()`. |
| `app/service/PermisoService.php` | Permisos por módulo para ADMIN y RECEPCIONISTA. |
| `public/index.php`, `login.php`, `logout.php`, `menu.php` | Inicio, autenticación, salida y navegación. |
| `app/view/login/formulario.php` | Formulario de acceso. |
| `app/service/UsuarioService.php` | Validación de usuarios, contraseñas, roles, cuentas propias y último administrador activo. |
| `app/controller/UsuarioController.php`, `public/usuarios.php`, `app/view/usuarios/listar.php` | Gestión de usuarios mediante solicitudes GET y POST, con permisos y CSRF. |
| `app/view/layout/inicio.php`, `public/menu.php`, `public/assets/css/estilo.css` | Menú lateral común, información del usuario y opciones según rol. |

## Verificación

- Las pruebas de inicio y cierre de sesión y navegación fueron reportadas como satisfactorias durante el desarrollo local.
- Se ejecutaron y reportaron como aprobadas **15 pruebas manuales** de Gestión de Usuarios (creación, duplicados, contraseñas, edición, roles, activación, desactivación, eliminación y protección de administradores).
- Se reportó correcto el funcionamiento del layout con los roles ADMIN y RECEPCIONISTA.
- Las comprobaciones específicas de sesión abierta después de cambios administrativos deben incorporarse con su resultado únicamente después de ejecutarlas.

## Integración con compañeros

- **David:** se reutilizan `UsuarioDAO`, `Usuario`, `RolUsuario`, conexión PDO y configuración de datos. El registro de `INICIO_SESION` depende de que implemente `EventoDAO::registrar()`.
- **Jair:** sus controladores de Habitaciones y Huéspedes deben adoptar `Sesion` y `Csrf` en sus propias ramas; Sandra no implementó sus operaciones.
- **Martin y Evelyn:** deberán aplicar los controles compartidos de sesión, permisos y CSRF en sus módulos.
- **Michael:** hardware RFID y lectura mediante JavaScript, sin intervención directa de Sandra.

## Git e integración

- **PR #3:** `feature/sandra-login` → `develop`, integrado. Commits: `e83fb60`, `e973d8c`, `1c4543b`.
- **Segundo PR:** `feature/sandra-layout` → `develop`, creado y pendiente de confirmar su integración. No se inventan hashes de commits de esta rama.

## Documentación relacionada

Ver [Manual de inicio de sesión y casos de uso](manual-login.md). Las pruebas individuales de Sandra deben añadirse a `docs/pruebas.md` sin reemplazar las de los demás integrantes.
