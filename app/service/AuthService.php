<?php

require_once __DIR__ . '/../dao/UsuarioDAO.php';
require_once __DIR__ . '/../core/ReglaNegocioException.php';

class AuthService
{
    private UsuarioDAO $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function autenticar(string $usuario, string $password): Usuario
    {
        $usuario = trim($usuario);

        if ($usuario === '' || $password === '') {
            throw new ReglaNegocioException(
                'Ingrese usuario y contraseña.'
            );
        }

        $encontrado = $this->usuarioDAO->buscarPorUsuario($usuario);

        if ($encontrado === null ||
            !$encontrado->activo ||
            !password_verify($password, $encontrado->passwordHash)) {

            throw new ReglaNegocioException(
                'Usuario o contraseña incorrectos.'
            );
        }

        return $encontrado;
    }
}
