<?php
// Equivale a CambiarEstadoDialog en Java.
// Variables que llegan desde HabitacionController::cambiarEstado():
//   $habitacion (Habitacion)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar estado</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="contenedor estrecho">
    <h1>Estado de la habitación <?= htmlspecialchars($habitacion->numero) ?></h1>

    <form method="post" action="">
        <div class="campo">
            <label for="estado">Nuevo estado</label>
            <select id="estado" name="estado">
                <?php foreach (HabitacionService::ESTADOS as $estado): ?>
                    <option value="<?= $estado ?>" <?= $estado === $habitacion->estado ? 'selected' : '' ?>><?= $estado ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="botones">
            <a href="habitaciones.php" class="btn">Cancelar</a>
            <button type="submit" class="primario">Aceptar</button>
        </div>
    </form>
</div>
</body>
</html>
