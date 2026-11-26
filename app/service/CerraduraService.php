<?php

require_once __DIR__ . '/../dao/LlaveDAO.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../dao/EventoDAO.php';

// Reglas de acceso a una habitación con tarjeta
class CerraduraService
{
    private LlaveDAO $llaveDao;
    private ReservaDAO $reservaDao;
    private HabitacionDAO $habitacionDao;
    private EventoDAO $eventoDao;

    public function __construct()
    {
        $this->llaveDao = new LlaveDAO();
        $this->reservaDao = new ReservaDAO();
        $this->habitacionDao = new HabitacionDAO();
        $this->eventoDao = new EventoDAO();
    }

    public function validar(string $uid, int $habitacionId): array
    {
        $uid = strtoupper(trim($uid));
        $ahora = date('Y-m-d H:i:s');

        if (!preg_match('/^[0-9A-F]{4,20}$/', $uid)) {
            throw new Exception('Escriba un UID hexadecimal válido, por ejemplo A3F1C20B.');
        }
        if ($this->habitacionDao->buscarPorId($habitacionId) === null) {
            throw new Exception('Seleccione una habitación válida.');
        }

        $resultado = $this->aplicarReglas($uid, $habitacionId, $ahora);
        $concedido = $resultado === 'CONCEDIDO';
        $mensajes = [
            'CONCEDIDO' => 'Acceso concedido',
            'UID_NO_REGISTRADO' => 'La tarjeta no está registrada',
            'LLAVE_INACTIVA' => 'La llave está revocada o vencida',
            'FUERA_DE_HORARIO' => 'La llave no es válida en este horario',
            'HABITACION_INCORRECTA' => 'La llave pertenece a otra habitación',
            'RESERVA_NO_ACTIVA' => 'La reserva no tiene check-in activo',
        ];
        $mensaje = $mensajes[$resultado];

        $tipo = $concedido ? 'ACCESO_CONCEDIDO' : 'ACCESO_DENEGADO';
        $this->eventoDao->registrar($tipo, $habitacionId, $uid, $resultado, $mensaje);

        return [
            'concedido' => $concedido,
            'resultado' => $resultado,
            'mensaje' => $mensaje,
            'respuesta' => $concedido ? 'OK' : 'NO',
        ];
    }

    private function aplicarReglas(string $uid, int $habitacionId, string $ahora): string
    {
        $llave = $this->llaveDao->buscarPorUid($uid);
        if ($llave === null) {
            return 'UID_NO_REGISTRADO';
        }

        if ($llave->estado !== 'ACTIVA') {
            return 'LLAVE_INACTIVA';
        }

        $momento = strtotime($ahora);
        if ($momento < strtotime($llave->validaDesde) || $momento >= strtotime($llave->validaHasta)) {
            return 'FUERA_DE_HORARIO';
        }

        $reserva = $this->reservaDao->buscarPorId($llave->reservaId);
        if ($reserva === null || $reserva->habitacionId !== $habitacionId) {
            return 'HABITACION_INCORRECTA';
        }

        if ($reserva->estado !== 'ACTIVA') {
            return 'RESERVA_NO_ACTIVA';
        }

        return 'CONCEDIDO';
    }
}
