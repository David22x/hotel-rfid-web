<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../dao/LlaveDAO.php';
require_once __DIR__ . '/../dao/EventoDAO.php';

// Check-in dentro de una transacción
class CheckInService
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

    public function checkIn(int $reservaId, string $uid): void
    {
        $uid = strtoupper(trim($uid));
        if (!preg_match('/^[A-F0-9]{4,20}$/', $uid)) {
            $mensaje = 'El UID debe tener entre 4 y 20 caracteres hexadecimales.';
            $this->eventoDao->registrarFallo('CHECK_IN', null, $uid, 'UID_INVALIDO', $mensaje);
            throw new Exception($mensaje);
        }

        $conexion = conectar();
        $habitacionId = null;
        try {
            $conexion->beginTransaction();

            $reserva = $this->reservaDao->buscarParaActualizar($reservaId);
            if ($reserva === null) {
                throw new Exception('La reserva no existe.');
            }
            if ($reserva->estado !== 'PENDIENTE') {
                throw new Exception('Solo se puede hacer check-in de una reserva pendiente.');
            }

            $ahora = new DateTimeImmutable();
            $entrada = new DateTimeImmutable($reserva->fechaEntrada);
            $salida = new DateTimeImmutable($reserva->fechaSalida);
            if ($ahora < $entrada->modify('-2 hours')) {
                throw new Exception('Aún no corresponde el horario de check-in de esta reserva.');
            }
            if ($ahora > $salida) {
                throw new Exception('El horario de la reserva ya venció.');
            }

            $habitacionId = $reserva->habitacionId;
            $habitacion = $this->habitacionDao->buscarParaActualizar($habitacionId);
            if ($habitacion === null) {
                throw new Exception('La habitación no existe.');
            }
            if ($habitacion->estado !== 'LIBRE') {
                throw new Exception('La habitación no está disponible para check-in.');
            }
            if ($this->llaveDao->uidTieneLlaveActiva($uid)) {
                throw new Exception('La tarjeta ya tiene una llave activa.');
            }

            $this->reservaDao->actualizarEstado($reservaId, 'ACTIVA');
            $this->llaveDao->crear(new Llave($reservaId, $uid, $reserva->fechaEntrada, $reserva->fechaSalida));
            $this->habitacionDao->actualizarEstado($habitacionId, 'OCUPADA');
            $this->eventoDao->registrar('CHECK_IN', $habitacionId, $uid, 'EXITO', 'Check-in registrado para la reserva #' . $reservaId);
            $this->eventoDao->registrar('LLAVE_EMITIDA', $habitacionId, $uid, 'EXITO', 'Llave activa emitida');

            $conexion->commit();
        } catch (Throwable $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $this->eventoDao->registrarFallo(
                'CHECK_IN',
                $habitacionId,
                $uid,
                'FALLO',
                $error instanceof Exception ? $error->getMessage() : 'Error inesperado durante el check-in.'
            );
            if (get_class($error) === 'Exception') {
                throw $error;
            }
            throw new RuntimeException('No se pudo completar el check-in. Los cambios fueron revertidos.');
        }
    }
}
