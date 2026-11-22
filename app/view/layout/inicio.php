<?php
// Cabecera común con menú lateral. Variables esperadas:
//   $tituloPagina (string), $activo ('habitaciones' | 'huespedes'), $estrecho (bool, opcional)
$estrecho = $estrecho ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<div class="app">
    <aside class="menu">
        <div class="menu-marca">Hotel RFID</div>
        <nav>
            <a href="habitaciones.php" class="<?= $activo === 'habitaciones' ? 'activo' : '' ?>">Habitaciones</a>
            <a href="huespedes.php" class="<?= $activo === 'huespedes' ? 'activo' : '' ?>">Huéspedes</a>
        </nav>
    </aside>
    <main class="contenido">
        <div class="contenedor<?= $estrecho ? ' estrecho' : '' ?>">
