
<?php

require_once __DIR__ . '/../app/bootstrap.php';

Sesion::exigirLogin();

$usuario = Sesion::usuario();

$modulos = [
    'HABITACIONES' => ['Habitaciones', 'habitaciones.php'],
    'HUESPEDES' => ['Huéspedes', 'huespedes.php'],
    'RESERVAS' => ['Reservas', 'reservas.php'],
    'CHECK_IN_OUT' => ['Check-In / Check-Out', 'checkin.php'],
    'CERRADURA_RFID' => ['Cerradura RFID', 'cerradura.php'],
    'BITACORA' => ['Bitácora', 'bitacora.php'],
    'GESTION_USUARIOS' => ['Usuarios', 'usuarios.php']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menú - Hotel RFID</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
    <div class="contenedor">
        <h1>Hotel RFID</h1>

        <p>
            Bienvenido,
            <strong><?= htmlspecialchars($usuario['usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
        </p>

        <p>Rol: <?= htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8') ?></p>

        <h2>Módulos</h2>

        <nav>
            <?php foreach ($modulos as $modulo => $datos): ?>
                <?php if (PermisoService::tienePermiso($usuario['rol'], $modulo)): ?>
                    <?php
                    $ruta = __DIR__ . '/' . $datos[1];
                    ?>
                    <?php if (file_exists($ruta)): ?>
                        <p>
                            <a class="btn" href="<?= htmlspecialchars($datos[1], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($datos[0], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <form method="post" action="logout.php">
            <?php Csrf::campo(); ?>
            <button type="submit">Cerrar sesión</button>
        </form>
    </div>
</body>
</html>
