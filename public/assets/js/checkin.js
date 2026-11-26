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
