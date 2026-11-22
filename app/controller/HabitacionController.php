<?php

require_once __DIR__ . '/../service/HabitacionService.php';

// TODO: cuando exista el login, verificar que el rol sea ADMIN para
// crear, editar, eliminar y cambiar estado (el recepcionista solo consulta).

class HabitacionController
{
    private HabitacionService $service;

    public function __construct()
    {
        $this->service = new HabitacionService();
    }

    // Punto de entrada: lee ?accion= de la URL y llama al método que toca.
    // En Java esto lo hacían los addActionListener de cada botón.
    public function atender(): void
    {
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

    // ---------- ACCIONES ----------

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
        // Valores iniciales del formulario vacío
        $datos = ['numero' => '', 'piso' => '', 'tipo' => 'Simple', 'estado' => EstadoHabitacion::LIBRE];

        // Si el formulario se envió, intentamos guardar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->leerFormulario();
            try {
                $habitacion = $this->armarHabitacion($datos, null);
                $this->service->crear($habitacion);
                $this->volverAlListado('mensaje', 'Habitación creada correctamente');
            } catch (Exception $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                $error = 'Error inesperado: ' . $e->getMessage();
            }
        }

        // Si es la primera vez (GET) o hubo un error, mostramos el formulario
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
        // Rellenamos el formulario con los datos actuales
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
            } catch (Exception $e) {
                $error = 'Error inesperado: ' . $e->getMessage();
            }
        }

        require __DIR__ . '/../view/habitaciones/formulario.php';
    }

    private function eliminar(): void
    {
        // Solo se elimina con POST (desde el botón), nunca con un simple enlace
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->volverAlListado('error', 'Acción no permitida');
        }

        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->service->eliminar($id);
            $this->volverAlListado('mensaje', 'Habitación eliminada');
        } catch (Exception $e) {
            $this->volverAlListado('error', $e->getMessage());
        } catch (Exception $e) {
            $this->volverAlListado('error', 'Error inesperado: ' . $e->getMessage());
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
            } catch (Exception $e) {
                $this->volverAlListado('error', 'Error inesperado: ' . $e->getMessage());
            }
        }

        require __DIR__ . '/../view/habitaciones/cambiar_estado.php';
    }

    // ---------- AYUDANTES ----------

    // Lee los campos que llegaron del formulario (siempre llegan como texto)
    private function leerFormulario(): array
    {
        return [
            'numero' => trim($_POST['numero'] ?? ''),
            'piso' => trim($_POST['piso'] ?? ''),
            'tipo' => $_POST['tipo'] ?? '',
            // Al crear, el <select> de estado está deshabilitado y no se envía
            'estado' => $_POST['estado'] ?? EstadoHabitacion::LIBRE,
        ];
    }

    // Validación de formato (lo que en Java hacía HabitacionFormDialog.onAceptar)
    private function armarHabitacion(array $datos, ?int $id): Habitacion
    {
        if ($datos['numero'] === '') {
            throw new Exception('El número es obligatorio');
        }
        // ctype_digit: true solo si son puros dígitos (así descarta negativos y decimales)
        if (!ctype_digit($datos['piso'])) {
            throw new Exception('El piso debe ser un número mayor o igual a 0');
        }

        return new Habitacion($datos['numero'], (int) $datos['piso'], $datos['tipo'], $datos['estado'], $id);
    }

    // Guarda un aviso en la sesión y redirige al listado.
    // Así el mensaje sobrevive a la redirección (equivale a los JOptionPane de Java).
    private function volverAlListado(string $tipo, string $texto): void
    {
        $_SESSION[$tipo] = $texto;
        header('Location: habitaciones.php');
        exit;
    }

    // Lee un aviso de la sesión y lo borra para que no se muestre dos veces
    private function tomarAviso(string $tipo): ?string
    {
        $texto = $_SESSION[$tipo] ?? null;
        unset($_SESSION[$tipo]);
        return $texto;
    }
}
