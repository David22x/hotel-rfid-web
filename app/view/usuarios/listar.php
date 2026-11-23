
<?php
/** @var Usuario[] $usuarios */
/** @var string $mensaje */
/** @var string $error */
$tituloPagina = 'Gestión de usuarios';
$activo = 'usuarios';
$estrecho = false;

require __DIR__ . '/../layout/inicio.php';
?>

<h1>Gestión de usuarios</h1>
<p>Administración de cuentas del sistema Hotel RFID.</p>

<?php if ($mensaje !== ''): ?>
    <div class="alerta ok">
        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alerta error">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<section>
    <h2>Crear usuario</h2>

    <form method="post" action="usuarios.php">
        <?php Csrf::campo(); ?>
        <input type="hidden" name="accion" value="crear">

        <p>
            <label for="nuevoUsuario">Nombre de usuario</label>
            <input type="text" id="nuevoUsuario"
                   name="usuario" maxlength="50" required>
        </p>

        <p>
            <label for="nuevaPassword">Contraseña</label>
            <input type="password" id="nuevaPassword"
                   name="password" minlength="8" required>
        </p>

        <p>
            <label for="nuevoRol">Rol</label>
            <select id="nuevoRol" name="rol" required>
                <option value="RECEPCIONISTA">Recepcionista</option>
                <option value="ADMIN">Administrador</option>
            </select>
        </p>

        <button type="submit" class="btn btn-primario">
            Crear usuario
        </button>
    </form>
</section>

<hr>

<h2>Usuarios registrados</h2>

<div style="overflow-x: auto;">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario y rol</th>
            <th>Estado</th>
            <th>Editar</th>
            <th>Contraseña</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= (int) $u->id ?></td>

            <td>
                <strong>
                    <?= htmlspecialchars($u->usuario, ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <p>
                    <?= htmlspecialchars($u->rol, ENT_QUOTES, 'UTF-8') ?>
                </p>
            </td>

            <td>
                <?= $u->activo ? 'ACTIVO' : 'INACTIVO' ?>
            </td>

            <td>
                <form method="post" action="usuarios.php">
                    <?php Csrf::campo(); ?>
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id"
                           value="<?= (int) $u->id ?>">

                    <input type="text" name="usuario"
                           maxlength="50" required
                           aria-label="Nombre de usuario"
                           value="<?= htmlspecialchars($u->usuario, ENT_QUOTES, 'UTF-8') ?>">

                    <select name="rol" aria-label="Rol">
                        <option value="ADMIN"
                            <?= $u->rol === 'ADMIN' ? 'selected' : '' ?>>
                            Administrador
                        </option>
                        <option value="RECEPCIONISTA"
                            <?= $u->rol === 'RECEPCIONISTA' ? 'selected' : '' ?>>
                            Recepcionista
                        </option>
                    </select>

                    <button type="submit">Guardar</button>
                </form>
            </td>

            <td>
                <form method="post" action="usuarios.php">
                    <?php Csrf::campo(); ?>
                    <input type="hidden" name="accion" value="password">
                    <input type="hidden" name="id"
                           value="<?= (int) $u->id ?>">

                    <input type="password" name="password"
                           minlength="8" required
                           aria-label="Nueva contraseña"
                           placeholder="Nueva contraseña">

                    <button type="submit">Restablecer</button>
                </form>
            </td>

            <td>
                <?php if ((int) $u->id !== (int) Sesion::usuario()['id']): ?>

                    <form method="post" action="usuarios.php">
                        <?php Csrf::campo(); ?>
                        <input type="hidden" name="accion" value="estado">
                        <input type="hidden" name="id"
                               value="<?= (int) $u->id ?>">

                        <button type="submit">
                            <?= $u->activo ? 'Desactivar' : 'Activar' ?>
                        </button>
                    </form>

                    <form method="post" action="usuarios.php"
                          onsubmit="return confirm('¿Eliminar este usuario?');">
                        <?php Csrf::campo(); ?>
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id"
                               value="<?= (int) $u->id ?>">

                        <button type="submit" class="btn-peligro">
                            Eliminar
                        </button>
                    </form>

                <?php else: ?>
                    <span>Cuenta actual</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php if (count($usuarios) === 0): ?>
    <p>No hay usuarios registrados.</p>
<?php endif; ?>

<?php require __DIR__ . '/../layout/fin.php'; ?>
