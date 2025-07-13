<?php
// Archivo: model/compra/CompraDAO.php

require_once 'CompraDTO.php';
require_once 'CompraMapper.php';

class CompraDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($compra)
{
    try {
        // 1. Llamar al SP que hace todo (INSERT + UPDATE)
        $stmt = $this->conn->prepare("CALL SumarCompra(?, ?)");
        return $stmt->execute([
            $compra->idCliente,
            $compra->total
        ]);

    } catch (PDOException $e) {
        error_log("Error al registrar compra: " . $e->getMessage());
        return false;
    }
}


    public function read($idCompra)
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraRead(?)");
            $stmt->execute([$idCompra]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? CompraMapper::mapRowToDTO($row) : null;
        } catch (PDOException $e) {
            error_log("Error al leer compra: " . $e->getMessage());
            return null;
        }
    }

    public function delete($idCompra)
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraDelete(?)");
            return $stmt->execute([$idCompra]);
        } catch (PDOException $e) {
            error_log("Error al eliminar compra: " . $e->getMessage());
            return false;
        }
    }

    public function readAll()
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraReadAll()");
            $stmt->execute();
            $compras = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $compras[] = CompraMapper::mapRowToDTO($row);
            }
            return $compras;
        } catch (PDOException $e) {
            error_log("Error al leer todas las compras: " . $e->getMessage());
            return [];
        }
    }

    /** NUEVO: Obtiene todas las compras de un cliente específico usando el SP CompraReadByCliente */
    public function readByCliente($idCliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraReadByCliente(?)");
            $stmt->execute([$idCliente]);
            $compras = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $compras[] = CompraMapper::mapRowToDTO($row);
            }
            return $compras;
        } catch (PDOException $e) {
            error_log("Error al leer compras por cliente: " . $e->getMessage());
            return [];
        }
    }
}
