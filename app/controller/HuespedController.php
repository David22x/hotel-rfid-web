<?php

require_once __DIR__ . '/../service/HuespedService.php';

class HuespedController
{
    private HuespedService $service;

    public function __construct()
    {
        $this->service = new HuespedService();
    }

    public function atender(): void
    {
        exigirPermiso('HUESPEDES');
        $accion = $_GET['accion'] ?? 'listar';

        if ($accion === 'crear') {
            $this->crear();
        } elseif ($accion === 'editar') {
            $this->editar();
        } elseif ($accion === 'eliminar') {
            $this->eliminar();
        } else {
            $this->listar();
        }
    }

    private function listar(): void
    {
        $huespedes = [];
        $termino = trim($_GET['buscar'] ?? '');
        $mensaje = $this->tomarAviso('mensaje');
        $error = $this->tomarAviso('error');

        try {
            $huespedes = $this->service->buscar($termino);
            if ($termino !== '' && count($huespedes) === 0) {
                $mensaje = 'No se encontraron huéspedes para: ' . $termino;
            }
        } catch (Exception $e) {
            $error = 'No se pudieron cargar los huéspedes: ' . $e->getMessage();
        }

        require __DIR__ . '/../view/huespedes/listar.php';
    }

    private function crear(): void
    {
        $titulo = 'Nuevo huésped';
        $error = null;
        $datos = ['nombre' => '', 'documento' => '', 'correo' => '', 'telefono' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->leerFormulario();
            try {
                $huesped = new Huesped($datos['nombre'], $datos['documento'], $datos['correo'], $datos['telefono']);
                $this->service->registrar($huesped);
                $this->volverAlListado('mensaje', 'Huésped registrado correctamente');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../view/huespedes/formulario.php';
    }

    private function editar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $original = $this->service->buscarPorId($id);
        if ($original === null) {
            $this->volverAlListado('error', 'Selecciona un huésped de la tabla');
        }

        $titulo = 'Editar huésped';
        $error = null;
        $datos = [
            'nombre' => $original->nombre,
            'documento' => $original->documento,
            'correo' => $original->correo,
            'telefono' => $original->telefono,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->leerFormulario();
            try {
                $huesped = new Huesped($datos['nombre'], $datos['documento'], $datos['correo'], $datos['telefono'], $id);
                $this->service->actualizar($huesped);
                $this->volverAlListado('mensaje', 'Huésped actualizado correctamente');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../view/huespedes/formulario.php';
    }

    private function eliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->volverAlListado('error', 'Acción no permitida');
        }

        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->service->eliminar($id);
            $this->volverAlListado('mensaje', 'Huésped eliminado');
        } catch (Exception $e) {
            $this->volverAlListado('error', $e->getMessage());
        }
    }

    private function leerFormulario(): array
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'documento' => trim($_POST['documento'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
        ];
    }

    private function volverAlListado(string $tipo, string $texto): void
    {
        $_SESSION[$tipo] = $texto;
        header('Location: huespedes.php');
        exit;
    }

    private function tomarAviso(string $tipo): ?string
    {
        $texto = $_SESSION[$tipo] ?? null;
        unset($_SESSION[$tipo]);
        return $texto;
    }
}
