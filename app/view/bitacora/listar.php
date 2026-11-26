<?php
$tituloPagina = 'Bitácora';
$activo = 'bitacora';
require __DIR__ . '/../layout/inicio.php';
?>
    <link rel="stylesheet" href="assets/css/bitacora.css">

    <h1>Bitácora</h1>

    <?php if ($error): ?>
        <div class="alerta error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="get" action="bitacora.php" class="filtros">
        <div class="campo">
            <label for="desde">Desde</label>
            <input type="date" id="desde" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>">
        </div>
        <div class="campo">
            <label for="hasta">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>">
        </div>
        <div class="campo">
            <label for="habitacion">Habitación</label>
            <select id="habitacion" name="habitacion">
                <option value="">Todas</option>
                <?php foreach ($habitaciones as $h): ?>
                    <option value="<?= $h->id ?>" <?= (string) $h->id === $filtros['habitacion'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($h->numero) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primario">Filtrar</button>
            <a href="bitacora.php" class="btn">Limpiar</a>
            <a href="reporte_bitacora.php?<?= htmlspecialchars($consultaReporte) ?>" class="btn" target="_blank">Generar PDF</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Tipo</th>
                <th>Habitación</th>
                <th>UID</th>
                <th>Resultado</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($eventos as $e): ?>
            <tr>
                <td><?= date('d/m/Y H:i:s', strtotime($e['fechaHora'])) ?></td>
                <td class="tipo-<?= htmlspecialchars($e['tipo']) ?>"><?= htmlspecialchars($e['tipo']) ?></td>
                <td><?= htmlspecialchars($e['habitacionNumero'] ?? '-') ?></td>
                <td><?= htmlspecialchars($e['uid'] ?? '-') ?></td>
                <td><?= htmlspecialchars($e['resultado'] ?? '-') ?></td>
                <td><?= htmlspecialchars($e['detalle'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (count($eventos) === 0): ?>
        <p class="vacio">No hay eventos con esos filtros.</p>
    <?php else: ?>
        <p>Total de eventos: <?= count($eventos) ?></p>
    <?php endif; ?>
<?php require __DIR__ . '/../layout/fin.php'; ?>
