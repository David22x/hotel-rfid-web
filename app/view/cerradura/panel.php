<?php
$tituloPagina = 'Cerradura RFID';
$activo = 'cerradura';
require __DIR__ . '/../layout/inicio.php';
?>
<link rel="stylesheet" href="assets/css/cerradura.css">

<h1>Cerradura RFID</h1>
<p>Elija la puerta y acerque una tarjeta al lector, o escriba su UID en el simulador.</p>

<?php if ($error): ?>
    <div class="alerta error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (count($habitaciones) === 0): ?>
    <p class="vacio">No hay habitaciones registradas.</p>
<?php else: ?>
    <form method="get" action="cerradura.php">
        <div class="campo">
            <label for="cmbHabitacion">Puerta de la habitación</label>
            <select id="cmbHabitacion" name="habitacion" onchange="this.form.submit()">
                <?php foreach ($habitaciones as $h): ?>
                    <option value="<?= $h->id ?>" <?= $h->id === $habitacionId ? 'selected' : '' ?>>
                        Habitación <?= htmlspecialchars($h->numero) ?> (piso <?= $h->piso ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div class="cerradura">
        <div id="indicador" class="indicador esperando"></div>
        <p id="lblResultado" class="resultado">Esperando tarjeta</p>
        <p id="lblDetalle" class="detalle"></p>
        <p>Último evento: <span id="lblUltimo"><?= htmlspecialchars($ultimoEvento) ?></span></p>
    </div>

    <?php require __DIR__ . '/../lector/controles.php'; ?>
<?php endif; ?>

<script src="assets/js/cerradura.js"></script>
<?php require __DIR__ . '/../layout/fin.php'; ?>
