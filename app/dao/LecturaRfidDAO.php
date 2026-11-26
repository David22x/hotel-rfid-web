<?php

require_once __DIR__ . '/../core/Database.php';

class LecturaRfidDAO
{
    public function guardar(string $uid, string $ahora): void
    {
        $conexion = Database::conectar();
        // Limpieza oportunista: las lecturas viejas ya no sirven.
        $limpiar = $conexion->prepare('DELETE FROM lectura_rfid WHERE fecha_hora < ?');
        $limpiar->execute([(new DateTimeImmutable($ahora))->modify('-1 day')->format('Y-m-d H:i:s')]);

        $consulta = $conexion->prepare('INSERT INTO lectura_rfid (uid, fecha_hora, consumida) VALUES (?, ?, 0)');
        $consulta->execute([$uid, $ahora]);
    }

    /** Entrega la lectura pendiente más reciente (no caducada) y la marca como consumida. */
    public function tomarPendiente(string $desde): ?string
    {
        $conexion = Database::conectar();
        $consulta = $conexion->prepare(
            'SELECT id, uid FROM lectura_rfid WHERE consumida = 0 AND fecha_hora >= ? ORDER BY id DESC LIMIT 1'
        );
        $consulta->execute([$desde]);
        $fila = $consulta->fetch();
        if ($fila === false) {
            return null;
        }
        // Las pendientes más viejas quedan descartadas junto con la tomada.
        $marcar = $conexion->prepare('UPDATE lectura_rfid SET consumida = 1 WHERE consumida = 0 AND id <= ?');
        $marcar->execute([(int) $fila['id']]);
        return $marcar->rowCount() > 0 ? $fila['uid'] : null;
    }
}
