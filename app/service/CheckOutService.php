<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../dao/LlaveDAO.php';
require_once __DIR__ . '/../dao/EventoDAO.php';

// Check-out dentro de una transacción
class CheckOutService
{
    private ReservaDAO $reservaDao;
    private HabitacionDAO $habitacionDao;
    private LlaveDAO $llaveDao;
    private EventoDAO $eventoDao;

    public function __construct()
    {
        $this->reservaDao = new ReservaDAO();
        $this->habitacionDao = new HabitacionDAO();
        $this->llaveDao = new LlaveDAO();
        $this->eventoDao = new EventoDAO();
    }

    public function checkOut(int $reservaId): void
    {
        $conexion = conectar();
        $habitacionId = null;
        try {
            $conexion->beginTransaction();

            $reserva = $this->reservaDao->buscarParaActualizar($reservaId);
            if ($reserva === null) {
                throw new Exception('La reserva no existe.');
            }
            if ($reserva->estado !== 'ACTIVA') {
                throw new Exception('Solo se puede hacer check-out de una reserva activa.');
            }

            $habitacionId = $reserva->habitacionId;
            if ($this->habitacionDao->buscarParaActualizar($habitacionId) === null) {
                throw new Exception('La habitación no existe.');
            }

            $this->reservaDao->actualizarEstado($reservaId, 'FINALIZADA');
            $this->llaveDao->revocarActivasPorReserva($reservaId);
            $this->habitacionDao->actualizarEstado($habitacionId, 'POR_LIMPIAR');
            $this->eventoDao->registrar(
                'CHECK_OUT',
                $habitacionId,
                null,
                'EXITO',
                'Check-out registrado y llaves revocadas para la reserva #' . $reservaId
            );

            $conexion->commit();
        } catch (Throwable $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $this->eventoDao->registrarFallo(
                'CHECK_OUT',
                $habitacionId,
                null,
                'FALLO',
                $error instanceof Exception ? $error->getMessage() : 'Error inesperado durante el check-out.'
            );
            if (get_class($error) === 'Exception') {
                throw $error;
            }
            throw new RuntimeException('No se pudo completar el check-out. Los cambios fueron revertidos.');
        }
    }
}
