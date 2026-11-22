<?php
// Variables que llegan desde HuespedController::listar():
//   $huespedes (Huesped[]), $termino (string), $mensaje (?string), $error (?string)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Huéspedes</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="contenedor">
    <h1>Huéspedes</h1>

    <?php if ($mensaje): ?>
        <div class="alerta ok"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alerta error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="barra">
        <!-- Búsqueda: un formulario GET pone ?buscar=... en la URL -->
        <form method="get" action="huespedes.php">
            <input type="text" id="buscar" name="buscar" placeholder="Nombre o documento"
                   value="<?= htmlspecialchars($termino) ?>">
            <button type="submit">Buscar</button>
            <a href="huespedes.php">Limpiar</a>
        </form>
        <a href="huespedes.php?accion=crear" class="btn btn-primario">+ Crear</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Documento</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($huespedes as $h): ?>
            <tr>
                <td><?= $h->id ?></td>
                <td><?= htmlspecialchars($h->nombre) ?></td>
                <td><?= htmlspecialchars($h->documento) ?></td>
                <td><?= htmlspecialchars($h->correo) ?></td>
                <td><?= htmlspecialchars($h->telefono) ?></td>
                <td class="acciones">
                    <a href="huespedes.php?accion=editar&id=<?= $h->id ?>" class="btn btn-sm">Editar</a>
                    <form method="post" action="huespedes.php?accion=eliminar"
                          onsubmit="return confirm('¿Eliminar este huésped?')">
                        <input type="hidden" name="id" value="<?= $h->id ?>">
                        <button type="submit" class="btn-sm btn-peligro">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (count($huespedes) === 0): ?>
        <p class="vacio">No hay huéspedes registrados.</p>
    <?php endif; ?>
</div>
</body>
</html>
