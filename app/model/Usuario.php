<?php

class Usuario
{
    public function __construct(
        public string $usuario,
        public string $passwordHash,
        public string $rol,
        public bool $activo = true,
        public ?int $id = null,
    ) {
    }
}
