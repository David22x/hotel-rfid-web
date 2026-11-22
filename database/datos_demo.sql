-- Importar una sola vez en una base local de pruebas, después de schema.sql.
-- Cuentas de prueba: admin / AdminDemo2026!, recepcion / RecepcionDemo2026!
-- Las fechas se calculan con la hora de Ecuador al momento de importar.
SET @ahora_demo = UTC_TIMESTAMP() - INTERVAL 5 HOUR;

START TRANSACTION;

INSERT INTO usuario (usuario, password_hash, rol, activo) VALUES
    ('admin', '$2y$10$cH0LBDFey5CckNaXr0nkpORr/snCsx9qpBhcLKoNa1rIkZnhFT9zS', 'ADMIN', 1),
    ('recepcion', '$2y$10$ARKZ3HF5Jx1h22dwm2KVv.O.zJrKdF7lnCOnXgfowcU2IkFV1tTJW', 'RECEPCIONISTA', 1);

INSERT INTO habitacion (numero, piso, tipo, estado) VALUES
    ('101', 1, 'Simple', 'LIBRE'),
    ('102', 1, 'Doble', 'LIBRE'),
    ('201', 2, 'Suite', 'MANTENIMIENTO');

INSERT INTO huesped (nombre, documento, correo, telefono) VALUES
    ('Ana Torres', '0000000001', 'ana.torres@example.com', '0900000001'),
    ('Luis Mora', '0000000002', 'luis.mora@example.com', '0900000002');

-- Habitación 101: estadía dentro de su vigencia para ensayar el check-in.
-- Habitación 102: estadía futura para probar consultas y cruces de reservas.
INSERT INTO reserva (huesped_id, habitacion_id, fecha_entrada, fecha_salida, estado) VALUES
    (
        (SELECT id FROM huesped WHERE documento = '0000000001'),
        (SELECT id FROM habitacion WHERE numero = '101'),
        @ahora_demo - INTERVAL 1 HOUR,
        @ahora_demo + INTERVAL 1 DAY,
        'PENDIENTE'
    ),
    (
        (SELECT id FROM huesped WHERE documento = '0000000002'),
        (SELECT id FROM habitacion WHERE numero = '102'),
        @ahora_demo + INTERVAL 2 DAY,
        @ahora_demo + INTERVAL 3 DAY,
        'PENDIENTE'
    );

COMMIT;
