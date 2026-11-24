
# Contribuciones
## Sandra — Autenticación, sesiones, permisos y gestión de usuarios

**Ramas:** `feature/sandra-login`, `feature/sandra-layout`.

**Aportes:** inicialización común (`bootstrap.php`), `Sesion`, `Csrf`, manejo de errores de negocio, `AuthService` con verificación de contraseña, login y logout, `PermisoService`, menú por rol, integración del layout lateral y Gestión de Usuarios (servicio, controlador y vista), con validaciones de cuenta propia y último administrador.

**Pruebas:** autenticación y navegación comprobadas localmente; 15 pruebas manuales de usuarios reportadas como aprobadas. La documentación de sus casos y resultados está en el apartado de Sandra de `docs/pruebas.md`.

**Commits del PR de autenticación:** `e83fb60`, `e973d8c`, `1c4543b`.

**Pull Requests:** PR #3 (`feature/sandra-login` → `develop`) integrado; PR de `feature/sandra-layout` creado (agregar número y estado al confirmar en GitHub).

**Colaboración:** reutilización de los DAO/modelos de David; coordinación de protección de Habitaciones y Huéspedes con Jair; preparación de clases compartidas para Martin y Evelyn. El evento `INICIO_SESION` depende de la implementación del `EventoDAO` por David.
