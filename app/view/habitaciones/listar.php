<?php
$tituloPagina = 'Habitaciones';
$activo = 'habitaciones';
require __DIR__ . '/../layout/inicio.php';
?>
    <h1>Habitaciones</h1>

<?php require __DIR__ . '/../layout/avisos.php'; ?>

    <div class="barra">
        <a href="habitaciones.php?accion=crear" class="btn btn-primario" data-modal>+ Crear</a>
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
                    <a href="habitaciones.php?accion=editar&id=<?= $h->id ?>" class="btn btn-sm" data-modal>Editar</a>
                    <a href="habitaciones.php?accion=cambiarEstado&id=<?= $h->id ?>" class="btn btn-sm" data-modal>Cambiar estado</a>
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
<?php require __DIR__ . '/../layout/fin.php'; ?>
