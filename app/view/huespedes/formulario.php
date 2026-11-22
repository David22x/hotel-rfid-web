<?php
// Sirve para crear y para editar (como HuespedFormDialog en Java).
// Variables que llegan desde HuespedController:
//   $titulo, $error (?string), $datos (array con nombre, documento, correo, telefono)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <style>
        .error { color: red; }
        label { display: inline-block; width: 90px; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($titulo) ?></h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- action vacío = se envía a la misma URL (incluye ?accion=... y &id=...) -->
    <form method="post" action="">
        <p>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>">
        </p>
        <p>
            <label for="documento">Documento:</label>
            <input type="text" id="documento" name="documento" value="<?= htmlspecialchars($datos['documento']) ?>">
        </p>
        <p>
            <label for="correo">Correo:</label>
            <input type="text" id="correo" name="correo" value="<?= htmlspecialchars($datos['correo']) ?>">
        </p>
        <p>
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($datos['telefono']) ?>">
        </p>
        <p>
            <a href="huespedes.php">Cancelar</a>
            <button type="submit">Aceptar</button>
        </p>
    </form>
</body>
</html>
