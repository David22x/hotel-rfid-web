-- Selecciona la base hotel_rfid (o la asignada por el hosting) antes de importar.

CREATE TABLE IF NOT EXISTS usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('ADMIN', 'RECEPCIONISTA') NOT NULL,
    activo TINYINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT chk_usuario_activo CHECK (activo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS habitacion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL UNIQUE,
    piso SMALLINT UNSIGNED NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    estado ENUM('LIBRE', 'OCUPADA', 'POR_LIMPIAR', 'MANTENIMIENTO') NOT NULL DEFAULT 'LIBRE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS huesped (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    documento VARCHAR(30) NOT NULL UNIQUE,
    correo VARCHAR(254) NOT NULL,
    telefono VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reserva (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    huesped_id INT UNSIGNED NOT NULL,
    habitacion_id INT UNSIGNED NOT NULL,
    fecha_entrada DATETIME NOT NULL,
    fecha_salida DATETIME NOT NULL,
    estado ENUM('PENDIENTE', 'ACTIVA', 'FINALIZADA', 'CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
    INDEX idx_reserva_cruce (habitacion_id, estado, fecha_entrada),
    CONSTRAINT chk_reserva_fechas CHECK (fecha_entrada < fecha_salida),
    CONSTRAINT fk_reserva_huesped FOREIGN KEY (huesped_id) REFERENCES huesped (id),
    CONSTRAINT fk_reserva_habitacion FOREIGN KEY (habitacion_id) REFERENCES habitacion (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS llave (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT UNSIGNED NOT NULL,
    uid VARCHAR(20) NOT NULL,
    valida_desde DATETIME NOT NULL,
    valida_hasta DATETIME NOT NULL,
    estado ENUM('ACTIVA', 'VENCIDA', 'REVOCADA') NOT NULL DEFAULT 'ACTIVA',
    -- El UID puede repetirse en el historial de llaves.
    INDEX idx_llave_uid_estado (uid, estado),
    INDEX idx_llave_expiracion (estado, valida_hasta),
    CONSTRAINT chk_llave_vigencia CHECK (valida_desde < valida_hasta),
    CONSTRAINT fk_llave_reserva FOREIGN KEY (reserva_id) REFERENCES reserva (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM(
        'ACCESO_CONCEDIDO', 'ACCESO_DENEGADO', 'CHECK_IN', 'CHECK_OUT',
        'LLAVE_EMITIDA', 'LLAVE_REVOCADA', 'LLAVE_VENCIDA', 'INICIO_SESION'
    ) NOT NULL,
    habitacion_id INT UNSIGNED NULL,
    uid VARCHAR(20) NULL,
    resultado VARCHAR(30) NULL,
    detalle VARCHAR(255) NULL,
    INDEX idx_evento_fecha (fecha_hora),
    INDEX idx_evento_habitacion_fecha (habitacion_id, fecha_hora),
    CONSTRAINT fk_evento_habitacion FOREIGN KEY (habitacion_id)
        REFERENCES habitacion (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
