<?php

require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../service/BitacoraService.php';

class BitacoraController
{
    private BitacoraService $service;
    private HabitacionDAO $habitacionDao;

    public function __construct()
    {
        $this->service = new BitacoraService();
        $this->habitacionDao = new HabitacionDAO();
    }

    public function atender(): void
    {
        exigirPermiso('BITACORA');

        $filtros = $this->leerFiltros();
        $eventos = [];
        $habitaciones = [];
        $error = null;

        try {
            $habitaciones = $this->habitacionDao->listar();
            $eventos = $this->service->listar($filtros['desde'], $filtros['hasta'], $filtros['habitacion']);
        } catch (Throwable $e) {
            $error = get_class($e) === 'Exception'
                ? $e->getMessage()
                : 'No se pudo cargar la bitácora: ' . $e->getMessage();
        }

        $consultaReporte = 'desde=' . $filtros['desde'] . '&hasta=' . $filtros['hasta'] . '&habitacion=' . $filtros['habitacion'];

        require __DIR__ . '/../view/bitacora/listar.php';
    }

    private function leerFiltros(): array
    {
        return [
            'desde' => trim($_GET['desde'] ?? ''),
            'hasta' => trim($_GET['hasta'] ?? ''),
            'habitacion' => trim($_GET['habitacion'] ?? ''),
        ];
    }
}
