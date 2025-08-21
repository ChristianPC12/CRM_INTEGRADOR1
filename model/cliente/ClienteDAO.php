<?php
// Archivo: model/cliente/ClienteDAO.php

require_once 'ClienteDTO.php';
require_once 'ClienteMapper.php';

class ClienteDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /** Crear */
    public function create($cliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteCreate(?, ?, ?, ?, ?, ?, ?, ?)");
            $ok = $stmt->execute([
                $cliente->cedula,
                self::nullIfEmpty($cliente->nombre),
                self::nullIfEmpty($cliente->correo),
                self::nullIfEmpty($cliente->telefono),
                self::nullIfEmpty($cliente->lugarResidencia),
                self::nullIfEmpty($cliente->fechaCumpleanos),
                self::nullIfEmpty($cliente->alergias),
                self::nullIfEmpty($cliente->gustosEspeciales)
            ]);
            $stmt->closeCursor(); // IMPORTANTE
            return $ok;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error PDO: ' . $e->getMessage()]);
            exit;
        }
    }

    /** Leer por Id */
    public function read($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteRead(?)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $row ? ClienteMapper::mapRowToDTO($row) : null;
        } catch (PDOException $e) {
            error_log("Error al leer cliente: " . $e->getMessage());
            return null;
        }
    }

    /** Leer todos */
    public function readAll()
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteReadAll()");
            $stmt->execute();
            $clientes = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Corregido: SUM(Total) según tu tabla compra
                $row['TotalHistorico'] = $this->obtenerTotalHistoricoPorCliente($row['Id']);
                $clientes[] = ClienteMapper::mapRowToDTO($row);
            }
            $stmt->closeCursor();
            return $clientes;
        } catch (PDOException $e) {
            error_log("Error al leer todos los clientes: " . $e->getMessage());
            return [];
        }
    }

    /** Total histórico por cliente (tabla compra: Total) */
    private function obtenerTotalHistoricoPorCliente($idCliente)
    {
        try {
            $stmt = $this->conn->prepare("SELECT IFNULL(SUM(Total), 0) AS Total FROM compra WHERE IdCliente = ?");
            $stmt->execute([$idCliente]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['Total'] : 0;
        } catch (PDOException $e) {
            error_log("Error al obtener total histórico: " . $e->getMessage());
            return 0;
        }
    }

    /** Duplicados tel/correo usando SP (excluye mismo Id en el SP) */
    public function existeTelefonoOCorreo($telefono, $correo, $id = null)
    {
        try {
            $tel = self::nullIfEmpty($telefono);
            $cor = self::nullIfEmpty($correo);

            $stmt = $this->conn->prepare("CALL ClienteVerificarDuplicados(?, ?, ?)");
            $stmt->execute([$tel, $cor, $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            // SP devuelve fila si hay duplicado
            return $row ? true : false;
        } catch (PDOException $e) {
            error_log("Error al verificar duplicados (SP): " . $e->getMessage());
            return true; // ser conservador
        }
    }

    /** Duplicado cédula usando SP (excluye mismo Id en el SP) */
    public function existeCedula($cedula, $id = null)
    {
        try {
            $ced = self::nullIfEmpty($cedula);
            if ($ced === null) return false;

            $stmt = $this->conn->prepare("CALL ClienteExisteCedula(?, ?)");
            $stmt->execute([$ced, $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $row ? true : false;
        } catch (PDOException $e) {
            error_log("Error al verificar cédula duplicada: " . $e->getMessage());
            return true;
        }
    }

    /** Update completo con SP */
    public function update($cliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteUpdate(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ok = $stmt->execute([
                $cliente->id,
                self::nullIfEmpty($cliente->cedula),
                self::nullIfEmpty($cliente->nombre),
                self::nullIfEmpty($cliente->correo),
                self::nullIfEmpty($cliente->telefono),
                self::nullIfEmpty($cliente->lugarResidencia),
                self::nullIfEmpty($cliente->fechaCumpleanos),
                // Si viene null, que pase null (el SP decide default/conservación)
                $cliente->acumulado === '' ? null : $cliente->acumulado,
                self::nullIfEmpty($cliente->alergias),
                self::nullIfEmpty($cliente->gustosEspeciales)
            ]);
            $stmt->closeCursor();
            return $ok;
        } catch (PDOException $e) {
            error_log("Error al actualizar cliente: " . $e->getMessage());
            return false;
        }
    }

    /** Update solo saldo (ligero, sin SP) */
    public function updateSaldo(int $id, int $acumulado): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE cliente SET Acumulado = ? WHERE Id = ?");
            return $stmt->execute([$acumulado, $id]);
        } catch (PDOException $e) {
            error_log("Error al actualizar saldo: " . $e->getMessage());
            return false;
        }
    }

    /** Delete con SP */
    public function delete($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteDelete(?)");
            $ok = $stmt->execute([$id]);
            $stmt->closeCursor();
            return $ok;
        } catch (PDOException $e) {
            error_log("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }

    /** Utilidad: convertir "", null, "null" => null */
    private static function nullIfEmpty($v)
    {
        if ($v === null) return null;
        if (is_string($v) && strtolower(trim($v)) === 'null') return null;
        $t = is_string($v) ? trim($v) : $v;
        return ($t === '') ? null : $t;
    }
}
