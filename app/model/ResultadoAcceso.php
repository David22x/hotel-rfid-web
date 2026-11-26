<?php

// Respuesta de la cerradura al pasar una tarjeta. Solo CONCEDIDO abre la puerta.
final class ResultadoAcceso
{
    public const CONCEDIDO = 'CONCEDIDO';
    public const UID_NO_REGISTRADO = 'UID_NO_REGISTRADO';
    public const LLAVE_INACTIVA = 'LLAVE_INACTIVA';
    public const FUERA_DE_HORARIO = 'FUERA_DE_HORARIO';
    public const HABITACION_INCORRECTA = 'HABITACION_INCORRECTA';
    public const RESERVA_NO_ACTIVA = 'RESERVA_NO_ACTIVA';

    // Mensaje que ve el usuario para cada resultado
    public const MENSAJES = [
        self::CONCEDIDO => 'Acceso concedido',
        self::UID_NO_REGISTRADO => 'La tarjeta no está registrada',
        self::LLAVE_INACTIVA => 'La llave está revocada o vencida',
        self::FUERA_DE_HORARIO => 'La llave no es válida en este horario',
        self::HABITACION_INCORRECTA => 'La llave pertenece a otra habitación',
        self::RESERVA_NO_ACTIVA => 'La reserva no tiene check-in activo',
    ];
}
