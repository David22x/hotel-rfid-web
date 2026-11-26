# Michael Rosillo

Responsable del hardware y del lector RFID de Hotel RFID Web.

## Módulo

- `arduino/lector_rfid.ino`: lee el UID de la tarjeta con el RC522 y lo envía por USB.
- `public/assets/js/lector-rfid.js`: conecta el navegador con el Arduino (Web Serial API), recibe el UID, envía `OK`/`NO` y ofrece el modo simulador.
- `public/prueba-lector.html`: página para probar el lector sin iniciar sesión.
- `docs/hardware.md`: cableado, protocolo serial y pruebas del lector.

## Tareas

- Cablear el RC522 al Arduino Uno y probarlo con el monitor serial.
- Programar el envío del UID (una línea por lectura, pausa de 1 s).
- Programar `lector-rfid.js` con `conectar`, `enviar` y `simular`.
- Encender el LED verde o rojo según la respuesta `OK` o `NO`.
- Integrar el lector en la pantalla de check-in (con Martín) y en la cerradura (con Evelyn).
- Probar el lector en `localhost` y en el dominio con HTTPS.
- Documentar las pruebas en `docs/pruebas.md`.
