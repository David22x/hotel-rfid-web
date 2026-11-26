<?php

require_once __DIR__ . '/../app/bootstrap.php';

exigirLogin();

$tituloPagina = 'Inicio - Hotel RFID';
$activo = 'menu';

$usuario = usuarioActual();

require __DIR__ . '/../app/view/layout/inicio.php';
?>

<h1>Bienvenido al Hotel RFID</h1>

<p>
    Hola,
    <strong>
        <?= htmlspecialchars($usuario['usuario'], ENT_QUOTES, 'UTF-8') ?>
    </strong>.
</p>

<p>
    Has iniciado sesión como
    <strong>
        <?= htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8') ?>
    </strong>.
</p>

<p>
    Utiliza el menú lateral para acceder a los
    módulos disponibles de acuerdo con tu rol.
</p>

<?php require __DIR__ . '/../app/view/layout/fin.php'; ?>
