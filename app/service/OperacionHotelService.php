<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/ReglaNegocioException.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../dao/LlaveDAO.php';
require_once __DIR__ . '/../dao/EventoDAO.php';
require_once __DIR__ . '/../model/EstadoReserva.php';
require_once __DIR__ . '/../model/EstadoHabitacion.php';
require_once __DIR__ . '/../model/EstadoLlave.php';
require_once __DIR__ . '/../model/TipoEvento.php';
require_once __DIR__ . '/../model/Llave.php';
require_once __DIR__ . '/../model/Evento.php';

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
            throw new ReglaNegocioException('El UID debe tener entre 4 y 20 caracteres hexadecimales.');
        }

        $conexion = Database::conectar();
        try {
            $conexion->beginTransaction();

            $consulta = $conexion->prepare(
                'SELECT id, huesped_id, habitacion_id, fecha_entrada, fecha_salida, estado
                 FROM reserva WHERE id = ? FOR UPDATE'
            );
            $consulta->execute([$reservaId]);
            $fila = $consulta->fetch();
            if ($fila === false) {
                throw new ReglaNegocioException('La reserva no existe.');
            }
            if ($fila['estado'] !== EstadoReserva::PENDIENTE) {
                throw new ReglaNegocioException('Solo se puede hacer check-in de una reserva pendiente.');
            }

            $ahora = new DateTimeImmutable();
            $entrada = new DateTimeImmutable($fila['fecha_entrada']);
            $salida = new DateTimeImmutable($fila['fecha_salida']);
            if ($ahora < $entrada->modify('-2 hours')) {
                throw new ReglaNegocioException('Aún no corresponde el horario de check-in de esta reserva.');
            }
            if ($ahora > $salida) {
                throw new ReglaNegocioException('El horario de la reserva ya venció.');
            }

            $habitacionId = (int) $fila['habitacion_id'];
            $consultaHabitacion = $conexion->prepare(
                'SELECT id, estado FROM habitacion WHERE id = ? FOR UPDATE'
            );
            $consultaHabitacion->execute([$habitacionId]);
            $habitacion = $consultaHabitacion->fetch();
            if ($habitacion === false) {
                throw new ReglaNegocioException('La habitación no existe.');
            }
            if ($habitacion['estado'] !== EstadoHabitacion::LIBRE) {
                throw new ReglaNegocioException('La habitación no está disponible para check-in.');
            }

            $consultaLlave = $conexion->prepare(
                'SELECT id FROM llave WHERE uid = ? AND estado = ? LIMIT 1'
            );
            $consultaLlave->execute([$uid, EstadoLlave::ACTIVA]);
            if ($consultaLlave->fetch() !== false) {
                throw new ReglaNegocioException('La tarjeta ya tiene una llave activa.');
            }

            $actualizarReserva = $conexion->prepare('UPDATE reserva SET estado = ? WHERE id = ?');
            $actualizarReserva->execute([EstadoReserva::ACTIVA, $reservaId]);

            $crearLlave = $conexion->prepare(
                'INSERT INTO llave (reserva_id, uid, valida_desde, valida_hasta, estado)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $crearLlave->execute([
                $reservaId,
                $uid,
                $fila['fecha_entrada'],
                $fila['fecha_salida'],
                EstadoLlave::ACTIVA,
            ]);

            $actualizarHabitacion = $conexion->prepare(
                'UPDATE habitacion SET estado = ? WHERE id = ?'
            );
            $actualizarHabitacion->execute([EstadoHabitacion::OCUPADA, $habitacionId]);

            $crearEvento = $conexion->prepare(
                'INSERT INTO evento (fecha_hora, tipo, habitacion_id, uid, resultado, detalle)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $ahoraTexto = $ahora->format('Y-m-d H:i:s');
            $crearEvento->execute([
                $ahoraTexto,
                TipoEvento::CHECK_IN,
                $habitacionId,
                $uid,
                'EXITO',
                'Check-in registrado para la reserva #' . $reservaId,
            ]);
            $crearEvento->execute([
                $ahoraTexto,
                TipoEvento::LLAVE_EMITIDA,
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
            if ($error instanceof ReglaNegocioException) {
                throw $error;
            }
            throw new RuntimeException('No se pudo completar el check-in. Los cambios fueron revertidos.');
        }
    }

    public function checkOut(int $reservaId): void
    {
        $conexion = Database::conectar();
        try {
            $conexion->beginTransaction();

            $consulta = $conexion->prepare(
                'SELECT id, habitacion_id, estado FROM reserva WHERE id = ? FOR UPDATE'
            );
            $consulta->execute([$reservaId]);
            $fila = $consulta->fetch();
            if ($fila === false) {
                throw new ReglaNegocioException('La reserva no existe.');
            }
            if ($fila['estado'] !== EstadoReserva::ACTIVA) {
                throw new ReglaNegocioException('Solo se puede hacer check-out de una reserva activa.');
            }

            $habitacionId = (int) $fila['habitacion_id'];
            $consultaHabitacion = $conexion->prepare(
                'SELECT id, estado FROM habitacion WHERE id = ? FOR UPDATE'
            );
            $consultaHabitacion->execute([$habitacionId]);
            $habitacion = $consultaHabitacion->fetch();
            if ($habitacion === false) {
                throw new ReglaNegocioException('La habitación no existe.');
            }

            $actualizarReserva = $conexion->prepare('UPDATE reserva SET estado = ? WHERE id = ?');
            $actualizarReserva->execute([EstadoReserva::FINALIZADA, $reservaId]);

            $revocarLlaves = $conexion->prepare(
                'UPDATE llave SET estado = ? WHERE reserva_id = ? AND estado = ?'
            );
            $revocarLlaves->execute([
                EstadoLlave::REVOCADA,
                $reservaId,
                EstadoLlave::ACTIVA,
            ]);

            $actualizarHabitacion = $conexion->prepare(
                'UPDATE habitacion SET estado = ? WHERE id = ?'
            );
            $actualizarHabitacion->execute([EstadoHabitacion::POR_LIMPIAR, $habitacionId]);

            $crearEvento = $conexion->prepare(
                'INSERT INTO evento (fecha_hora, tipo, habitacion_id, uid, resultado, detalle)
                 VALUES (?, ?, ?, NULL, ?, ?)'
            );
            $ahora = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $crearEvento->execute([
                $ahora,
                TipoEvento::CHECK_OUT,
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
            if ($error instanceof ReglaNegocioException) {
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
            // La bitácora no debe reemplazar el error de negocio original.
        }
    }
}
