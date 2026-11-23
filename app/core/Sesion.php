
<?php
require_once __DIR__ . '/../service/PermisoService.php';
class Sesion
{
    public static function iniciar(Usuario $usuario): void
    {
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'rol' => $usuario->rol
        ];
    }

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
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
                $modulo)) {

            http_response_code(403);
            exit('No tiene permiso para acceder a esta página.');
        }
    }

    public static function cerrar(): void
    {
        $_SESSION = [];

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
