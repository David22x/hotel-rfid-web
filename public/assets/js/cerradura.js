// Pantalla de la cerradura RFID.
// Usa $.post de jQuery para validar la tarjeta sin recargar la página.

var lectorConectado = false;

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

        // Con el lector real se responde OK o NO al Arduino para encender el LED
        if (lectorConectado) {
            LectorRFID.enviar(respuesta.respuesta);
        }
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

// Botón "Simular lectura": revisa el formato antes de enviarlo
function simularLectura() {
    var uid = document.getElementById("txtUid").value.trim().toUpperCase();

    if (!/^[0-9A-F]{4,20}$/.test(uid)) {
        alert("Escriba un UID hexadecimal válido, por ejemplo A3F1C20B.");
        return;
    }
    validarTarjeta(uid);
}

// Botón "Conectar lector": usa lector-rfid.js, que hace Michael.
// Cada UID que llega del Arduino se valida igual que en el simulador.
function conectarLector() {
    if (typeof LectorRFID === "undefined") {
        alert("Todavía no está el archivo lector-rfid.js. Use el simulador.");
        return;
    }

    LectorRFID.conectar(validarTarjeta);
    lectorConectado = true;
    document.getElementById("lblLector").innerText = "Lector conectado";
    document.getElementById("btnConectar").disabled = true;
}
