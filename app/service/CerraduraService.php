<?php

require_once __DIR__ . '/../dao/LlaveDAO.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/EventoDAO.php';
require_once __DIR__ . '/../model/ResultadoAcceso.php';
require_once __DIR__ . '/../model/EstadoLlave.php';
require_once __DIR__ . '/../model/EstadoReserva.php';
require_once __DIR__ . '/../model/TipoEvento.php';

/**
 * Decide si una tarjeta abre una habitación (mismas 5 reglas que la versión Java):
 * 1. El UID debe estar registrado como llave.
 * 2. La llave debe estar ACTIVA.
 * 3. La hora actual debe estar dentro de la vigencia de la llave.
 * 4. La reserva de la llave debe ser de la habitación donde se pasa la tarjeta.
 * 5. La reserva debe estar ACTIVA (check-in hecho).
 * Cada intento queda registrado en la bitácora.
 */
class CerraduraService
{
    private LlaveDAO $llaves;
    private ReservaDAO $reservas;
    private EventoDAO $eventos;

    public function __construct()
    {
        $this->llaves = new LlaveDAO();
        $this->reservas = new ReservaDAO();
        $this->eventos = new EventoDAO();
    }

    public function validar(string $uid, int $habitacionId, ?DateTimeImmutable $ahora = null): string
    {
        $uid = strtoupper(trim($uid));
        $resultado = $this->aplicarReglas($uid, $habitacionId, $ahora ?? new DateTimeImmutable());

        $this->eventos->registrar(
            $resultado === ResultadoAcceso::CONCEDIDO ? TipoEvento::ACCESO_CONCEDIDO : TipoEvento::ACCESO_DENEGADO,
            $habitacionId,
            $uid,
            $resultado,
            ResultadoAcceso::mensaje($resultado)
        );

        return $resultado;
    }

    private function aplicarReglas(string $uid, int $habitacionId, DateTimeImmutable $ahora): string
    {
        $llave = $uid === '' ? null : $this->llaves->buscarPorUid($uid);
        if ($llave === null) {
            return ResultadoAcceso::UID_NO_REGISTRADO;
        }
        if ($llave->estado !== EstadoLlave::ACTIVA) {
            return ResultadoAcceso::LLAVE_INACTIVA;
        }
        if ($ahora < new DateTimeImmutable($llave->validaDesde) || $ahora >= new DateTimeImmutable($llave->validaHasta)) {
            return ResultadoAcceso::FUERA_DE_HORARIO;
        }
        $reserva = $this->reservas->buscarPorId($llave->reservaId);
        if ($reserva === null || $reserva->habitacionId !== $habitacionId) {
            return ResultadoAcceso::HABITACION_INCORRECTA;
        }
        if ($reserva->estado !== EstadoReserva::ACTIVA) {
            return ResultadoAcceso::RESERVA_NO_ACTIVA;
        }
        return ResultadoAcceso::CONCEDIDO;
    }
}
