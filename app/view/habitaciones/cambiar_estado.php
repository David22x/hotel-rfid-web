<?php
$esModal = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
if (!$esModal) {
    $tituloPagina = 'Cambiar estado';
    $activo = 'habitaciones';
    $estrecho = true;
    require __DIR__ . '/../layout/inicio.php';
}
?>
    <h1>Estado de la habitación <?= htmlspecialchars($habitacion->numero) ?></h1>

    <form method="post" action="" data-modal-form>
        <div class="campo">
            <label for="estado">Nuevo estado</label>
            <select id="estado" name="estado">
                <?php foreach ($this->service->estados as $estado): ?>
                    <option value="<?= $estado ?>" <?= $estado === $habitacion->estado ? 'selected' : '' ?>><?= $estado ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="botones">
            <a href="habitaciones.php" class="btn" data-cerrar>Cancelar</a>
            <button type="submit" class="primario">Aceptar</button>
        </div>
    </form>
<?php if (!$esModal) require __DIR__ . '/../layout/fin.php'; ?>
