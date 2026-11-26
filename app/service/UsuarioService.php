<?php

require_once __DIR__ . '/../dao/UsuarioDAO.php';

class UsuarioService
{
    private UsuarioDAO $dao;

    public function __construct()
    {
        $this->dao = new UsuarioDAO();
    }

    public function listar(): array
    {
        return $this->dao->listar();
    }

    public function crear(
        string $nombre,
        string $password,
        string $rol
    ): void {
        $nombre = $this->validarNombre($nombre);
        $this->validarPassword($password);
        $this->validarRol($rol);

        if ($this->dao->buscarPorUsuario($nombre) !== null) {
            throw new Exception('El nombre de usuario ya existe.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $usuario = new Usuario(
            $nombre,
            $hash,
            $rol,
            true
        );

        $this->dao->crear($usuario);
    }

    public function editar(
        int $id,
        string $nombre,
        string $rol,
        int $administradorActual
    ): void {
        $usuario = $this->obtener($id);

        $nombre = $this->validarNombre($nombre);
        $this->validarRol($rol);

        $duplicado = $this->dao->buscarPorUsuario($nombre);

        if ($duplicado !== null && $duplicado->id !== $id) {
            throw new Exception('El nombre de usuario ya existe.');
        }

        if ($id === $administradorActual &&
            $rol !== 'ADMIN') {
            throw new Exception('No puede quitarse su propio rol de administrador.');
        }

        if ($usuario->rol === 'ADMIN' &&
            $usuario->activo &&
            $rol !== 'ADMIN') {
            $this->protegerUltimoAdmin();
        }

        $usuario->usuario = $nombre;
        $usuario->rol = $rol;

        $this->dao->actualizar($usuario);
    }

    public function cambiarEstado(
        int $id,
        int $administradorActual
    ): void {
        $usuario = $this->obtener($id);

        if ($id === $administradorActual) {
            throw new Exception('No puede desactivar su propia cuenta.');
        }

        if ($usuario->activo &&
            $usuario->rol === 'ADMIN') {
            $this->protegerUltimoAdmin();
        }

        $this->dao->actualizarActivo(
            $id,
            !$usuario->activo
        );
    }

    public function restablecerPassword(
        int $id,
        string $password
    ): void {
        $this->obtener($id);
        $this->validarPassword($password);

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $this->dao->actualizarPasswordHash($id, $hash);
    }

    public function eliminar(
        int $id,
        int $administradorActual
    ): void {
        $usuario = $this->obtener($id);

        if ($id === $administradorActual) {
            throw new Exception('No puede eliminar su propia cuenta.');
        }

        if ($usuario->rol === 'ADMIN' &&
            $usuario->activo) {
            $this->protegerUltimoAdmin();
        }

        $this->dao->eliminar($id);
    }

    private function obtener(int $id): Usuario
    {
        $usuario = $id > 0
            ? $this->dao->buscarPorId($id)
            : null;

        if ($usuario === null) {
            throw new Exception('El usuario seleccionado no existe.');
        }

        return $usuario;
    }

    private function validarNombre(string $nombre): string
    {
        $nombre = trim($nombre);

        if ($nombre === '' || strlen($nombre) > 50) {
            throw new Exception('El usuario debe tener entre 1 y 50 caracteres.');
        }

        return $nombre;
    }

    private function validarPassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new Exception('La contraseña debe tener mínimo 8 caracteres.');
        }

        if (strlen($password) > 72) {
            throw new Exception('La contraseña no debe superar 72 bytes.');
        }
    }

    private function validarRol(string $rol): void
    {
        if (!in_array($rol, [
            'ADMIN',
            'RECEPCIONISTA'
        ], true)) {
            throw new Exception('El rol seleccionado no es válido.');
        }
    }

    private function protegerUltimoAdmin(): void
    {
        if ($this->dao->contarAdministradoresActivos() <= 1) {
            throw new Exception('Debe existir al menos un administrador activo.');
        }
    }
}
