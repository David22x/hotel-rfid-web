
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - Hotel RFID</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/css/estilo.css">
</head>
<body>
    <div class="contenedor estrecho">
        <h1>Hotel RFID</h1>
        <p>Ingrese sus credenciales para continuar.</p>

        <?php if ($error !== ''): ?>
            <div class="alerta error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <?php Csrf::campo(); ?>

            <p>
                <label for="usuario">Usuario</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    value="<?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>"
                    required
                    autocomplete="username"
                >
            </p>

            <p>
                <label for="password">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </p>

            <button type="submit" class="primario">
                Ingresar
            </button>
        </form>
    </div>
</body>
</html>
