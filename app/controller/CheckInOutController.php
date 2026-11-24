<?php

require_once __DIR__ . '/../service/OperacionHotelService.php';
require_once __DIR__ . '/../service/ReservaService.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/ReglaNegocioException.php';
require_once __DIR__ . '/../model/EstadoReserva.php';

class CheckInOutController
{
    private OperacionHotelService $operacion;
    private ReservaService $reservas;

    public function __construct()
    {
        $this->operacion = new OperacionHotelService();
        $this->reservas = new ReservaService();
    }

    public function atender(): void
    {
        Sesion::exigirPermiso('CHECK_IN_OUT');
        $accion = $_GET['accion'] ?? 'listar';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($accion === 'checkin') {
                $this->checkIn();
                return;
            }
            if ($accion === 'checkout') {
                $this->checkOut();
                return;
            }
        }
        $this->listar();
    }

    private function checkIn(): void
    {
        try {
            Csrf::validar();
            $this->operacion->checkIn(
                (int) ($_POST['reserva_id'] ?? 0),
                trim($_POST['uid'] ?? '')
            );
            $this->redirigir('mensaje', 'Check-in registrado y llave activada.');
        } catch (Throwable $error) {
            $this->redirigir('error', $error instanceof Exception ? $error->getMessage() : 'No se pudo realizar el check-in.');
        }
    }

    private function checkOut(): void
    {
        try {
            Csrf::validar();
            $this->operacion->checkOut((int) ($_POST['reserva_id'] ?? 0));
            $this->redirigir('mensaje', 'Check-out registrado. La habitación quedó pendiente de limpieza.');
        } catch (Throwable $error) {
            $this->redirigir('error', $error instanceof Exception ? $error->getMessage() : 'No se pudo realizar el check-out.');
        }
    }

    private function listar(?string $error = null): void
    {
        $mensaje = $this->tomarAviso('mensaje');
        $error = $error ?? $this->tomarAviso('error');
        $pendientes = [];
        $activas = [];

        try {
            foreach ($this->reservas->listar() as $reserva) {
                if ($reserva->estado === EstadoReserva::PENDIENTE) {
                    $pendientes[] = $reserva;
                } elseif ($reserva->estado === EstadoReserva::ACTIVA) {
                    $activas[] = $reserva;
                }
            }
        } catch (Throwable $e) {
            $error = 'No se pudieron cargar las reservas: ' . $e->getMessage();
        }

        require __DIR__ . '/../view/checkin/listar.php';
    }

    private function redirigir(string $tipo, string $texto): void
    {
        $_SESSION[$tipo] = $texto;
        header('Location: checkin.php');
        exit;
    }

    private function tomarAviso(string $tipo): ?string
    {
        $texto = $_SESSION[$tipo] ?? null;
        unset($_SESSION[$tipo]);
        return $texto;
    }
}
