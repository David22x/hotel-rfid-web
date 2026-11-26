// Envía la tarjeta al servidor y muestra el resultado
async function validarTarjeta(uid) {
    const datos = new URLSearchParams({
        habitacion_id: document.getElementById('cmbHabitacion').value,
        uid: uid
    });
    let respuesta;
    try {
        const r = await fetch('api/validar_acceso.php', { method: 'POST', body: datos });
        respuesta = await r.json();
    } catch (error) {
        alert('No se pudo validar la tarjeta.');
        return;
    }
    if (!respuesta.ok) {
        alert(respuesta.error);
        return;
    }

    mostrarResultado(respuesta);
    document.getElementById('lblUltimo').innerText = respuesta.ultimoEvento;
    LectorRFID.enviar(respuesta.respuesta);
}

// Indicador verde o rojo durante 4 segundos
function mostrarResultado(respuesta) {
    const indicador = document.getElementById('indicador');
    const resultado = document.getElementById('lblResultado');

    if (respuesta.concedido) {
        indicador.className = 'indicador concedido';
        resultado.innerText = 'ACCESO CONCEDIDO';
    } else {
        indicador.className = 'indicador denegado';
        resultado.innerText = 'ACCESO DENEGADO';
    }
    document.getElementById('lblDetalle').innerText = respuesta.mensaje + ' (UID ' + respuesta.uid + ')';

    setTimeout(mostrarEsperando, 4000);
}

function mostrarEsperando() {
    document.getElementById('indicador').className = 'indicador esperando';
    document.getElementById('lblResultado').innerText = 'Esperando tarjeta';
    document.getElementById('lblDetalle').innerText = '';
}

if (document.getElementById('cmbHabitacion')) {
    prepararLector(validarTarjeta);
}
