<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/service/AuthService.php';

if (usuarioActual() !== null) {
    header('Location: ' . BASE_URL . '/menu.php');
    exit;
}

$error = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!is_string($usuario) || !is_string($password)) {
            throw new Exception('Datos de inicio de sesión no válidos.');
        }

        $usuario = trim($usuario);
        $servicio = new AuthService();
        iniciarSesion($servicio->autenticar($usuario, $password));

        header('Location: ' . BASE_URL . '/menu.php');
        exit;
    } catch (Throwable $e) {
        if (get_class($e) === 'Exception') {
            $error = $e->getMessage();
        } else {
            error_log($e->getMessage());
            $error = 'No fue posible iniciar sesión. Intente nuevamente.';
        }
    }
}

require __DIR__ . '/../app/view/login/formulario.php';
