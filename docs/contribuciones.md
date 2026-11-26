
# Contribuciones
## Sandra — Autenticación, sesiones, permisos y gestión de usuarios

**Ramas:** `feature/sandra-login`, `feature/sandra-layout`.

**Aportes:** inicialización común (`bootstrap.php`), `Sesion`, `Csrf`, manejo de errores de negocio, `AuthService` con verificación de contraseña, login y logout, `PermisoService`, menú por rol, integración del layout lateral y Gestión de Usuarios (servicio, controlador y vista), con validaciones de cuenta propia y último administrador.

**Pruebas:** autenticación y navegación comprobadas localmente; 15 pruebas manuales de usuarios reportadas como aprobadas. La documentación de sus casos y resultados está en el apartado de Sandra de `docs/pruebas.md`.

**Commits del PR de autenticación:** `e83fb60`, `e973d8c`, `1c4543b`.

**Pull Requests:** PR #3 (`feature/sandra-login` → `develop`) integrado; PR de `feature/sandra-layout` creado (agregar número y estado al confirmar en GitHub).

**Colaboración:** reutilización de los DAO/modelos de David; coordinación de protección de Habitaciones y Huéspedes con Jair; preparación de clases compartidas para Martin y Evelyn. El evento `INICIO_SESION` depende de la implementación del `EventoDAO` por David.

## Michael — Hardware y lector RFID

**Rama:** `feature/michael-hardware`.

**Aportes:** programa del Arduino con el RC522 (`arduino/lector_rfid.ino`) con envío del UID y LEDs verde/rojo según `OK`/`NO`; `lector-rfid.js` con Web Serial, reconexión automática y modo simulador; controles comunes del lector (`app/view/lector/controles.php`); página de prueba `prueba-lector.html`; integración del lector en la pantalla de check-in (`checkin.js`); documentación del cableado y del protocolo en `docs/hardware.md`.

**Pruebas:** casos HW-01 a HW-18 en la sección de Michael de `docs/pruebas.md`.

**Pull Requests:** PR de `feature/michael-hardware` → `develop` (agregar número al confirmar en GitHub).

**Colaboración:** integración del lector en check-in con Martín; guía de uso del lector para la cerradura de Evelyn en `docs/hardware.md`.
