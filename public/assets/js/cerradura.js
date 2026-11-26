// Pantalla de la cerradura RFID.
// Usa $.post de jQuery para validar la tarjeta sin recargar la página.

// Envía el UID al servidor, que revisa las 5 reglas y responde en JSON
function validarTarjeta(uid) {
    var datos = {
        csrf_token: document.getElementById("csrf").value,
        habitacion_id: document.getElementById("cmbHabitacion").value,
        uid: uid
    };

    $.post("api/validar_acceso.php", datos, function (respuesta) {
        if (!respuesta.ok) {
            alert(respuesta.error);
            return;
        }

        mostrarResultado(respuesta);
        document.getElementById("lblUltimo").innerText = respuesta.ultimoEvento;

        // Responde OK o NO al Arduino para encender el LED verde o rojo.
        // Si no hay lector conectado (simulador), no hace nada.
        LectorRFID.enviar(respuesta.respuesta);
    }, "json");
}

// Pinta el círculo de verde o rojo y a los 4 segundos lo regresa a gris
function mostrarResultado(respuesta) {
    var indicador = document.getElementById("indicador");
    var resultado = document.getElementById("lblResultado");

    if (respuesta.concedido) {
        indicador.className = "indicador concedido";
        resultado.innerText = "ACCESO CONCEDIDO";
    } else {
        indicador.className = "indicador denegado";
        resultado.innerText = "ACCESO DENEGADO";
    }
    document.getElementById("lblDetalle").innerText = respuesta.mensaje + " (UID " + respuesta.uid + ")";

    setTimeout(mostrarEsperando, 4000);
}

function mostrarEsperando() {
    document.getElementById("indicador").className = "indicador esperando";
    document.getElementById("lblResultado").innerText = "Esperando tarjeta";
    document.getElementById("lblDetalle").innerText = "";
}

// Cada UID que llega del lector o del simulador de Michael se valida en el servidor
if (document.getElementById("csrf")) {
    prepararLector(validarTarjeta);
}
