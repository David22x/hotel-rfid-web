<?php

require_once __DIR__ . '/../dao/UsuarioDAO.php';
require_once __DIR__ . '/../service/PermisoService.php';

class Sesion
{
    private static bool $verificada = false;
    private static ?array $usuarioActual = null;

    public static function iniciar(Usuario $usuario): void
    {
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'rol' => $usuario->rol
        ];

        self::$usuarioActual = $_SESSION['usuario'];
        self::$verificada = true;
    }

    public static function usuario(): ?array
    {
        if (!isset($_SESSION['usuario'])) {
            return null;
        }

        // Consultar la base una sola vez por petición.
        if (self::$verificada) {
            return self::$usuarioActual;
        }

        self::$verificada = true;

        $id = $_SESSION['usuario']['id'] ?? null;

        if (!is_int($id) || $id <= 0) {
            unset($_SESSION['usuario']);
            return null;
        }

        $dao = new UsuarioDAO();
        $usuario = $dao->buscarPorId($id);

        if ($usuario === null || !$usuario->activo) {
            unset($_SESSION['usuario']);
            self::$usuarioActual = null;
            return null;
        }

        // Actualizar nombre y rol desde MySQL.
        self::$usuarioActual = [
            'id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'rol' => $usuario->rol
        ];

        $_SESSION['usuario'] = self::$usuarioActual;

        return self::$usuarioActual;
    }

    public static function estaAutenticado(): bool
    {
        return self::usuario() !== null;
    }

    public static function exigirLogin(): void
    {
        if (!self::estaAutenticado()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }

    public static function exigirPermiso(string $modulo): void
    {
        self::exigirLogin();

        $usuario = self::usuario();

        if (!PermisoService::tienePermiso(
            $usuario['rol'],
            $modulo
        )) {
            http_response_code(403);
            exit('No tiene permiso para acceder a esta página.');
        }
    }

    public static function cerrar(): void
    {
        $_SESSION = [];

        self::$usuarioActual = null;
        self::$verificada = false;

        if (ini_get('session.use_cookies')) {
            $datos = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 3600,
                $datos['path'],
                $datos['domain'],
                $datos['secure'],
                $datos['httponly']
            );
        }

        session_destroy();
    }
}
