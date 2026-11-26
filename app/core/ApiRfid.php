<?php

/** Utilidades comunes de las rutas /api/ que consume el lector RFID. */
class ApiRfid
{
    public static function responder(int $codigo, array $datos): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function exigirMetodo(string $metodo): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
            header('Allow: ' . $metodo);
            self::responder(405, ['error' => 'Método no permitido.']);
        }
    }

    /** Lee el cuerpo como JSON o como formulario. */
    public static function datos(): array
    {
        $crudo = file_get_contents('php://input');
        $json = json_decode($crudo ?: '', true);
        return is_array($json) ? $json : $_POST;
    }

    /** UID hexadecimal (4 a 10 bytes) normalizado, o null si no es válido. */
    public static function uidValido(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return null;
        }
        $uid = strtoupper(trim($valor));
        return preg_match('/^([0-9A-F]{2}){4,10}$/', $uid) ? $uid : null;
    }
}
