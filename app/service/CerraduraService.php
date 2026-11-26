<?php

require_once __DIR__ . '/../dao/LlaveDAO.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../dao/EventoDAO.php';
require_once __DIR__ . '/../model/ResultadoAcceso.php';
require_once __DIR__ . '/../model/TipoEvento.php';
require_once __DIR__ . '/../model/EstadoLlave.php';
require_once __DIR__ . '/../model/EstadoReserva.php';

// Decide si una tarjeta abre una habitación. Las 5 reglas se revisan en orden:
// 1. El UID debe estar registrado como llave.
// 2. La llave debe estar ACTIVA (no revocada ni vencida).
// 3. La hora actual debe estar dentro de la vigencia de la llave.
// 4. La reserva de la llave debe ser de la habitación donde se pasa la tarjeta.
// 5. La reserva debe estar ACTIVA, es decir, con check-in hecho.
// Cada intento queda registrado en la bitácora.
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

    // Devuelve un arreglo con: concedido (true o false), resultado, mensaje y respuesta ('OK' o 'NO')
    public function validar(string $uid, int $habitacionId): array
    {
        $uid = strtoupper(trim($uid));
        $ahora = date('Y-m-d H:i:s');

        // Mismo formato que acepta el check-in: de 4 a 20 caracteres hexadecimales
        if (!preg_match('/^[0-9A-F]{4,20}$/', $uid)) {
            throw new ReglaNegocioException('Escriba un UID hexadecimal válido, por ejemplo A3F1C20B.');
        }
        if ($this->habitacionDao->buscarPorId($habitacionId) === null) {
            throw new ReglaNegocioException('Seleccione una habitación válida.');
        }

        $resultado = $this->aplicarReglas($uid, $habitacionId, $ahora);
        $concedido = $resultado === ResultadoAcceso::CONCEDIDO;
        $mensaje = ResultadoAcceso::MENSAJES[$resultado];

        $tipo = $concedido ? TipoEvento::ACCESO_CONCEDIDO : TipoEvento::ACCESO_DENEGADO;
        $this->eventoDao->registrar($tipo, $habitacionId, $uid, $resultado, $mensaje);

        return [
            'concedido' => $concedido,
            'resultado' => $resultado,
            'mensaje' => $mensaje,
            // Texto que se envía al Arduino para encender el LED verde o rojo
            'respuesta' => $concedido ? 'OK' : 'NO',
        ];
    }

    private function aplicarReglas(string $uid, int $habitacionId, string $ahora): string
    {
        // Regla 1: la tarjeta debe estar registrada
        $llave = $this->llaveDao->buscarPorUid($uid);
        if ($llave === null) {
            return ResultadoAcceso::UID_NO_REGISTRADO;
        }

        // Regla 2: la llave debe estar activa
        if ($llave->estado !== EstadoLlave::ACTIVA) {
            return ResultadoAcceso::LLAVE_INACTIVA;
        }

        // Regla 3: dentro del horario de vigencia (desde incluido, hasta excluido)
        $momento = strtotime($ahora);
        if ($momento < strtotime($llave->validaDesde) || $momento >= strtotime($llave->validaHasta)) {
            return ResultadoAcceso::FUERA_DE_HORARIO;
        }

        // Regla 4: la reserva debe ser de esta habitación
        $reserva = $this->reservaDao->buscarPorId($llave->reservaId);
        if ($reserva === null || $reserva->habitacionId !== $habitacionId) {
            return ResultadoAcceso::HABITACION_INCORRECTA;
        }

        // Regla 5: la reserva debe tener check-in activo
        if ($reserva->estado !== EstadoReserva::ACTIVA) {
            return ResultadoAcceso::RESERVA_NO_ACTIVA;
        }

        return ResultadoAcceso::CONCEDIDO;
    }
}
