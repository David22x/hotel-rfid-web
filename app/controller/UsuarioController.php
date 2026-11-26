<?php

require_once __DIR__ . '/../service/UsuarioService.php';

class UsuarioController
{
    private UsuarioService $servicio;

    public function __construct()
    {
        $this->servicio = new UsuarioService();
    }

    public function manejar(): void
    {
        exigirPermiso('GESTION_USUARIOS');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesar();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit('Método no permitido.');
        }

        $usuarios = [];
        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';

        unset($_SESSION['mensaje'], $_SESSION['error']);

        try {
            $usuarios = $this->servicio->listar();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $error = 'No se pudo cargar la lista de usuarios.';
        }

        require __DIR__ . '/../view/usuarios/listar.php';
    }

    private function procesar(): void
    {
        try {
            $accion = $this->texto('accion');
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $actual = usuarioActual();
            $administradorActual = (int) $actual['id'];

            switch ($accion) {
                case 'crear':
                    $this->servicio->crear(
                        $this->texto('usuario'),
                        $this->texto('password'),
                        $this->texto('rol')
                    );
                    $mensaje = 'Usuario creado correctamente.';
                    break;

                case 'editar':
                    $this->exigirId($id);

                    $this->servicio->editar(
                        $id,
                        $this->texto('usuario'),
                        $this->texto('rol'),
                        $administradorActual
                    );
                    $mensaje = 'Usuario actualizado correctamente.';
                    break;

                case 'estado':
                    $this->exigirId($id);

                    $this->servicio->cambiarEstado(
                        $id,
                        $administradorActual
                    );
                    $mensaje = 'Estado actualizado correctamente.';
                    break;

                case 'password':
                    $this->exigirId($id);

                    $this->servicio->restablecerPassword(
                        $id,
                        $this->texto('password')
                    );
                    $mensaje = 'Contraseña restablecida correctamente.';
                    break;

                case 'eliminar':
                    $this->exigirId($id);

                    $this->servicio->eliminar(
                        $id,
                        $administradorActual
                    );
                    $mensaje = 'Usuario eliminado correctamente.';
                    break;

                default:
                    throw new ReglaNegocioException('La operación solicitada no es válida.');
            }

            $_SESSION['mensaje'] = $mensaje;

        } catch (ReglaNegocioException $e) {
            $_SESSION['error'] = $e->getMessage();

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['error'] =
                'No fue posible completar la operación.';
        }

        header('Location: ' . BASE_URL . '/usuarios.php');
        exit;
    }

    private function texto(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';

        if (!is_string($valor)) {
            throw new ReglaNegocioException('Los datos enviados no son válidos.');
        }

        return $valor;
    }

    private function exigirId($id): void
    {
        if (!is_int($id) || $id < 1) {
            throw new ReglaNegocioException('Seleccione un usuario válido.');
        }
    }
}
