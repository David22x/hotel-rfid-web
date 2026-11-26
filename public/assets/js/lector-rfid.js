/*
 * lector-rfid.js - Hotel RFID Web
 * Autor: Michael Rosillo
 *
 * Conecta el navegador con el Arduino (lector RC522) usando la Web Serial API.
 * Hace lo mismo que LectorRFID.java del sistema de escritorio:
 *
 *   LectorRFID.conectar(alLeerUID)  -> abre el puerto USB y llama alLeerUID(uid) en cada lectura
 *   LectorRFID.enviar(mensaje)      -> envía "OK" o "NO" al Arduino (LED verde o rojo)
 *   LectorRFID.simular(uid)         -> MODO SIMULADOR: llama a la misma función con un UID escrito a mano
 *
 * Funciones de apoyo:
 *   LectorRFID.escuchar(alLeerUID, alCambiarEstado) -> guarda las funciones y reconecta solo
 *                                                    si el puerto ya fue autorizado antes
 *   LectorRFID.soportado()     -> true si el navegador tiene Web Serial (Chrome o Edge de escritorio)
 *   LectorRFID.estaConectado() -> true si hay un Arduino conectado
 *   LectorRFID.normalizarUid(texto) -> UID en mayúsculas o null si no es válido
 *
 * Requisitos de Web Serial: Chrome o Edge de escritorio, y la página en HTTPS
 * (en localhost funciona sin HTTPS). Firefox y Safari NO lo soportan: ahí se usa el simulador.
 */
const LectorRFID = (function () {
    const VELOCIDAD = 9600;                  // mismos baudios que el Arduino
    const FORMATO_UID = /^[0-9A-F]{8,20}$/;  // hexadecimal de 8 a 20 caracteres

    let puerto = null;          // puerto serial abierto
    let lector = null;          // lector del flujo de datos del puerto
    let funcionLectura = null;  // función que recibe cada UID leído
    let funcionEstado = null;   // función que recibe los mensajes de estado

    // ---------- Funciones de apoyo ----------

    function soportado() {
        return 'serial' in navigator;
    }

    function estaConectado() {
        return puerto !== null;
    }

    // Quita espacios, pasa a mayúsculas y valida el formato. Devuelve null si no es válido.
    function normalizarUid(texto) {
        const uid = String(texto || '').trim().toUpperCase();
        return FORMATO_UID.test(uid) ? uid : null;
    }

    function avisarEstado(texto) {
        if (funcionEstado) {
            funcionEstado(texto);
        }
    }

    // Entrega el UID a la pantalla (check-in o cerradura).
    function entregarUid(texto) {
        const uid = normalizarUid(texto);
        if (uid === null) {
            return; // líneas vacías o ruido del puerto: se ignoran
        }
        if (funcionLectura) {
            funcionLectura(uid);
        }
    }

    // ---------- Conexión ----------

    // Abre el puerto y empieza a escuchar (no espera a que termine la lectura).
    async function abrirPuerto(puertoElegido) {
        await puertoElegido.open({ baudRate: VELOCIDAD });
        puerto = puertoElegido;
        avisarEstado('Lector conectado');
        leerLineas(); // queda escuchando en segundo plano
    }

    // Lee el puerto sin parar y corta el texto en líneas (una línea = un UID).
    async function leerLineas() {
        const decodificador = new TextDecoder();
        let acumulado = '';

        try {
            lector = puerto.readable.getReader();
            while (true) {
                const { value, done } = await lector.read();
                if (done) {
                    break;
                }
                acumulado += decodificador.decode(value, { stream: true });

                // Mientras haya un salto de línea, separa una línea completa.
                let posicion = acumulado.indexOf('\n');
                while (posicion >= 0) {
                    entregarUid(acumulado.slice(0, posicion));
                    acumulado = acumulado.slice(posicion + 1);
                    posicion = acumulado.indexOf('\n');
                }
            }
        } catch (error) {
            // Pasa, por ejemplo, si desconectan el cable USB.
            console.error('Error leyendo el lector RFID:', error);
        }

        await cerrarPuerto();
        avisarEstado('Lector desconectado');
    }

    async function cerrarPuerto() {
        try {
            if (lector) {
                lector.releaseLock();
            }
            if (puerto) {
                await puerto.close();
            }
        } catch (error) {
            // El puerto ya estaba cerrado: no hay nada que hacer.
        }
        lector = null;
        puerto = null;
    }

    // Guarda las funciones de la pantalla y, si el usuario ya autorizó el
    // puerto antes, se reconecta solo (útil porque la página se recarga
    // después de cada check-in).
    async function escuchar(alLeerUID, alCambiarEstado) {
        funcionLectura = alLeerUID;
        funcionEstado = alCambiarEstado || null;

        if (!soportado()) {
            avisarEstado('Este navegador no soporta el lector. Use el simulador o Chrome/Edge.');
            return;
        }
        if (estaConectado()) {
            avisarEstado('Lector conectado');
            return;
        }

        const autorizados = await navigator.serial.getPorts();
        if (autorizados.length === 0) {
            avisarEstado('Lector sin conectar');
            return;
        }
        try {
            await abrirPuerto(autorizados[0]);
        } catch (error) {
            // Lo más común: el puerto está abierto en otra pestaña.
            avisarEstado('Lector sin conectar (¿está abierto en otra pestaña?)');
        }
    }

    // Pide al usuario elegir el puerto del Arduino. Debe llamarse desde un clic.
    async function conectar(alLeerUID) {
        if (alLeerUID) {
            funcionLectura = alLeerUID;
        }
        if (!soportado()) {
            throw new Error('Este navegador no soporta Web Serial. Use Chrome o Edge, o el simulador.');
        }
        if (estaConectado()) {
            return;
        }
        let elegido;
        try {
            elegido = await navigator.serial.requestPort();
        } catch (error) {
            throw new Error('No se eligió ningún puerto.');
        }
        try {
            await abrirPuerto(elegido);
        } catch (error) {
            throw new Error('No se pudo abrir el puerto. Cierre el monitor serial del Arduino IDE u otras pestañas.');
        }
    }

    // Envía "OK" o "NO" al Arduino. Si no hay lector conectado no hace nada
    // (así el modo simulador funciona igual sin hardware).
    async function enviar(mensaje) {
        if (!estaConectado() || !puerto.writable) {
            return;
        }
        const escritor = puerto.writable.getWriter();
        try {
            await escritor.write(new TextEncoder().encode(mensaje + '\n'));
        } finally {
            escritor.releaseLock();
        }
    }

    // MODO SIMULADOR: valida el UID escrito y llama a la misma función que el lector real.
    function simular(uid) {
        const uidValido = normalizarUid(uid);
        if (uidValido === null) {
            throw new Error('El UID debe ser hexadecimal (0-9, A-F) y tener de 8 a 20 caracteres.');
        }
        if (funcionLectura) {
            funcionLectura(uidValido);
        }
        return uidValido;
    }

    // Lo único visible desde fuera del archivo:
    return {
        conectar: conectar,
        enviar: enviar,
        simular: simular,
        escuchar: escuchar,
        soportado: soportado,
        estaConectado: estaConectado,
        normalizarUid: normalizarUid
    };
})();

