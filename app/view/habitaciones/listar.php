<?php
// Variables que llegan desde HabitacionController::listar():
//   $habitaciones (Habitacion[]), $mensaje (?string), $error (?string)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Habitaciones</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="contenedor">
    <h1>Habitaciones</h1>

    <?php if ($mensaje): ?>
        <div class="alerta ok"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alerta error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="barra">
        <a href="habitaciones.php?accion=crear" class="btn btn-primario">+ Crear</a>
        <a href="habitaciones.php" class="btn">Refrescar</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Piso</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($habitaciones as $h): ?>
            <tr>
                <td><?= $h->id ?></td>
                <td><?= htmlspecialchars($h->numero) ?></td>
                <td><?= $h->piso ?></td>
                <td><?= htmlspecialchars($h->tipo) ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($h->estado) ?>"><?= htmlspecialchars($h->estado) ?></span></td>
                <td class="acciones">
                    <a href="habitaciones.php?accion=editar&id=<?= $h->id ?>" class="btn btn-sm">Editar</a>
                    <a href="habitaciones.php?accion=cambiarEstado&id=<?= $h->id ?>" class="btn btn-sm">Cambiar estado</a>
                    <!-- Eliminar usa un formulario POST y pide confirmación con JavaScript -->
                    <form method="post" action="habitaciones.php?accion=eliminar"
                          onsubmit="return confirm('¿Eliminar esta habitación?')">
                        <input type="hidden" name="id" value="<?= $h->id ?>">
                        <button type="submit" class="btn-sm btn-peligro">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (count($habitaciones) === 0): ?>
        <p class="vacio">No hay habitaciones registradas.</p>
    <?php endif; ?>
</div>
</body>
</html>
