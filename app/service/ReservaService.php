<?php

require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/HuespedDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../model/Reserva.php';
require_once __DIR__ . '/../core/ReglaNegocioException.php';

class ReservaService
{
    private ReservaDAO $reservaDao;
    private HuespedDAO $huespedDao;
    private HabitacionDAO $habitacionDao;

    public function __construct()
    {
        $this->reservaDao = new ReservaDAO();
        $this->huespedDao = new HuespedDAO();
        $this->habitacionDao = new HabitacionDAO();
    }

    public function listar(): array
    {
        return $this->reservaDao->listar();
    }

    public function listarPendientes(): array
    {
        return $this->reservaDao->buscarPendientes();
    }

    public function buscarPorId(int $id): ?Reserva
    {
        return $this->reservaDao->buscarPorId($id);
    }

    public function crear(int $huespedId, int $habitacionId, string $entrada, string $salida): Reserva
    {
        if ($huespedId <= 0) {
            throw new ReglaNegocioException('Seleccione un huésped válido.');
        }
        if ($habitacionId <= 0) {
            throw new ReglaNegocioException('Seleccione una habitación válida.');
        }
        if ($this->huespedDao->buscarPorId($huespedId) === null) {
            throw new ReglaNegocioException('El huésped seleccionado no existe.');
        }
        if ($this->habitacionDao->buscarPorId($habitacionId) === null) {
            throw new ReglaNegocioException('La habitación seleccionada no existe.');
        }

        $entradaTime = $this->leerFechaHora($entrada, 'entrada');
        $salidaTime = $this->leerFechaHora($salida, 'salida');
        $ahora = time();

        if ($entradaTime < $ahora) {
            throw new ReglaNegocioException('La fecha y hora de entrada no pueden estar en el pasado.');
        }
        if ($salidaTime <= $entradaTime) {
            throw new ReglaNegocioException('La salida debe ser posterior a la entrada.');
        }
        if ($this->reservaDao->hayCruce($habitacionId, date('Y-m-d H:i:s', $entradaTime), date('Y-m-d H:i:s', $salidaTime))) {
            throw new ReglaNegocioException('La habitación ya tiene una reserva activa o pendiente que se cruza con ese horario.');
        }

        $reserva = new Reserva(
            $huespedId,
            $habitacionId,
            date('Y-m-d H:i:s', $entradaTime),
            date('Y-m-d H:i:s', $salidaTime)
        );
        $reserva->id = $this->reservaDao->crear($reserva);
        return $reserva;
    }

    public function cancelar(int $id): void
    {
        $reserva = $this->reservaDao->buscarPorId($id);
        if ($reserva === null) {
            throw new ReglaNegocioException('La reserva no existe.');
        }
        if ($reserva->estado !== 'PENDIENTE') {
            throw new ReglaNegocioException('Solo se pueden cancelar reservas pendientes.');
        }
        $this->reservaDao->actualizarEstado($id, 'CANCELADA');
    }

    private function leerFechaHora(string $valor, string $tipo): int
    {
        $valor = trim($valor);
        $fecha = date_create_immutable_from_format('Y-m-d\TH:i', $valor);
        if ($fecha === false) {
            throw new ReglaNegocioException('La fecha y hora de ' . $tipo . ' no tienen un formato válido.');
        }
        $errores = date_get_last_errors();
        if (is_array($errores) && ($errores['warning_count'] > 0 || $errores['error_count'] > 0)) {
            throw new ReglaNegocioException('La fecha y hora de ' . $tipo . ' no existen.');
        }
        return $fecha->getTimestamp();
    }
}
