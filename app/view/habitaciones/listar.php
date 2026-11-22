<?php
// Variables que llegan desde HabitacionController::listar():
//   $habitaciones (Habitacion[]), $mensaje (?string), $error (?string)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Habitaciones</title>
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 10px; }
        .mensaje { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Habitaciones</h1>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <p>
        <a href="habitaciones.php?accion=crear">Crear</a> |
        <a href="habitaciones.php">Refrescar</a>
    </p>

    <table>
        <tr>
            <th>ID</th>
            <th>Número</th>
            <th>Piso</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($habitaciones as $h): ?>
            <tr>
                <td><?= $h->id ?></td>
                <td><?= htmlspecialchars($h->numero) ?></td>
                <td><?= $h->piso ?></td>
                <td><?= htmlspecialchars($h->tipo) ?></td>
                <td><?= htmlspecialchars($h->estado) ?></td>
                <td>
                    <a href="habitaciones.php?accion=editar&id=<?= $h->id ?>">Editar</a>
                    <a href="habitaciones.php?accion=cambiarEstado&id=<?= $h->id ?>">Cambiar estado</a>
                    <!-- Eliminar usa un formulario POST y pide confirmación con JavaScript -->
                    <form method="post" action="habitaciones.php?accion=eliminar" style="display:inline"
                          onsubmit="return confirm('¿Eliminar esta habitación?')">
                        <input type="hidden" name="id" value="<?= $h->id ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if (count($habitaciones) === 0): ?>
        <p>No hay habitaciones registradas.</p>
    <?php endif; ?>
</body>
</html>
