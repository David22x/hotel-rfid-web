<?php
// Equivale a CambiarEstadoDialog en Java.
// Variables que llegan desde HabitacionController::cambiarEstado():
//   $habitacion (Habitacion)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar estado</title>
</head>
<body>
    <h1>Cambiar estado de la habitación <?= htmlspecialchars($habitacion->numero) ?></h1>

    <form method="post" action="">
        <p>
            <label for="estado">Nuevo estado:</label>
            <select id="estado" name="estado">
                <?php foreach (HabitacionService::ESTADOS as $estado): ?>
                    <option value="<?= $estado ?>" <?= $estado === $habitacion->estado ? 'selected' : '' ?>><?= $estado ?></option>
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
