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

    public function obtenerCumplesSemana(int $offset = 0)
    {
        try {
            // 0 = semana actual, 1 = semana siguiente
            $stmt = $this->conn->prepare("CALL ClienteCumpleSemanaOffset(:o)");
            $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $clientes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $clientes[] = CumpleMapper::mapRowToDTO($row);
            }
            $stmt->closeCursor();

            // (Opcional) ordenar por fecha de cumpleaños
            usort($clientes, function ($a, $b) {
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
        // El campo 'estado' ya no existe en la tabla cliente, así que esta función no realiza ninguna acción.
        return true;
    }

    public function obtenerHistorial()
    {
        try {

            // ✅ Actualiza los vencidos antes de consultar
            $this->marcarCumplesVencidos();

            // ✅ Purga los registros con más de 30 días
            $this->purgarHistorial30Dias();
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
    private function purgarHistorial30Dias()
    {
        try {
            $stmt = $this->conn->prepare("CALL PurgarHistorialCumple30Dias()");
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al purgar historial: " . $e->getMessage());
        }
    }





}
