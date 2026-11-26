# Evelyn Larraga

Responsable de la cerradura RFID y la bitácora de Hotel RFID Web.

## Día 1

- Validar el acceso con las 5 reglas en `CerraduraService::validar(...)`.
- Crear `api/validar_acceso.php`, que responde en JSON y revisa el token CSRF y el permiso.
- Crear la pantalla `cerradura.php` con el campo de simulación.
- Crear `BitacoraService` con filtros por fecha y habitación.
- Usar los DAO de David: `LlaveDAO::buscarPorUid`, `ReservaDAO::buscarPorId` y `EventoDAO::registrar`.

## Día 2

- `cerradura.js` con `$.post`, el indicador verde o rojo y el último evento.
- `cron/expirar_llaves.php`, que vence las llaves cada minuto.
- Filtros por fecha y habitación en la pantalla de bitácora que creó Martin.
- Reporte PDF de la bitácora con FPDF.
- Diagrama de flujo de la cerradura.

## Para el equipo

- **Michael:** `cerradura.js` usa `LectorRFID.conectar(alLeerUid)` y `LectorRFID.enviar(texto)` de `lector-rfid.js`. Cada UID leído se pasa a la función que recibe `conectar`.
- **Martin:** la cerradura acepta el mismo formato de UID que el check-in (4 a 20 caracteres hexadecimales). La bitácora conserva tu tabla y le agrega los filtros y el botón del PDF.
- **David:** `cron/expirar_llaves.php` usa `listarActivasVencidas` y `marcarVencida`, y solo registra `LLAVE_VENCIDA` cuando la llave cambió de estado. Ya se puede activar como indica `docs/despliegue.md`.
