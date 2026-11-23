
<?php

class Csrf
{
    public static function generar(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] =
                    bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function campo(): void
    {
        $token = htmlspecialchars(
            self::generar(),
            ENT_QUOTES,
            'UTF-8'
        );

        echo '<input type="hidden" name="csrf_token" value="'
                . $token . '">';
    }

    public static function validar(): void
    {
        $tokenGuardado = $_SESSION['csrf_token'] ?? '';
        $tokenRecibido = $_POST['csrf_token'] ?? '';

        if (!is_string($tokenRecibido) ||
                !is_string($tokenGuardado) ||
                $tokenGuardado === '' ||
                !hash_equals($tokenGuardado, $tokenRecibido)) {

            throw new ReglaNegocioException(
                'Solicitud no válida. Actualice la página e intente nuevamente.'
            );
        }
    }
}
