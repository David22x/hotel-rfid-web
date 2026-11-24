<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../model/Evento.php';

class EventoDAO
{
    public function crear(Evento $evento): int
    {
        $conexion = Database::conectar();
        $consulta = $conexion->prepare(
            'INSERT INTO evento (fecha_hora, tipo, habitacion_id, uid, resultado, detalle)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $consulta->execute([
            $evento->fechaHora,
            $evento->tipo,
            $evento->habitacionId,
            $evento->uid,
            $evento->resultado,
            $evento->detalle,
        ]);
        return (int) $conexion->lastInsertId();
    }

    public function listar(): array
    {
        $consulta = Database::conectar()->prepare(
            'SELECT e.id, e.fecha_hora, e.tipo, e.habitacion_id, e.uid, e.resultado, e.detalle,
                    h.numero AS habitacion_numero
             FROM evento e
             LEFT JOIN habitacion h ON h.id = e.habitacion_id
             ORDER BY e.fecha_hora DESC, e.id DESC
             LIMIT 300'
        );
        $consulta->execute();
        $filas = $consulta->fetchAll();
        $eventos = [];
        foreach ($filas as $fila) {
            $eventos[] = [
                'id' => (int) $fila['id'],
                'fechaHora' => $fila['fecha_hora'],
                'tipo' => $fila['tipo'],
                'habitacionId' => $fila['habitacion_id'] === null ? null : (int) $fila['habitacion_id'],
                'habitacionNumero' => $fila['habitacion_numero'],
                'uid' => $fila['uid'],
                'resultado' => $fila['resultado'],
                'detalle' => $fila['detalle'],
            ];
        }
        return $eventos;
    }
}
