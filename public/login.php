<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/service/AuthService.php';

if (Sesion::estaAutenticado()) {
    header('Location: ' . BASE_URL . '/menu.php');
    exit;
}

$error = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Csrf::validar();

        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!is_string($usuario) || !is_string($password)) {
            throw new ReglaNegocioException(
                'Datos de inicio de sesión no válidos.'
            );
        }

        $usuario = trim($usuario);

        $servicio = new AuthService();
        $usuarioAutenticado = $servicio->autenticar(
            $usuario,
            $password
        );

        Sesion::iniciar($usuarioAutenticado);

        header('Location: ' . BASE_URL . '/menu.php');
        exit;

    } catch (ReglaNegocioException $e) {
        $error = $e->getMessage();

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'No fue posible iniciar sesión. Intente nuevamente.';
    }
}

require __DIR__ . '/../app/view/login/formulario.php';
