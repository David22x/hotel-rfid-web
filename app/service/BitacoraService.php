<?php

require_once __DIR__ . '/../dao/EventoDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';

// Consultas de la bitácora para la cerradura, la pantalla de bitácora y el reporte PDF
class BitacoraService
{
    private EventoDAO $eventoDao;
    private HabitacionDAO $habitacionDao;

    public function __construct()
    {
        $this->eventoDao = new EventoDAO();
        $this->habitacionDao = new HabitacionDAO();
    }

    // Las fechas llegan como AAAA-MM-DD desde los campos type="date". Un filtro vacío no se aplica.
    public function listar(string $desde, string $hasta, string $habitacion): array
    {
        if ($desde !== '' && $hasta !== '' && $desde > $hasta) {
            throw new ReglaNegocioException('La fecha inicial no puede ser mayor que la fecha final.');
        }

        // El día inicial empieza a las 00:00:00 y el final termina a las 23:59:59
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

    // Texto corto del último evento de una habitación, para mostrarlo en la cerradura
    public function describirUltimo(int $habitacionId): string
    {
        // listar ya viene ordenado del más reciente al más antiguo
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

    // Número de la habitación para la cabecera del reporte PDF
    public function numeroDeHabitacion(int $habitacionId): string
    {
        $habitacion = $this->habitacionDao->buscarPorId($habitacionId);
        if ($habitacion === null) {
            return (string) $habitacionId;
        }

        return $habitacion->numero;
    }
}
