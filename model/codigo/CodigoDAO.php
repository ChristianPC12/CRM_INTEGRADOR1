<?php
// model/codigo/CodigoDAO.php

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
            $stmt = $this->conn->prepare("CALL CodigoCreate(?, ?, ?)");
            $success = $stmt->execute([
                $codigo->idCliente,
                $codigo->codigoBarra,
                $codigo->cantImpresiones
            ]);
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [
                    'success' => false,
                    'message' => 'Error al crear código: ' . $errorInfo[2]
                ];
            }
            return ['success' => true];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error PDO (create): ' . $e->getMessage()
            ];
        }
    }

    public function read($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoRead(?)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return [
                    'success' => false,
                    'message' => "No se encontró el código con id $id"
                ];
            }
            return [
                'success' => true,
                'data' => CodigoMapper::mapRowToDTO($row)
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error PDO (read): ' . $e->getMessage()
            ];
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
            return [
                'success' => true,
                'data' => $codigos
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error PDO (readAll): ' . $e->getMessage()
            ];
        }
    }

    public function update($codigo)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoUpdate(?, ?, ?)");
            $success = $stmt->execute([
                $codigo->id,
                $codigo->codigoBarra,
                $codigo->cantImpresiones
            ]);
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [
                    'success' => false,
                    'message' => 'Error al actualizar código: ' . $errorInfo[2]
                ];
            }
            return ['success' => true];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error PDO (update): ' . $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoDelete(?)");
            $success = $stmt->execute([$id]);
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [
                    'success' => false,
                    'message' => 'Error al eliminar código: ' . $errorInfo[2]
                ];
            }
            return ['success' => true];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error PDO (delete): ' . $e->getMessage()
            ];
        }
    }

    public function readByCliente($idCliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoSelectByCliente(?)");
            $stmt->execute([$idCliente]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return [
                    'success' => false,
                    'message' => "No se encontró código para el cliente $idCliente"
                ];
            }
            return [
                'success' => true,
                'data' => CodigoMapper::mapRowToDTO($row)
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error PDO (readByCliente): ' . $e->getMessage()
            ];
        }
    }
}
