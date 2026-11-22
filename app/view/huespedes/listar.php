<?php
// Variables que llegan desde HuespedController::listar():
//   $huespedes (Huesped[]), $termino (string), $mensaje (?string), $error (?string)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Huéspedes</title>
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 10px; }
        .mensaje { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Huéspedes</h1>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Búsqueda: un formulario GET pone ?buscar=... en la URL -->
    <form method="get" action="huespedes.php">
        <label for="buscar">Buscar (nombre o documento):</label>
        <input type="text" id="buscar" name="buscar" value="<?= htmlspecialchars($termino) ?>">
        <button type="submit">Buscar</button>
        <a href="huespedes.php">Limpiar búsqueda</a>
    </form>

    <p>
        <a href="huespedes.php?accion=crear">Crear</a> |
        <a href="huespedes.php">Refrescar</a>
    </p>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($huespedes as $h): ?>
            <tr>
                <td><?= $h->id ?></td>
                <td><?= htmlspecialchars($h->nombre) ?></td>
                <td><?= htmlspecialchars($h->documento) ?></td>
                <td><?= htmlspecialchars($h->correo) ?></td>
                <td><?= htmlspecialchars($h->telefono) ?></td>
                <td>
                    <a href="huespedes.php?accion=editar&id=<?= $h->id ?>">Editar</a>
                    <form method="post" action="huespedes.php?accion=eliminar" style="display:inline"
                          onsubmit="return confirm('¿Eliminar este huésped?')">
                        <input type="hidden" name="id" value="<?= $h->id ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
