<?php

require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../service/BitacoraService.php';

class CerraduraController
{
    private HabitacionDAO $habitacionDao;
    private BitacoraService $bitacora;

    public function __construct()
    {
        $this->habitacionDao = new HabitacionDAO();
        $this->bitacora = new BitacoraService();
    }

    public function atender(): void
    {
        exigirPermiso('CERRADURA_RFID');

        $habitaciones = [];
        $habitacionId = (int) ($_GET['habitacion'] ?? 0);
        $ultimoEvento = 'sin registros';
        $error = null;

        try {
            $habitaciones = $this->habitacionDao->listar();
            if ($habitacionId === 0 && count($habitaciones) > 0) {
                $habitacionId = $habitaciones[0]->id;
            }
            if ($habitacionId > 0) {
                $ultimoEvento = $this->bitacora->describirUltimo($habitacionId);
            }
        } catch (Exception $e) {
            $error = 'No se pudieron cargar las habitaciones.';
        }

        require __DIR__ . '/../view/cerradura/panel.php';
    }
}
