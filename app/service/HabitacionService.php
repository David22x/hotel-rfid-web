<?php

require_once __DIR__ . '/../dao/HabitacionDAO.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../model/Habitacion.php';

class HabitacionService
{
    public array $tipos = ['Simple', 'Doble', 'Suite', 'Familiar'];
    public array $estados = ['LIBRE', 'OCUPADA', 'POR_LIMPIAR', 'MANTENIMIENTO'];

    private HabitacionDAO $dao;
    private ReservaDAO $reservaDao;

    public function __construct()
    {
        $this->dao = new HabitacionDAO();
        $this->reservaDao = new ReservaDAO();
    }

    public function crear(Habitacion $h): Habitacion
    {
        $this->validarFormato($h);

        if ($this->dao->buscarPorNumero($h->numero) !== null) {
            throw new Exception('Ya existe una habitación con el número ' . $h->numero);
        }

        $h->estado = 'LIBRE';

        $idGenerado = $this->dao->crear($h);
        if ($idGenerado == 0) {
            throw new Exception('No se pudo guardar la habitación');
        }
        $h->id = $idGenerado;
        return $h;
    }

    public function listar(): array
    {
        return $this->dao->listar();
    }

    public function buscarPorId(int $id): ?Habitacion
    {
        return $this->dao->buscarPorId($id);
    }

    public function actualizar(Habitacion $h): void
    {
        $this->validarFormato($h);

        $actual = $this->dao->buscarPorId($h->id);
        if ($actual === null) {
            throw new Exception('No existe la habitación con id ' . $h->id);
        }

        if ($actual->numero !== $h->numero && $this->dao->buscarPorNumero($h->numero) !== null) {
            throw new Exception('Ya existe otra habitación con el número ' . $h->numero);
        }

        $this->dao->actualizar($h);
    }

    public function eliminar(int $id): void
    {
        $h = $this->dao->buscarPorId($id);
        if ($h === null) {
            throw new Exception('No existe la habitación con id ' . $id);
        }

        if ($h->estado === 'OCUPADA') {
            throw new Exception('No se puede eliminar una habitación ocupada');
        }

        if ($this->reservaDao->existenPorHabitacion($id)) {
            throw new Exception('No se puede eliminar la habitación porque tiene reservas registradas');
        }

        $this->dao->eliminar($id);
    }

    public function cambiarEstado(int $id, string $nuevo): void
    {
        $h = $this->dao->buscarPorId($id);
        if ($h === null) {
            throw new Exception('No existe la habitación con id ' . $id);
        }

        $this->validarEstado($nuevo);
        $this->validarTransicion($h->estado, $nuevo);

        $this->dao->actualizarEstado($id, $nuevo);
    }

    private function validarFormato(Habitacion $h): void
    {
        if (trim($h->numero) === '') {
            throw new Exception('El número de habitación es obligatorio');
        }
        if ($h->piso < 0) {
            throw new Exception('El piso no puede ser negativo');
        }
        if (trim($h->tipo) === '') {
            throw new Exception('El tipo de habitación es obligatorio');
        }
        $this->validarEstado($h->estado);
    }

    private function validarEstado(string $estado): void
    {
        if (!in_array($estado, $this->estados)) {
            throw new Exception('El estado ' . $estado . ' no es válido');
        }
    }

    private function validarTransicion(string $actual, string $nuevo): void
    {
        if ($actual === $nuevo) {
            return;
        }

        if ($actual === 'OCUPADA' && $nuevo !== 'POR_LIMPIAR') {
            throw new Exception('Una habitación ocupada solo puede pasar a POR_LIMPIAR');
        }

        if ($actual === 'MANTENIMIENTO' && $nuevo === 'OCUPADA') {
            throw new Exception('Una habitación en mantenimiento no puede ocuparse');
        }
    }
}
