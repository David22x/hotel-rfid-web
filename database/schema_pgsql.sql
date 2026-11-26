-- Esquema para PostgreSQL (Azure Database for PostgreSQL).
-- Equivalente a schema.sql (MySQL). Los ENUM se modelan con CHECK para
-- conservar los mismos valores como texto.

CREATE TABLE IF NOT EXISTS usuario (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL,
    activo SMALLINT NOT NULL DEFAULT 1,
    CONSTRAINT chk_usuario_rol CHECK (rol IN ('ADMIN', 'RECEPCIONISTA')),
    CONSTRAINT chk_usuario_activo CHECK (activo IN (0, 1))
);

CREATE TABLE IF NOT EXISTS habitacion (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(20) NOT NULL UNIQUE,
    piso SMALLINT NOT NULL CHECK (piso >= 0),
    tipo VARCHAR(50) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'LIBRE',
    CONSTRAINT chk_habitacion_estado CHECK (estado IN ('LIBRE', 'OCUPADA', 'POR_LIMPIAR', 'MANTENIMIENTO'))
);

CREATE TABLE IF NOT EXISTS huesped (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    documento VARCHAR(30) NOT NULL UNIQUE,
    correo VARCHAR(254) NOT NULL,
    telefono VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS reserva (
    id SERIAL PRIMARY KEY,
    huesped_id INTEGER NOT NULL,
    habitacion_id INTEGER NOT NULL,
    fecha_entrada TIMESTAMP NOT NULL,
    fecha_salida TIMESTAMP NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
    CONSTRAINT chk_reserva_estado CHECK (estado IN ('PENDIENTE', 'ACTIVA', 'FINALIZADA', 'CANCELADA')),
    CONSTRAINT chk_reserva_fechas CHECK (fecha_entrada < fecha_salida),
    CONSTRAINT fk_reserva_huesped FOREIGN KEY (huesped_id) REFERENCES huesped (id),
    CONSTRAINT fk_reserva_habitacion FOREIGN KEY (habitacion_id) REFERENCES habitacion (id)
);
CREATE INDEX IF NOT EXISTS idx_reserva_cruce ON reserva (habitacion_id, estado, fecha_entrada);

CREATE TABLE IF NOT EXISTS llave (
    id SERIAL PRIMARY KEY,
    reserva_id INTEGER NOT NULL,
    uid VARCHAR(20) NOT NULL,
    valida_desde TIMESTAMP NOT NULL,
    valida_hasta TIMESTAMP NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVA',
    CONSTRAINT chk_llave_estado CHECK (estado IN ('ACTIVA', 'VENCIDA', 'REVOCADA')),
    CONSTRAINT chk_llave_vigencia CHECK (valida_desde < valida_hasta),
    CONSTRAINT fk_llave_reserva FOREIGN KEY (reserva_id) REFERENCES reserva (id)
);
-- El UID puede repetirse en el historial de llaves.
CREATE INDEX IF NOT EXISTS idx_llave_uid_estado ON llave (uid, estado);
CREATE INDEX IF NOT EXISTS idx_llave_expiracion ON llave (estado, valida_hasta);

CREATE TABLE IF NOT EXISTS evento (
    id SERIAL PRIMARY KEY,
    fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo VARCHAR(30) NOT NULL,
    habitacion_id INTEGER NULL,
    uid VARCHAR(20) NULL,
    resultado VARCHAR(30) NULL,
    detalle VARCHAR(255) NULL,
    CONSTRAINT chk_evento_tipo CHECK (tipo IN (
        'ACCESO_CONCEDIDO', 'ACCESO_DENEGADO', 'CHECK_IN', 'CHECK_OUT',
        'LLAVE_EMITIDA', 'LLAVE_REVOCADA', 'LLAVE_VENCIDA', 'INICIO_SESION'
    )),
    CONSTRAINT fk_evento_habitacion FOREIGN KEY (habitacion_id)
        REFERENCES habitacion (id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_evento_fecha ON evento (fecha_hora);
CREATE INDEX IF NOT EXISTS idx_evento_habitacion_fecha ON evento (habitacion_id, fecha_hora);

-- Lecturas RFID enviadas por el lector de recepción; el formulario de
-- check-in las recoge una sola vez (consumida = 1) y caducan a los 60 s.
CREATE TABLE IF NOT EXISTS lectura_rfid (
    id SERIAL PRIMARY KEY,
    uid VARCHAR(20) NOT NULL,
    fecha_hora TIMESTAMP NOT NULL,
    consumida SMALLINT NOT NULL DEFAULT 0,
    CONSTRAINT chk_lectura_consumida CHECK (consumida IN (0, 1))
);
CREATE INDEX IF NOT EXISTS idx_lectura_pendiente ON lectura_rfid (consumida, fecha_hora);