/*
 * Controles comunes del lector (botón "Conectar lector", estado y simulador).
 * Los imprime app/view/lector/controles.php. La pantalla solo llama:
 *
 *   prepararLector(function (uid) { ...qué hacer con el UID... });
 */
function prepararLector(alLeerUID) {
    const botonConectar = document.getElementById('lector-conectar');
    const textoEstado = document.getElementById('lector-estado');
    const campoSimular = document.getElementById('lector-simular-uid');
    const botonSimular = document.getElementById('lector-simular');
    const textoError = document.getElementById('lector-error');

    function mostrarEstado(texto) {
        textoEstado.textContent = texto;
        botonConectar.disabled = LectorRFID.estaConectado();
    }

    function mostrarError(texto) {
        textoError.textContent = texto;
    }

    if (!LectorRFID.soportado()) {
        botonConectar.disabled = true;
    }

    botonConectar.addEventListener('click', async function () {
        mostrarError('');
        try {
            await LectorRFID.conectar(alLeerUID);
        } catch (error) {
            mostrarError(error.message);
        }
    });

    function simularLectura() {
        mostrarError('');
        try {
            LectorRFID.simular(campoSimular.value);
            campoSimular.value = '';
        } catch (error) {
            mostrarError(error.message);
        }
    }

    botonSimular.addEventListener('click', simularLectura);

    // Enter dentro del campo del simulador simula la lectura (y no envía el formulario).
    campoSimular.addEventListener('keydown', function (evento) {
        if (evento.key === 'Enter') {
            evento.preventDefault();
            simularLectura();
        }
    });

    LectorRFID.escuchar(alLeerUID, mostrarEstado);
}
