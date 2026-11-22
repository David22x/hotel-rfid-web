<?php
// Sirve para crear y para editar (como HabitacionFormDialog en Java).
// Variables que llegan desde HabitacionController:
//   $titulo, $esNueva (bool), $error (?string),
//   $datos (array con numero, piso, tipo, estado)
?>
<?php
// Petición AJAX (desde el modal): se devuelve solo el formulario, sin el layout.
$esModal = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
if (!$esModal) {
    $tituloPagina = $titulo;
    $activo = 'habitaciones';
    $estrecho = true;
    require __DIR__ . '/../layout/inicio.php';
}
?>
    <h1><?= htmlspecialchars($titulo) ?></h1>

    <?php if ($error): ?>
        <div class="alerta error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- action vacío = se envía a la misma URL (incluye ?accion=... y &id=...) -->
    <form method="post" action="" data-modal-form>
        <div class="campo">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" value="<?= htmlspecialchars($datos['numero']) ?>">
        </div>
        <div class="campo">
            <label for="piso">Piso</label>
            <input type="number" id="piso" name="piso" min="0" value="<?= htmlspecialchars($datos['piso']) ?>">
        </div>
        <div class="campo">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo">
                <?php foreach (HabitacionService::TIPOS as $tipo): ?>
                    <option value="<?= $tipo ?>" <?= $tipo === $datos['tipo'] ? 'selected' : '' ?>><?= $tipo ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="estado">Estado</label>
            <!-- Al crear el estado no se puede elegir: siempre empieza LIBRE -->
            <select id="estado" name="estado" <?= $esNueva ? 'disabled' : '' ?>>
                <?php foreach (HabitacionService::ESTADOS as $estado): ?>
                    <option value="<?= $estado ?>" <?= $estado === $datos['estado'] ? 'selected' : '' ?>><?= $estado ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="botones">
            <a href="habitaciones.php" class="btn" data-cerrar>Cancelar</a>
            <button type="submit" class="primario">Aceptar</button>
        </div>
    </form>
<?php if (!$esModal) require __DIR__ . '/../layout/fin.php'; ?>
