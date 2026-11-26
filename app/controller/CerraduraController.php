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

    // Muestra la pantalla de la cerradura. La habitación elegida llega por GET.
    // Cada tarjeta la valida api/validar_acceso.php, que cerradura.js llama con $.post.
    public function atender(): void
    {
        Sesion::exigirPermiso('CERRADURA_RFID');

        $habitaciones = [];
        $habitacionId = (int) ($_GET['habitacion'] ?? 0);
        $ultimoEvento = 'sin registros';
        $error = null;

        try {
            $habitaciones = $this->habitacionDao->listar();
            // Si no se eligió ninguna, se muestra la primera
            if ($habitacionId === 0 && count($habitaciones) > 0) {
                $habitacionId = $habitaciones[0]->id;
            }
            if ($habitacionId > 0) {
                $ultimoEvento = $this->bitacora->describirUltimo($habitacionId);
            }
        } catch (Exception $e) {
            $error = 'No se pudieron cargar las habitaciones.';
        }

        $tokenCsrf = Csrf::generar();

        require __DIR__ . '/../view/cerradura/panel.php';
    }
}
