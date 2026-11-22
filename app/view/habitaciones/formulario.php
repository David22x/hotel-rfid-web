<?php
// Sirve para crear y para editar (como HabitacionFormDialog en Java).
// Variables que llegan desde HabitacionController:
//   $titulo, $esNueva (bool), $error (?string),
//   $datos (array con numero, piso, tipo, estado)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <style>
        .error { color: red; }
        label { display: inline-block; width: 80px; }
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
            <label for="numero">Número:</label>
            <input type="text" id="numero" name="numero" value="<?= htmlspecialchars($datos['numero']) ?>">
        </p>
        <p>
            <label for="piso">Piso:</label>
            <input type="number" id="piso" name="piso" min="0" value="<?= htmlspecialchars($datos['piso']) ?>">
        </p>
        <p>
            <label for="tipo">Tipo:</label>
            <select id="tipo" name="tipo">
                <?php foreach (HabitacionService::TIPOS as $tipo): ?>
                    <option value="<?= $tipo ?>" <?= $tipo === $datos['tipo'] ? 'selected' : '' ?>><?= $tipo ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="estado">Estado:</label>
            <!-- Al crear el estado no se puede elegir: siempre empieza LIBRE -->
            <select id="estado" name="estado" <?= $esNueva ? 'disabled' : '' ?>>
                <?php foreach (HabitacionService::ESTADOS as $estado): ?>
                    <option value="<?= $estado ?>" <?= $estado === $datos['estado'] ? 'selected' : '' ?>><?= $estado ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <a href="habitaciones.php">Cancelar</a>
            <button type="submit">Aceptar</button>
        </p>
    </form>
</body>
</html>
