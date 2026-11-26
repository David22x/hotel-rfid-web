# Diagrama de flujo de la cerradura

Muestra lo que pasa desde que se acerca una tarjeta a la puerta hasta que se enciende el LED.
Lo implementan `cerradura.js`, `api/validar_acceso.php` y `CerraduraService::validar(...)`.

```mermaid
flowchart TD
    A([Se acerca una tarjeta]) --> B{¿Lector real o simulador?}
    B -- Lector RC522 --> C[El Arduino envía el UID por Web Serial]
    B -- Simulador --> D[Se escribe el UID en cerradura.php]
    C --> E[cerradura.js envía el UID con $.post a api/validar_acceso.php]
    D --> E
    E --> V{¿Sesión, permiso y token CSRF válidos?}
    V -- No --> ERR[Responde un error en JSON]
    V -- Sí --> F{1. ¿El UID está registrado como llave?}
    F -- No --> X1[UID_NO_REGISTRADO]
    F -- Sí --> G{2. ¿La llave está ACTIVA?}
    G -- No --> X2[LLAVE_INACTIVA]
    G -- Sí --> H{3. ¿La hora está dentro de la vigencia?}
    H -- No --> X3[FUERA_DE_HORARIO]
    H -- Sí --> I{4. ¿La reserva es de esta habitación?}
    I -- No --> X4[HABITACION_INCORRECTA]
    I -- Sí --> J{5. ¿La reserva tiene check-in activo?}
    J -- No --> X5[RESERVA_NO_ACTIVA]
    J -- Sí --> OK[CONCEDIDO]
    X1 --> N[Registrar ACCESO_DENEGADO en la bitácora]
    X2 --> N
    X3 --> N
    X4 --> N
    X5 --> N
    OK --> P[Registrar ACCESO_CONCEDIDO en la bitácora]
    N --> Q[Indicador rojo y se envía NO al Arduino]
    P --> R[Indicador verde y se envía OK al Arduino]
    Q --> S([LED rojo])
    R --> T([LED verde])
```

## Vencimiento automático de llaves

La tarea cron del hosting ejecuta `cron/expirar_llaves.php` cada minuto.

```mermaid
flowchart TD
    A([Cada minuto]) --> B[Buscar llaves ACTIVA cuya hora de salida ya pasó]
    B --> C{¿Hay alguna?}
    C -- No --> A
    C -- Sí --> D[Marcarlas como VENCIDA]
    D --> E[Registrar LLAVE_VENCIDA en la bitácora]
    E --> A
```

Una llave vencida ya no abre la puerta porque falla la regla 2.
