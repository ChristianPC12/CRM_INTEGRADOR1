<?php
// Archivo: model/codigo/CodigoDAO.php

require_once 'CodigoDTO.php';
require_once 'CodigoMapper.php';

class CodigoDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($codigo)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoCreate(?, ?)");
            return $stmt->execute([
                $codigo->idCliente,
                $codigo->fechaRegistro
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error PDO: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function read($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoRead(?)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? CodigoMapper::mapRowToDTO($row) : null;
        } catch (PDOException $e) {
            error_log("Error al leer codigo: " . $e->getMessage());
            return null;
        }
    }

    public function readAll()
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoReadAll()");
            $stmt->execute();
            $codigos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $codigos[] = CodigoMapper::mapRowToDTO($row);
            }
            return $codigos;
        } catch (PDOException $e) {
            error_log("Error al leer todos los codigos: " . $e->getMessage());
            return [];
        }
    }

    public function update($codigo)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoUpdate(?, ?, ?, ?)");
            return $stmt->execute([
                $codigo->id,
                $codigo->idCliente,
                $codigo->fechaRegistro,
                $codigo->cantImpresiones
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar codigo: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoDelete(?)");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar codigo: " . $e->getMessage());
            return false;
        }
    }
}
