<?php
$tituloPagina = 'Bitácora';
$activo = 'bitacora';
require __DIR__ . '/../layout/inicio.php';
?>
<h1>Bitácora</h1>

<?php if ($error): ?>
    <div class="alerta error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

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
    <?php foreach ($eventos as $evento): ?>
        <tr>
            <td><?= htmlspecialchars($evento['fechaHora'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($evento['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($evento['habitacionNumero'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($evento['uid'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($evento['resultado'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($evento['detalle'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if (count($eventos) === 0): ?>
    <p class="vacio">No hay eventos registrados.</p>
<?php endif; ?>
<?php require __DIR__ . '/../layout/fin.php'; ?>
