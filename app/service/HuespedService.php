<?php

require_once __DIR__ . '/../dao/HuespedDAO.php';
require_once __DIR__ . '/../dao/ReservaDAO.php';
require_once __DIR__ . '/../model/Huesped.php';

class HuespedService
{
    private HuespedDAO $dao;
    private ReservaDAO $reservaDao;

    public function __construct()
    {
        $this->dao = new HuespedDAO();
        $this->reservaDao = new ReservaDAO();
    }

    public function registrar(Huesped $h): Huesped
    {
        $this->validar($h);

        if ($this->existeDocumento($h->documento, null)) {
            throw new Exception('Ya existe un huésped con el documento ' . $h->documento);
        }

        $id = $this->dao->crear($h);
        if ($id == 0) {
            throw new Exception('No se pudo registrar el huésped');
        }
        $h->id = $id;
        return $h;
    }

    public function actualizar(Huesped $h): void
    {
        $this->validar($h);

        if ($this->dao->buscarPorId($h->id) === null) {
            throw new Exception('No existe el huésped con id ' . $h->id);
        }

        if ($this->existeDocumento($h->documento, $h->id)) {
            throw new Exception('Ya existe otro huésped con el documento ' . $h->documento);
        }

        $this->dao->actualizar($h);
    }

    public function eliminar(int $id): void
    {
        if ($this->dao->buscarPorId($id) === null) {
            throw new Exception('No existe el huésped con id ' . $id);
        }

        if ($this->reservaDao->existenPorHuesped($id)) {
            throw new Exception('No se puede eliminar el huésped porque tiene reservas registradas');
        }

        $this->dao->eliminar($id);
    }

    public function buscarPorId(int $id): ?Huesped
    {
        return $this->dao->buscarPorId($id);
    }

    public function listar(): array
    {
        return $this->dao->listar();
    }

    public function buscar(string $termino): array
    {
        $termino = trim($termino);
        if ($termino === '') {
            return $this->dao->listar();
        }
        return $this->dao->buscar($termino);
    }

    private function existeDocumento(string $documento, ?int $idActual): bool
    {
        $otro = $this->dao->buscarPorDocumento($documento);
        return $otro !== null && $otro->id !== $idActual;
    }

    private function validar(Huesped $h): void
    {
        if (trim($h->nombre) === '') {
            throw new Exception('El nombre es obligatorio');
        }

        if (trim($h->documento) === '') {
            throw new Exception('El documento es obligatorio');
        }

        if ($h->correo !== '' && !preg_match('/^[\w._%+-]+@[\w.-]+\.[A-Za-z]{2,}$/', $h->correo)) {
            throw new Exception('El correo no tiene un formato válido');
        }

        if ($h->telefono !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $h->telefono)) {
            throw new Exception('El teléfono no tiene un formato válido');
        }
    }
}
