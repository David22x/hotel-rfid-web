<?php
$tituloPagina = $tituloPagina ?? 'Hotel RFID';
$activo = $activo ?? '';
$estrecho = $estrecho ?? false;
$usuarioActual = usuarioActual();

$modulosMenu = [
    'HABITACIONES' => ['Habitaciones', 'habitaciones.php', 'habitaciones'],
    'HUESPEDES' => ['Huéspedes', 'huespedes.php', 'huespedes'],
    'RESERVAS' => ['Reservas', 'reservas.php', 'reservas'],
    'CHECK_IN_OUT' => ['Check-In / Check-Out', 'checkin.php', 'checkin'],
    'CERRADURA_RFID' => ['Cerradura RFID', 'cerradura.php', 'cerradura'],
    'BITACORA' => ['Bitácora', 'bitacora.php', 'bitacora'],
    'GESTION_USUARIOS' => ['Usuarios', 'usuarios.php', 'usuarios'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/estilo.css">
</head>
<body>
<div class="app">
    <aside class="menu">
        <div class="menu-marca">Hotel RFID</div>
        <div class="menu-usuario">
            <strong><?= htmlspecialchars($usuarioActual['usuario']) ?></strong>
            <small><?= htmlspecialchars($usuarioActual['rol']) ?></small>
        </div>
        <nav>
            <a href="<?= BASE_URL ?>/menu.php" class="<?= $activo === 'menu' ? 'activo' : '' ?>">Inicio</a>
            <?php foreach ($modulosMenu as $modulo => [$nombre, $pagina, $clave]): ?>
                <?php if (tienePermiso($usuarioActual['rol'], $modulo)): ?>
                    <a href="<?= BASE_URL ?>/<?= $pagina ?>" class="<?= $activo === $clave ? 'activo' : '' ?>"><?= $nombre ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="<?= BASE_URL ?>/logout.php" class="menu-salir">
            <button type="submit">Cerrar sesión</button>
        </form>
    </aside>
    <main class="contenido">
        <div class="contenedor<?= $estrecho ? ' estrecho' : '' ?>">
