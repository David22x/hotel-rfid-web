# Hardware: lector RFID

Responsable: Michael Rosillo.

El Arduino va por USB al computador de recepción. Hay dos formas de leerlo (**solo una a la vez**, porque el puerto serial admite un único programa):

1. **Puente desacoplado** (`arduino/puente_serial.py`): envía cada lectura al servidor local y a la nube. Ver [Puente local y nube](#puente-local-y-nube).
2. **Navegador** (Chrome o Edge de escritorio) con la **Web Serial API** desde `public/assets/js/lector-rfid.js`: la lectura solo llega a la pantalla abierta, no se envía a otros servidores.

```
Tarjeta → RC522 → Arduino Uno → USB → Chrome/Edge (lector-rfid.js) → check-in / cerradura
```

## Materiales

- Arduino Uno y cable USB.
- Módulo RC522 con tarjetas o llaveros MIFARE.
- LED verde y LED rojo con dos resistencias de 220 Ω (opcional).
- Protoboard y cables dupont.

## Conexión

> El RC522 funciona a **3.3 V**. Conectarlo a 5 V puede dañarlo.

| RC522 | Arduino Uno |
| --- | --- |
| SDA (SS) | 10 |
| SCK | 13 |
| MOSI | 11 |
| MISO | 12 |
| IRQ | sin conectar |
| GND | GND |
| RST | 9 |
| 3.3V | 3.3V |

| LED | Arduino Uno |
| --- | --- |
| Verde (ánodo, pata larga) | 6, con resistencia de 220 Ω |
| Rojo (ánodo, pata larga) | 7, con resistencia de 220 Ω |
| Cátodos (pata corta) | GND |

No se usa el LED integrado del pin 13 porque ese pin es SCK del RC522.

## Programa del Arduino

Archivo: `arduino/lector_rfid.ino`.

1. Instalar el Arduino IDE.
2. En *Herramientas > Administrar bibliotecas*, instalar **MFRC522** (GithubCommunity).
3. Abrir `arduino/lector_rfid.ino`, elegir la placa *Arduino Uno* y el puerto COM, y cargar.

## Protocolo serial (9600 baudios)

| Dirección | Mensaje | Ejemplo |
| --- | --- | --- |
| Arduino → navegador | UID en hexadecimal mayúsculas, una línea por lectura. Pausa de 1 s tras cada lectura | `A3F1C20B` |
| Navegador → Arduino | `OK` o `NO` con salto de línea | `OK` |

Con `OK` se enciende el LED verde y con `NO` el rojo, durante 2 segundos.

## lector-rfid.js

| Función | Qué hace |
| --- | --- |
| `LectorRFID.conectar(alLeerUID)` | Pide elegir el puerto del Arduino (debe llamarse desde un clic) y llama `alLeerUID(uid)` en cada lectura |
| `LectorRFID.enviar(mensaje)` | Envía `OK` o `NO`. Sin lector conectado no hace nada |
| `LectorRFID.simular(uid)` | Modo simulador: valida el UID y llama a la misma función que el lector real |
| `LectorRFID.escuchar(alLeerUID, alCambiarEstado)` | Guarda las funciones y reconecta solo si el puerto ya fue autorizado |
| `LectorRFID.normalizarUid(texto)` | Devuelve el UID en mayúsculas, o `null` si no es hexadecimal de 8 a 20 caracteres |

### Cómo usarlo en una pantalla

1. En la vista PHP, incluir los controles comunes (botón *Conectar lector*, estado y simulador):

   ```php
   <?php require __DIR__ . '/../lector/controles.php'; ?>
   ```

2. En el JS de la pantalla, decir qué hacer con cada UID:

   ```js
   prepararLector(function (uid) {
       // qué hacer con el UID leído o simulado
   });
   ```

**Check-in** (`checkin.js`): el UID llena el campo del formulario y se envía `OK` para encender el LED verde.

**Cerradura** (`cerradura.js`, de Evelyn): el UID se envía a la API y la respuesta enciende el LED.

```js
prepararLector(async function (uid) {
    const datos = new FormData();
    datos.append('uid', uid);
    datos.append('habitacion_id', document.getElementById('habitacion_id').value);
    datos.append('csrf_token', document.querySelector('input[name=csrf_token]').value);

    const respuesta = await fetch('api/validar_acceso.php', { method: 'POST', body: datos });
    const resultado = await respuesta.json(); // { permitido: true/false, motivo: "..." }

    // ...mostrar verde o rojo con resultado.motivo...
    LectorRFID.enviar(resultado.permitido ? 'OK' : 'NO');
});
```

## Puente local y nube

```
Tarjeta → RC522 → Arduino → USB → puente_serial.py ─┬→ API local (XAMPP, MySQL)
                                                    └→ API en Azure (PostgreSQL)
```

1. `pip install pyserial requests`
2. Copiar `arduino/puente.example.json` como `arduino/puente.json` y ajustar puerto, modo y URLs.
3. En cada servidor, tener la tabla `lectura_rfid` (`database/schema.sql` en MySQL, `database/schema_pgsql.sql` en PostgreSQL).
4. `python arduino/puente_serial.py` (con `--detalle` para diagnosticar).

Las rutas `api/lectura.php` y `api/acceso.php` no piden autenticación: cualquiera que conozca la URL puede enviarles lecturas.

Cada servidor trabaja con sus propios datos. Un servidor que no responde se ignora sin avisar. En modo `checkin`, la pantalla de check-in abierta recibe el UID. En modo `cerradura`, el orden de `servidores` es la prioridad: decide el primero que conozca la tarjeta; `habitacion_id` se configura por servidor.

## Requisitos y problemas comunes

- **Navegador:** solo Chrome o Edge de escritorio. En Firefox y Safari no hay Web Serial: se usa el campo **Simular lectura**.
- **HTTPS:** en el dominio la página debe abrirse con `https://`. En `localhost` funciona sin HTTPS.
- **Primera conexión:** el navegador exige un clic en *Conectar lector* y elegir el puerto. Después, al recargar la página (por ejemplo tras un check-in), se reconecta solo.
- **Un solo programa a la vez:** si el monitor serial del Arduino IDE u otra pestaña tiene el puerto abierto, no se puede conectar. Cerrarlos.
- **Al conectar, el Arduino se reinicia** y tarda unos 2 segundos en leer.

## Pruebas del lector

Página de prueba sin login: `public/prueba-lector.html` (por ejemplo `http://localhost/Proy_Visual_Final/hotel-rfid-web/public/prueba-lector.html`). Muestra cada UID leído y tiene botones para enviar `OK` y `NO`.

| Prueba | Resultado |
| --- | --- |
| Monitor serial del Arduino IDE a 9600: acercar una tarjeta | Pendiente: anotar el UID leído |
| `prueba-lector.html` en `localhost` con Chrome | Pendiente |
| `prueba-lector.html` en el dominio con HTTPS | Pendiente |
| Botones `OK` y `NO` encienden los LEDs | Pendiente |

Los resultados detallados están en la sección de Michael de `docs/pruebas.md`.

## Esquema y video

- Esquema de conexión: `docs/diagramas/conexion-rc522.png` (pendiente de foto o dibujo).
- Video corto de la prueba: pendiente (enlace).
