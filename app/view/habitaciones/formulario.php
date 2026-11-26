<?php
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
                <?php foreach ($this->service->tipos as $tipo): ?>
                    <option value="<?= $tipo ?>" <?= $tipo === $datos['tipo'] ? 'selected' : '' ?>><?= $tipo ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado" <?= $esNueva ? 'disabled' : '' ?>>
                <?php foreach ($this->service->estados as $estado): ?>
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
