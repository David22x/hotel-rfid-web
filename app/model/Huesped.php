<?php

class Huesped
{
    public function __construct(
        public string $nombre,
        public string $documento,
        public string $correo,
        public string $telefono,
        public ?int $id = null,
    ) {
    }
}
