<?php
// Sirve para crear y para editar (como HuespedFormDialog en Java).
// Variables que llegan desde HuespedController:
//   $titulo, $error (?string), $datos (array con nombre, documento, correo, telefono)
?>
<?php
// Petición AJAX (desde el modal): se devuelve solo el formulario, sin el layout.
$esModal = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
if (!$esModal) {
    $tituloPagina = $titulo;
    $activo = 'huespedes';
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
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>">
        </div>
        <div class="campo">
            <label for="documento">Documento</label>
            <input type="text" id="documento" name="documento" value="<?= htmlspecialchars($datos['documento']) ?>">
        </div>
        <div class="campo">
            <label for="correo">Correo</label>
            <input type="text" id="correo" name="correo" value="<?= htmlspecialchars($datos['correo']) ?>">
        </div>
        <div class="campo">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($datos['telefono']) ?>">
        </div>
        <div class="botones">
            <a href="huespedes.php" class="btn" data-cerrar>Cancelar</a>
            <button type="submit" class="primario">Aceptar</button>
        </div>
    </form>
<?php if (!$esModal) require __DIR__ . '/../layout/fin.php'; ?>
