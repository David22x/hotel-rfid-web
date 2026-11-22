<?php
// Sirve para crear y para editar (como HuespedFormDialog en Java).
// Variables que llegan desde HuespedController:
//   $titulo, $error (?string), $datos (array con nombre, documento, correo, telefono)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="contenedor estrecho">
    <h1><?= htmlspecialchars($titulo) ?></h1>

    <?php if ($error): ?>
        <div class="alerta error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- action vacío = se envía a la misma URL (incluye ?accion=... y &id=...) -->
    <form method="post" action="">
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
            <a href="huespedes.php" class="btn">Cancelar</a>
            <button type="submit" class="primario">Aceptar</button>
        </div>
    </form>
</div>
</body>
</html>
