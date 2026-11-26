<?php

require_once __DIR__ . '/../service/HabitacionService.php';

class HabitacionController
{
    private HabitacionService $service;

    public function __construct()
    {
        $this->service = new HabitacionService();
    }

    public function atender(): void
    {
        exigirPermiso('HABITACIONES');
        $accion = $_GET['accion'] ?? 'listar';

        if ($accion === 'crear') {
            $this->crear();
        } elseif ($accion === 'editar') {
            $this->editar();
        } elseif ($accion === 'eliminar') {
            $this->eliminar();
        } elseif ($accion === 'cambiarEstado') {
            $this->cambiarEstado();
        } else {
            $this->listar();
        }
    }

    private function listar(): void
    {
        $habitaciones = [];
        $mensaje = $this->tomarAviso('mensaje');
        $error = $this->tomarAviso('error');

        try {
            $habitaciones = $this->service->listar();
        } catch (Exception $e) {
            $error = 'No se pudieron cargar las habitaciones: ' . $e->getMessage();
        }

        require __DIR__ . '/../view/habitaciones/listar.php';
    }

    private function crear(): void
    {
        $titulo = 'Nueva habitación';
        $esNueva = true;
        $error = null;
        $datos = ['numero' => '', 'piso' => '', 'tipo' => 'Simple', 'estado' => 'LIBRE'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->leerFormulario();
            try {
                $habitacion = $this->armarHabitacion($datos, null);
                $this->service->crear($habitacion);
                $this->volverAlListado('mensaje', 'Habitación creada correctamente');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../view/habitaciones/formulario.php';
    }

    private function editar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $original = $this->service->buscarPorId($id);
        if ($original === null) {
            $this->volverAlListado('error', 'Selecciona una habitación de la tabla');
        }

        $titulo = 'Editar habitación';
        $esNueva = false;
        $error = null;
        $datos = [
            'numero' => $original->numero,
            'piso' => $original->piso,
            'tipo' => $original->tipo,
            'estado' => $original->estado,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->leerFormulario();
            try {
                $habitacion = $this->armarHabitacion($datos, $id);
                $this->service->actualizar($habitacion);
                $this->volverAlListado('mensaje', 'Habitación actualizada correctamente');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../view/habitaciones/formulario.php';
    }

    private function eliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->volverAlListado('error', 'Acción no permitida');
        }

        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->service->eliminar($id);
            $this->volverAlListado('mensaje', 'Habitación eliminada');
        } catch (Exception $e) {
            $this->volverAlListado('error', $e->getMessage());
        }
    }

    private function cambiarEstado(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $habitacion = $this->service->buscarPorId($id);
        if ($habitacion === null) {
            $this->volverAlListado('error', 'Selecciona una habitación de la tabla');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevo = $_POST['estado'] ?? '';

            if ($nuevo === $habitacion->estado) {
                $this->volverAlListado('mensaje', 'La habitación ya tiene ese estado');
            }

            try {
                $this->service->cambiarEstado($id, $nuevo);
                $this->volverAlListado('mensaje', 'Estado actualizado a ' . $nuevo);
            } catch (Exception $e) {
                $this->volverAlListado('error', $e->getMessage());
            }
        }

        require __DIR__ . '/../view/habitaciones/cambiar_estado.php';
    }

    private function leerFormulario(): array
    {
        return [
            'numero' => trim($_POST['numero'] ?? ''),
            'piso' => trim($_POST['piso'] ?? ''),
            'tipo' => $_POST['tipo'] ?? '',
            'estado' => $_POST['estado'] ?? 'LIBRE',
        ];
    }

    private function armarHabitacion(array $datos, ?int $id): Habitacion
    {
        if ($datos['numero'] === '') {
            throw new Exception('El número es obligatorio');
        }
        if (!ctype_digit($datos['piso'])) {
            throw new Exception('El piso debe ser un número mayor o igual a 0');
        }

        return new Habitacion($datos['numero'], (int) $datos['piso'], $datos['tipo'], $datos['estado'], $id);
    }

    private function volverAlListado(string $tipo, string $texto): void
    {
        $_SESSION[$tipo] = $texto;
        header('Location: habitaciones.php');
        exit;
    }

    private function tomarAviso(string $tipo): ?string
    {
        $texto = $_SESSION[$tipo] ?? null;
        unset($_SESSION[$tipo]);
        return $texto;
    }
}
