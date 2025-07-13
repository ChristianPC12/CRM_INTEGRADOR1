<?php
require_once __DIR__ . '/../model/Bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/Bitacora/BitacoraMapper.php';

class BitacoraController {
    private $dao;

    public function __construct() {
        $this->dao = new BitacoraDAO();
    }

    public function readAll() {
        $this->dao->limpiarAntiguos();
        $result = $this->dao->getAll();
        return BitacoraMapper::mapAll($result);
    }

    public function read($id): ?BitacoraDTO {
        $result = $this->dao->getById($id);
        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return BitacoraMapper::map($row);
            }
        }
        return null;
    }

    public function delete($id): bool {
        return $this->dao->delete($id);
    }

    public function mostrarAvisoExpiracion(): bool {
        return $this->dao->hayPorExpirar();
    }

  public function registrarSalida($idUsuario): bool {
    return $this->dao->registrarSalida($idUsuario);
}
}
