<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../dao/LlaveDAO.php';
require_once __DIR__ . '/../dao/EventoDAO.php';
require_once __DIR__ . '/../model/Llave.php';
require_once __DIR__ . '/../model/Evento.php';

// Check-in y check-out dentro de transacciones
class OperacionHotelService
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
            $this->registrarFallo('CHECK_IN', null, $uid, 'UID_INVALIDO', 'El UID debe tener entre 4 y 20 caracteres hexadecimales.');
            throw new Exception('El UID debe tener entre 4 y 20 caracteres hexadecimales.');
        }

        $conexion = conectar();
        try {
            $conexion->beginTransaction();

            $consulta = $conexion->prepare(
                'SELECT id, huesped_id, habitacion_id, fecha_entrada, fecha_salida, estado
                 FROM reserva WHERE id = ? FOR UPDATE'
            );
            $consulta->execute([$reservaId]);
            $fila = $consulta->fetch();
            if ($fila === false) {
                throw new Exception('La reserva no existe.');
            }
            if ($fila['estado'] !== 'PENDIENTE') {
                throw new Exception('Solo se puede hacer check-in de una reserva pendiente.');
            }

            $ahora = new DateTimeImmutable();
            $entrada = new DateTimeImmutable($fila['fecha_entrada']);
            $salida = new DateTimeImmutable($fila['fecha_salida']);
            if ($ahora < $entrada->modify('-2 hours')) {
                throw new Exception('Aún no corresponde el horario de check-in de esta reserva.');
            }
            if ($ahora > $salida) {
                throw new Exception('El horario de la reserva ya venció.');
            }

            $habitacionId = (int) $fila['habitacion_id'];
            $consultaHabitacion = $conexion->prepare(
                'SELECT id, estado FROM habitacion WHERE id = ? FOR UPDATE'
            );
            $consultaHabitacion->execute([$habitacionId]);
            $habitacion = $consultaHabitacion->fetch();
            if ($habitacion === false) {
                throw new Exception('La habitación no existe.');
            }
            if ($habitacion['estado'] !== 'LIBRE') {
                throw new Exception('La habitación no está disponible para check-in.');
            }

            $consultaLlave = $conexion->prepare(
                'SELECT id FROM llave WHERE uid = ? AND estado = ? LIMIT 1'
            );
            $consultaLlave->execute([$uid, 'ACTIVA']);
            if ($consultaLlave->fetch() !== false) {
                throw new Exception('La tarjeta ya tiene una llave activa.');
            }

            $actualizarReserva = $conexion->prepare('UPDATE reserva SET estado = ? WHERE id = ?');
            $actualizarReserva->execute(['ACTIVA', $reservaId]);

            $crearLlave = $conexion->prepare(
                'INSERT INTO llave (reserva_id, uid, valida_desde, valida_hasta, estado)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $crearLlave->execute([
                $reservaId,
                $uid,
                $fila['fecha_entrada'],
                $fila['fecha_salida'],
                'ACTIVA',
            ]);

            $actualizarHabitacion = $conexion->prepare(
                'UPDATE habitacion SET estado = ? WHERE id = ?'
            );
            $actualizarHabitacion->execute(['OCUPADA', $habitacionId]);

            $crearEvento = $conexion->prepare(
                'INSERT INTO evento (fecha_hora, tipo, habitacion_id, uid, resultado, detalle)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $ahoraTexto = $ahora->format('Y-m-d H:i:s');
            $crearEvento->execute([
                $ahoraTexto,
                'CHECK_IN',
                $habitacionId,
                $uid,
                'EXITO',
                'Check-in registrado para la reserva #' . $reservaId,
            ]);
            $crearEvento->execute([
                $ahoraTexto,
                'LLAVE_EMITIDA',
                $habitacionId,
                $uid,
                'EXITO',
                'Llave activa emitida',
            ]);

            $conexion->commit();
        } catch (Throwable $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $this->registrarFallo(
                'CHECK_IN',
                isset($habitacionId) ? $habitacionId : null,
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

    public function checkOut(int $reservaId): void
    {
        $conexion = conectar();
        try {
            $conexion->beginTransaction();

            $consulta = $conexion->prepare(
                'SELECT id, habitacion_id, estado FROM reserva WHERE id = ? FOR UPDATE'
            );
            $consulta->execute([$reservaId]);
            $fila = $consulta->fetch();
            if ($fila === false) {
                throw new Exception('La reserva no existe.');
            }
            if ($fila['estado'] !== 'ACTIVA') {
                throw new Exception('Solo se puede hacer check-out de una reserva activa.');
            }

            $habitacionId = (int) $fila['habitacion_id'];
            $consultaHabitacion = $conexion->prepare(
                'SELECT id, estado FROM habitacion WHERE id = ? FOR UPDATE'
            );
            $consultaHabitacion->execute([$habitacionId]);
            $habitacion = $consultaHabitacion->fetch();
            if ($habitacion === false) {
                throw new Exception('La habitación no existe.');
            }

            $actualizarReserva = $conexion->prepare('UPDATE reserva SET estado = ? WHERE id = ?');
            $actualizarReserva->execute(['FINALIZADA', $reservaId]);

            $revocarLlaves = $conexion->prepare(
                'UPDATE llave SET estado = ? WHERE reserva_id = ? AND estado = ?'
            );
            $revocarLlaves->execute([
                'REVOCADA',
                $reservaId,
                'ACTIVA',
            ]);

            $actualizarHabitacion = $conexion->prepare(
                'UPDATE habitacion SET estado = ? WHERE id = ?'
            );
            $actualizarHabitacion->execute(['POR_LIMPIAR', $habitacionId]);

            $crearEvento = $conexion->prepare(
                'INSERT INTO evento (fecha_hora, tipo, habitacion_id, uid, resultado, detalle)
                 VALUES (?, ?, ?, NULL, ?, ?)'
            );
            $ahora = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $crearEvento->execute([
                $ahora,
                'CHECK_OUT',
                $habitacionId,
                'EXITO',
                'Check-out registrado y llaves revocadas para la reserva #' . $reservaId,
            ]);

            $conexion->commit();
        } catch (Throwable $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $this->registrarFallo(
                'CHECK_OUT',
                isset($habitacionId) ? $habitacionId : null,
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

    private function registrarFallo(
        string $tipo,
        ?int $habitacionId,
        ?string $uid,
        string $resultado,
        string $detalle
    ): void {
        try {
            $this->eventoDao->crear(new Evento(
                (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                $tipo,
                $habitacionId,
                $uid,
                $resultado,
                function_exists('mb_substr') ? mb_substr($detalle, 0, 255) : substr($detalle, 0, 255)
            ));
        } catch (Throwable $ignored) {
        }
    }
}
