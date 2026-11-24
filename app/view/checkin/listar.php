<?php
$tituloPagina = 'Check-In / Check-Out';
$activo = 'checkin';
require __DIR__ . '/../layout/inicio.php';
?>
<h1>Check-In / Check-Out</h1>

<?php if ($mensaje): ?>
    <div class="alerta ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alerta error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<h2>Check-in</h2>
<form method="post" action="checkin.php?accion=checkin" class="form-grid">
    <?php Csrf::campo(); ?>
    <div class="campo">
        <label for="reserva_checkin">Reserva pendiente</label>
        <select id="reserva_checkin" name="reserva_id" required>
            <option value="">Seleccione una reserva</option>
            <?php foreach ($pendientes as $reserva): ?>
                <option value="<?= (int) $reserva->id ?>">
                    <?= htmlspecialchars('#' . $reserva->id . ' - ' . $reserva->huespedNombre . ' - Hab. ' . $reserva->habitacionNumero . ' (' . $reserva->fechaEntrada . ')', ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo">
        <label for="uid">UID de tarjeta / lectura simulada</label>
        <input id="uid" type="text" name="uid" maxlength="20" pattern="[A-Fa-f0-9]{4,20}" placeholder="Ej. A1B2C3D4" required>
    </div>
    <div class="botones botones-formulario">
        <button type="button" id="simular_uid" class="btn">Generar lectura simulada</button>
        <button type="submit" class="primario">Registrar check-in</button>
    </div>
</form>

<h2>Check-out</h2>
<table>
    <thead>
        <tr>
            <th>Reserva</th>
            <th>Huésped</th>
            <th>Habitación</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($activas as $reserva): ?>
        <tr>
            <td><?= (int) $reserva->id ?></td>
            <td><?= htmlspecialchars($reserva->huespedNombre ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reserva->habitacionNumero ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reserva->fechaEntrada, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reserva->fechaSalida, ENT_QUOTES, 'UTF-8') ?></td>
            <td class="acciones tabla-acciones">
                <form method="post" action="checkin.php?accion=checkout" onsubmit="return confirm('¿Registrar el check-out de esta reserva?')">
                    <?php Csrf::campo(); ?>
                    <input type="hidden" name="reserva_id" value="<?= (int) $reserva->id ?>">
                    <button type="submit" class="btn-sm">Check-out</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if (count($pendientes) === 0): ?>
    <p class="vacio">No hay reservas pendientes de check-in.</p>
<?php endif; ?>
<?php if (count($activas) === 0): ?>
    <p class="vacio">No hay reservas activas de check-out.</p>
<?php endif; ?>

<script>
document.getElementById('simular_uid').addEventListener('click', function () {
    const bytes = crypto.getRandomValues(new Uint8Array(4));
    document.getElementById('uid').value = Array.from(bytes, b => b.toString(16).padStart(2, '0')).join('').toUpperCase();
});
</script>
<?php require __DIR__ . '/../layout/fin.php'; ?>
