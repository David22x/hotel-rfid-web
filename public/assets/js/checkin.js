// checkin.js - pantalla de Check-In / Check-Out
// Cuando el lector (o el simulador) lee una tarjeta, su UID llena el campo
// del formulario de check-in. El recepcionista revisa y pulsa "Registrar check-in".
prepararLector(function (uid) {
    const campoUid = document.getElementById('uid');
    campoUid.value = uid;
    campoUid.focus();

    // Luz verde en el Arduino para confirmar que la tarjeta se leyó.
    LectorRFID.enviar('OK');
});

// Además del lector USB de este navegador, recoge las lecturas que el puente
// (arduino/puente_serial.py) envía a /api/lectura.php. Cada una llega una sola vez.
// Si el servidor no responde, se reintenta en silencio.
(function () {
    const campo = document.getElementById('uid');
    const estado = document.getElementById('estado_lector');

    async function consultar() {
        try {
            const r = await fetch('api/ultima_lectura.php', { credentials: 'same-origin', cache: 'no-store' });
            const datos = await r.json();
            if (datos.uid) {
                campo.value = datos.uid;
                estado.textContent = 'Tarjeta recibida: ' + datos.uid;
            }
        } catch (error) {
            // Sin conexión: no se notifica.
        }
        setTimeout(consultar, 1500);
    }

    consultar();
})();
