
<?php
require_once __DIR__ . '/../../bootstrap.php';

// Configuración de la página
$tituloPagina = $tituloPagina ?? 'Hotel RFID';
$activo = $activo ?? '';
$estrecho = $estrecho ?? false;

// El layout solo se utiliza con una sesión iniciada.
Sesion::exigirLogin();

$usuarioActual = Sesion::usuario();

$modulosMenu = [
    'HABITACIONES' => ['Habitaciones', 'habitaciones.php', 'habitaciones'],
    'HUESPEDES' => ['Huéspedes', 'huespedes.php', 'huespedes'],
    'RESERVAS' => ['Reservas', 'reservas.php', 'reservas'],
    'CHECK_IN_OUT' => ['Check-In / Check-Out', 'checkin.php', 'checkin'],
    'CERRADURA_RFID' => ['Cerradura RFID', 'cerradura.php', 'cerradura'],
    'BITACORA' => ['Bitácora', 'bitacora.php', 'bitacora'],
    'GESTION_USUARIOS' => ['Usuarios', 'usuarios.php', 'usuarios']
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?>
    </title>

    <link rel="stylesheet"
          href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/css/estilo.css">
</head>
<body>

<div class="app">

    <aside class="menu">

        <div class="menu-marca">Hotel RFID</div>

        <div class="menu-usuario">
            <strong>
                <?= htmlspecialchars(
                    $usuarioActual['usuario'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

            <small>
                <?= htmlspecialchars(
                    $usuarioActual['rol'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </small>
        </div>

        <nav>
            <a href="<?= BASE_URL ?>/menu.php"
               class="<?= $activo === 'menu' ? 'activo' : '' ?>">
                Inicio
            </a>

            <?php foreach ($modulosMenu as $modulo => $datos): ?>

                <?php
                $permitido = PermisoService::tienePermiso(
                    $usuarioActual['rol'],
                    $modulo
                );

                $existe = file_exists(
                    __DIR__ . '/../../../public/' . $datos[1]
                );
                ?>

                <?php if ($permitido && $existe): ?>

                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($datos[1], ENT_QUOTES, 'UTF-8') ?>"
                       class="<?= $activo === $datos[2] ? 'activo' : '' ?>">

                        <?= htmlspecialchars($datos[0], ENT_QUOTES, 'UTF-8') ?>

                    </a>

                <?php endif; ?>

            <?php endforeach; ?>
        </nav>

        <form method="post"
              action="<?= BASE_URL ?>/logout.php"
              class="menu-salir">

            <?php Csrf::campo(); ?>

            <button type="submit">
                Cerrar sesión
            </button>
        </form>

    </aside>

    <main class="contenido">

        <div class="contenedor<?= $estrecho ? ' estrecho' : '' ?>">
