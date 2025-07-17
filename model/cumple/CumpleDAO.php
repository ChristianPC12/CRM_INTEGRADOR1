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
        $clientes = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = CumpleMapper::mapRowToDTO($row);
        }

        // Opcional: ordenar por fecha de cumpleaños
        usort($clientes, function($a, $b) {
            $fechaA = new DateTime($a->fechaCumpleanos);
            $fechaB = new DateTime($b->fechaCumpleanos);
            return $fechaA <=> $fechaB;
        });

        return $clientes;
    } catch (PDOException $e) {
        error_log("Error al obtener cumpleaños de la semana: " . $e->getMessage());
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

public function obtenerHistorial()
{
    try {
        // ✅ Actualiza los vencidos antes de consultar
        $this->marcarCumplesVencidos();

        $sql = "SELECT c.Id, cl.Cedula, cl.Nombre, cl.Correo, cl.Telefono, cl.FechaCumpleanos, 
               c.FechaLlamada, c.Vence, c.Vencido
        FROM cumple c
        INNER JOIN cliente cl ON c.IdCliente = cl.Id
        ORDER BY FechaLlamada DESC";

                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = CumpleMapper::mapRowToDTO($row);
        }

        return $historial;
    } catch (PDOException $e) {
        error_log("Error al obtener historial: " . $e->getMessage());
        return [];
    }
}

private function marcarCumplesVencidos()
{
    try {
        $sql = "UPDATE cumple 
                SET Vencido = 'SI' 
                WHERE Vencido = 'NO' AND Vence < CURDATE()";

        $this->conn->exec($sql);
    } catch (PDOException $e) {
        error_log("Error al marcar vencidos: " . $e->getMessage());
    }
}



}
