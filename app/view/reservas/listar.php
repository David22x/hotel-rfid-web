<?php
$tituloPagina = 'Reservas';
$activo = 'reservas';
require __DIR__ . '/../layout/inicio.php';
?>
<h1>Reservas</h1>

<?php if ($mensaje): ?>
    <div class="alerta ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alerta error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="reservas.php?accion=crear" class="form-grid">
    <?php Csrf::campo(); ?>
    <div class="campo">
        <label for="huesped_id">Huésped</label>
        <select id="huesped_id" name="huesped_id" required>
            <option value="">Seleccione un huésped</option>
            <?php foreach ($huespedes as $huesped): ?>
                <option value="<?= (int) $huesped->id ?>">
                    <?= htmlspecialchars($huesped->nombre . ' - ' . $huesped->documento, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo">
        <label for="habitacion_id">Habitación</label>
        <select id="habitacion_id" name="habitacion_id" required>
            <option value="">Seleccione una habitación</option>
            <?php foreach ($habitaciones as $habitacion): ?>
                <option value="<?= (int) $habitacion->id ?>">
                    <?= htmlspecialchars($habitacion->numero . ' - ' . $habitacion->tipo . ' (' . $habitacion->estado . ')', ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <fieldset class="grupo-horario">
        <legend>Entrada</legend>
        <div class="campo">
            <label for="entrada_fecha">Fecha</label>
            <input id="entrada_fecha" type="date" name="entrada_fecha" min="<?= htmlspecialchars(substr($minimo, 0, 10), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="campo">
            <label for="entrada_hora">Hora</label>
            <input id="entrada_hora" type="time" name="entrada_hora" step="60" required>
        </div>
    </fieldset>
    <fieldset class="grupo-horario">
        <legend>Salida</legend>
        <div class="campo">
            <label for="salida_fecha">Fecha</label>
            <input id="salida_fecha" type="date" name="salida_fecha" min="<?= htmlspecialchars(substr($minimo, 0, 10), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="campo">
            <label for="salida_hora">Hora</label>
            <input id="salida_hora" type="time" name="salida_hora" step="60" required>
        </div>
    </fieldset>
    <div class="botones botones-formulario">
        <button type="submit" class="primario">Crear reserva</button>
    </div>
</form>

<div class="barra">
    <strong>Reservas registradas</strong>
    <a href="reservas.php" class="btn">Actualizar tabla</a>
</div>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Huésped</th>
            <th>Habitación</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($reservas as $reserva): ?>
        <tr>
            <td><?= (int) $reserva->id ?></td>
            <td><?= htmlspecialchars($reserva->huespedNombre ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reserva->habitacionNumero ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reserva->fechaEntrada, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reserva->fechaSalida, ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge badge-<?= htmlspecialchars($reserva->estado, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($reserva->estado, ENT_QUOTES, 'UTF-8') ?></span></td>
            <td class="acciones tabla-acciones">
                <?php if ($reserva->estado === EstadoReserva::PENDIENTE): ?>
                    <form method="post" action="reservas.php?accion=cancelar" onsubmit="return confirm('¿Cancelar esta reserva pendiente?')">
                        <?php Csrf::campo(); ?>
                        <input type="hidden" name="id" value="<?= (int) $reserva->id ?>">
                        <button type="submit" class="btn-sm btn-peligro">Cancelar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if (count($reservas) === 0): ?>
    <p class="vacio">No hay reservas registradas.</p>
<?php endif; ?>

<script>
const entradaFecha = document.getElementById('entrada_fecha');
const entradaHora = document.getElementById('entrada_hora');
const salidaFecha = document.getElementById('salida_fecha');
const salidaHora = document.getElementById('salida_hora');

function sumarMinutos(fechaTexto, horaTexto, minutos) {
    const [anio, mes, dia] = fechaTexto.split('-').map(Number);
    const [hora, minuto] = horaTexto.split(':').map(Number);
    const fecha = new Date(anio, mes - 1, dia, hora, minuto + minutos);
    return {
        fecha: [
            fecha.getFullYear(),
            String(fecha.getMonth() + 1).padStart(2, '0'),
            String(fecha.getDate()).padStart(2, '0')
        ].join('-'),
        hora: String(fecha.getHours()).padStart(2, '0') + ':' +
              String(fecha.getMinutes()).padStart(2, '0')
    };
}

function validarRango() {
    const dentroDelMismoDia =
        entradaFecha.value &&
        salidaFecha.value &&
        entradaFecha.value === salidaFecha.value &&
        entradaHora.value &&
        salidaHora.value &&
        salidaHora.value <= entradaHora.value;

    salidaHora.setCustomValidity(
        dentroDelMismoDia
            ? 'La hora de salida debe ser posterior a la hora de entrada.'
            : ''
    );
}

function actualizarSalida() {
    const entradaCompleta = entradaFecha.value && entradaHora.value;
    if (!entradaCompleta) return;

    salidaFecha.min = entradaFecha.value;
    if (!salidaFecha.value || salidaFecha.value < entradaFecha.value) {
        salidaFecha.value = entradaFecha.value;
    }

    const sugerida = sumarMinutos(
        entradaFecha.value,
        entradaHora.value,
        60
    );

    if (sugerida.fecha !== entradaFecha.value) {
        if (!salidaFecha.value ||
            salidaFecha.value <= entradaFecha.value) {
            salidaFecha.value = sugerida.fecha;
        }
    }

    if (salidaFecha.value === entradaFecha.value &&
        salidaHora.value &&
        salidaHora.value <= entradaHora.value) {
        salidaHora.value = '';
    }

    if (!salidaHora.value) {
        salidaHora.value = sugerida.hora;
    }

    if (salidaFecha.value === entradaFecha.value) {
        salidaHora.min = sumarMinutos(
            entradaFecha.value,
            entradaHora.value,
            1
        ).hora;
    } else {
        salidaHora.removeAttribute('min');
    }

    validarRango();
}

[entradaFecha, entradaHora, salidaFecha].forEach(elemento => {
    elemento.addEventListener('change', actualizarSalida);
});

salidaHora.addEventListener('change', validarRango);
</script>
<?php require __DIR__ . '/../layout/fin.php'; ?>
