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
        $stmt = $this->conn->prepare("SELECT * FROM cliente WHERE Estado = 'PENDIENTE'");
        $stmt->execute();
        $clientes = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = CumpleMapper::mapRowToDTO($row);
        }

        // Ordenar por fecha de cumpleaños más próxima (día y mes)
        usort($clientes, function($a, $b) {
            $fechaA = DateTime::createFromFormat('Y-m-d', $a->fechaCumpleanos);
            $fechaB = DateTime::createFromFormat('Y-m-d', $b->fechaCumpleanos);
            if (!$fechaA || !$fechaB) return 0;
            $fechaA->setDate((int)date('Y'), (int)$fechaA->format('m'), (int)$fechaA->format('d'));
            $fechaB->setDate((int)date('Y'), (int)$fechaB->format('m'), (int)$fechaB->format('d'));
            return $fechaA <=> $fechaB;
        });

        return $clientes;
    } catch (PDOException $e) {
        error_log("Error al obtener cumpleaños: " . $e->getMessage());
        return [];
    }
}


    public function actualizarEstado($id, $estado)
    {
        try {
            $sql = "UPDATE cliente SET estado = :estado WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":estado", $estado);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }
}
