<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../model/Evento.php';

class EventoDAO
{
    public function crear(Evento $evento): int
    {
        $conexion = conectar();
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
        return ultimoId($conexion, 'evento');
    }

    public function registrar(
        string $tipo,
        ?int $habitacionId,
        ?string $uid,
        ?string $resultado,
        ?string $detalle,
    ): int {
        return $this->crear(new Evento(date('Y-m-d H:i:s'), $tipo, $habitacionId, $uid, $resultado, $detalle));
    }

    // Registra un intento fallido sin reemplazar el error original
    public function registrarFallo(
        string $tipo,
        ?int $habitacionId,
        ?string $uid,
        string $resultado,
        string $detalle
    ): void {
        try {
            $detalle = function_exists('mb_substr') ? mb_substr($detalle, 0, 255) : substr($detalle, 0, 255);
            $this->registrar($tipo, $habitacionId, $uid, $resultado, $detalle);
        } catch (Throwable $ignorado) {
        }
    }

    public function listar(?string $desde = null, ?string $hasta = null, ?int $habitacionId = null): array
    {
        $sql = 'SELECT e.id, e.fecha_hora, e.tipo, e.habitacion_id, e.uid, e.resultado, e.detalle,
                    h.numero AS habitacion_numero
             FROM evento e
             LEFT JOIN habitacion h ON h.id = e.habitacion_id';
        $filtros = [];
        $parametros = [];

        if ($desde !== null) {
            $filtros[] = 'e.fecha_hora >= ?';
            $parametros[] = $desde;
        }
        if ($hasta !== null) {
            $filtros[] = 'e.fecha_hora <= ?';
            $parametros[] = $hasta;
        }
        if ($habitacionId !== null) {
            $filtros[] = 'e.habitacion_id = ?';
            $parametros[] = $habitacionId;
        }
        if ($filtros !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $filtros);
        }
        $sql .= ' ORDER BY e.fecha_hora DESC, e.id DESC LIMIT 300';

        $consulta = conectar()->prepare($sql);
        $consulta->execute($parametros);
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
