<?php
// Variables que llegan desde CerraduraController::atender():
//   $habitaciones, $habitacionId, $ultimoEvento, $error y $tokenCsrf
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
    <!-- Al cambiar de habitación se recarga la página con el último evento de esa puerta -->
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

    <!-- Token que cerradura.js envía en cada validación -->
    <input type="hidden" id="csrf" value="<?= htmlspecialchars($tokenCsrf) ?>">

    <div class="cerradura">
        <div id="indicador" class="indicador esperando"></div>
        <p id="lblResultado" class="resultado">Esperando tarjeta</p>
        <p id="lblDetalle" class="detalle"></p>
        <p>Último evento: <span id="lblUltimo"><?= htmlspecialchars($ultimoEvento) ?></span></p>
    </div>

    <fieldset>
        <legend>Lector RFID</legend>
        <button type="button" id="btnConectar" class="btn" onclick="conectarLector()">Conectar lector</button>
        <span id="lblLector">Lector desconectado</span>
    </fieldset>

    <fieldset>
        <legend>Simulador</legend>
        <label for="txtUid">UID de la tarjeta</label>
        <input type="text" id="txtUid" maxlength="20" placeholder="A3F1C20B">
        <button type="button" class="btn btn-primario" onclick="simularLectura()">Simular lectura</button>
    </fieldset>
<?php endif; ?>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/lector-rfid.js"></script>
<script src="assets/js/cerradura.js"></script>
<?php require __DIR__ . '/../layout/fin.php'; ?>
