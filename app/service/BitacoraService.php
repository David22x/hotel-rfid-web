<?php

require_once __DIR__ . '/../dao/EventoDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';

class BitacoraService
{
    private EventoDAO $eventoDao;
    private HabitacionDAO $habitacionDao;

    public function __construct()
    {
        $this->eventoDao = new EventoDAO();
        $this->habitacionDao = new HabitacionDAO();
    }

    public function listar(string $desde, string $hasta, string $habitacion): array
    {
        if ($desde !== '' && $hasta !== '' && $desde > $hasta) {
            throw new Exception('La fecha inicial no puede ser mayor que la fecha final.');
        }

        $fechaDesde = null;
        $fechaHasta = null;
        $habitacionId = null;
        if ($desde !== '') {
            $fechaDesde = $desde . ' 00:00:00';
        }
        if ($hasta !== '') {
            $fechaHasta = $hasta . ' 23:59:59';
        }
        if ($habitacion !== '') {
            $habitacionId = (int) $habitacion;
        }

        return $this->eventoDao->listar($fechaDesde, $fechaHasta, $habitacionId);
    }

    public function describirUltimo(int $habitacionId): string
    {
        $eventos = $this->eventoDao->listar(null, null, $habitacionId);
        if (count($eventos) === 0) {
            return 'sin registros';
        }

        $evento = $eventos[0];
        $texto = date('d/m/Y H:i:s', strtotime($evento['fechaHora'])) . ' | ' . $evento['tipo'];
        if ($evento['uid'] !== null) {
            $texto .= ' | UID ' . $evento['uid'];
        }

        return $texto;
    }

    public function numeroDeHabitacion(int $habitacionId): string
    {
        $habitacion = $this->habitacionDao->buscarPorId($habitacionId);
        if ($habitacion === null) {
            return (string) $habitacionId;
        }

        return $habitacion->numero;
    }
}
