<?php if (!empty($mensaje)): ?>
    <div class="alerta ok"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alerta error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
