<?php

require_once __DIR__ . '/../service/ReservaService.php';
require_once __DIR__ . '/../service/HuespedService.php';
require_once __DIR__ . '/../service/HabitacionService.php';
require_once __DIR__ . '/../core/ReglaNegocioException.php';

class ReservaController
{
    private ReservaService $service;
    private HuespedService $huespedService;
    private HabitacionService $habitacionService;

    public function __construct()
    {
        $this->service = new ReservaService();
        $this->huespedService = new HuespedService();
        $this->habitacionService = new HabitacionService();
    }

    public function atender(): void
    {
        exigirPermiso('RESERVAS');
        $accion = $_GET['accion'] ?? 'listar';

        if ($accion === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crear();
            return;
        }
        if ($accion === 'cancelar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->cancelar();
            return;
        }
        $this->listar();
    }

    private function crear(): void
    {
        try {
            $entrada = trim($_POST['fecha_entrada'] ?? '');
            $salida = trim($_POST['fecha_salida'] ?? '');

            if (isset($_POST['entrada_fecha'], $_POST['entrada_hora'])) {
                $entrada = trim($_POST['entrada_fecha']) . 'T' . trim($_POST['entrada_hora']);
            }
            if (isset($_POST['salida_fecha'], $_POST['salida_hora'])) {
                $salida = trim($_POST['salida_fecha']) . 'T' . trim($_POST['salida_hora']);
            }

            $reserva = $this->service->crear(
                (int) ($_POST['huesped_id'] ?? 0),
                (int) ($_POST['habitacion_id'] ?? 0),
                $entrada,
                $salida
            );
            $this->redirigir('mensaje', 'Reserva #' . $reserva->id . ' creada correctamente.');
        } catch (Throwable $error) {
            $this->listar($error instanceof Exception ? $error->getMessage() : 'No se pudo crear la reserva.');
        }
    }

    private function cancelar(): void
    {
        try {
            $this->service->cancelar((int) ($_POST['id'] ?? 0));
            $this->redirigir('mensaje', 'Reserva cancelada correctamente.');
        } catch (Throwable $error) {
            $this->redirigir('error', $error instanceof Exception ? $error->getMessage() : 'No se pudo cancelar la reserva.');
        }
    }

    private function listar(?string $error = null): void
    {
        $mensaje = $this->tomarAviso('mensaje');
        $error = $error ?? $this->tomarAviso('error');

        try {
            $reservas = $this->service->listar();
            $huespedes = $this->huespedService->listar();
            $habitaciones = $this->habitacionService->listar();
        } catch (Throwable $e) {
            $reservas = [];
            $huespedes = [];
            $habitaciones = [];
            $error = 'No se pudieron cargar los datos: ' . $e->getMessage();
        }

        $minimo = (new DateTimeImmutable())->format('Y-m-d\TH:i');
        require __DIR__ . '/../view/reservas/listar.php';
    }

    private function redirigir(string $tipo, string $texto): void
    {
        $_SESSION[$tipo] = $texto;
        header('Location: reservas.php');
        exit;
    }

    private function tomarAviso(string $tipo): ?string
    {
        $texto = $_SESSION[$tipo] ?? null;
        unset($_SESSION[$tipo]);
        return $texto;
    }
}
