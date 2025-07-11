<?php
require_once 'CumpleDTO.php';
require_once 'CumpleMapper.php';

class CumpleDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerCumplesSemana()
    {
        try {
            $stmt = $this->conn->prepare("CALL ClienteCumpleSemana()");
            $stmt->execute();
            $cumples = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cumples[] = CumpleMapper::mapRowToDTO($row);
            }
            return $cumples;
        } catch (PDOException $e) {
            error_log("Error al obtener cumpleaños: " . $e->getMessage());
            return [];
        }
    }

    public function actualizarEstado($id, $nuevoEstado)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE cliente SET estado = :estado WHERE id = :id");
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }
}
