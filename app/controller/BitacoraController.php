<?php

require_once __DIR__ . '/../dao/EventoDAO.php';

class BitacoraController
{
    public function atender(): void
    {
        Sesion::exigirPermiso('BITACORA');
        $eventoDao = new EventoDAO();
        $eventos = [];
        $error = null;
        try {
            $eventos = $eventoDao->listar();
        } catch (Throwable $e) {
            $error = 'No se pudo cargar la bitácora: ' . $e->getMessage();
        }
        require __DIR__ . '/../view/bitacora/listar.php';
    }
}
