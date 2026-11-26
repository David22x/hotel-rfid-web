<?php

require_once __DIR__ . '/../dao/UsuarioDAO.php';

function iniciarSesion(Usuario $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario'] = ['id' => $usuario->id, 'usuario' => $usuario->usuario, 'rol' => $usuario->rol];
}

function usuarioActual(): ?array
{
    $id = $_SESSION['usuario']['id'] ?? null;
    if ($id === null) {
        return null;
    }

    $dao = new UsuarioDAO();
    $usuario = $dao->buscarPorId($id);
    if ($usuario === null || !$usuario->activo) {
        unset($_SESSION['usuario']);
        return null;
    }

    $_SESSION['usuario'] = ['id' => $usuario->id, 'usuario' => $usuario->usuario, 'rol' => $usuario->rol];
    return $_SESSION['usuario'];
}

function exigirLogin(): void
{
    if (usuarioActual() === null) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function tienePermiso(string $rol, string $modulo): bool
{
    if ($rol === 'ADMIN') {
        return true;
    }
    return $rol === 'RECEPCIONISTA'
        && in_array($modulo, ['HABITACIONES', 'HUESPEDES', 'RESERVAS', 'CHECK_IN_OUT']);
}

function exigirPermiso(string $modulo): void
{
    exigirLogin();
    if (!tienePermiso(usuarioActual()['rol'], $modulo)) {
        http_response_code(403);
        exit('No tiene permiso para acceder a esta página.');
    }
}

function cerrarSesion(): void
{
    $_SESSION = [];
    session_destroy();
}
