<?php
// Archivo: model.cliente/ClienteDAO.php

require_once 'ClienteDTO.php';
require_once 'ClienteMapper.php';

class ClienteDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($cliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteCreate(?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $cliente->cedula,
                $cliente->nombre,
                $cliente->correo,
                $cliente->telefono,
                $cliente->lugarResidencia,
                $cliente->fechaCumpleanos,
                $cliente->alergias,
                $cliente->gustosEspeciales
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
            $stmt = $this->conn->prepare("CALL ClienteRead(?)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? ClienteMapper::mapRowToDTO($row) : null;
        } catch (PDOException $e) {
            error_log("Error al leer cliente: " . $e->getMessage());
            return null;
        }
    }

    public function readAll()
{
    try {
        $stmt = $this->conn->prepare("CALL ClienteReadAll()");
        $stmt->execute();
        $clientes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Obtener total histórico manualmente por cliente
            $row['TotalHistorico'] = $this->obtenerTotalHistoricoPorCliente($row['Id']);
            $clientes[] = ClienteMapper::mapRowToDTO($row);
        }
        return $clientes;
    } catch (PDOException $e) {
        error_log("Error al leer todos los clientes: " . $e->getMessage());
        return [];
    }
}

private function obtenerTotalHistoricoPorCliente($idCliente)
{
    try {
        $stmt = $this->conn->prepare("SELECT IFNULL(SUM(Monto), 0) AS Total FROM compra WHERE IdCliente = ?");
        $stmt->execute([$idCliente]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['Total'] : 0;
    } catch (PDOException $e) {
        error_log("Error al obtener total histórico: " . $e->getMessage());
        return 0;
    }
}

    
    public function existeTelefonoOCorreo($telefono, $correo, $id = null) {
        try {
            $stmt = $this->conn->prepare("CALL ClienteVerificarDuplicados(?, ?, ?)");
            $stmt->execute([$telefono, $correo, $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
        } catch (PDOException $e) {
             error_log("Error al verificar duplicados (SP): " . $e->getMessage());
             return true;
        }
    }
    
    public function existeCedula($cedula, $id = null) {
        try {
            $stmt = $this->conn->prepare("CALL ClienteExisteCedula(?, ?)");
            $stmt->execute([$cedula, $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
        } catch (PDOException $e) {
            error_log("Error al verificar cédula duplicada: " . $e->getMessage());
            return true; 
        }
    }

    public function update($cliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteUpdate(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $cliente->id,
                $cliente->cedula,
                $cliente->nombre,
                $cliente->correo,
                $cliente->telefono,
                $cliente->lugarResidencia,
                $cliente->fechaCumpleanos,
                $cliente->acumulado,
                $cliente->alergias,
                $cliente->gustosEspeciales
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteDelete(?)");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }
}
