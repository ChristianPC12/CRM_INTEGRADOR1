<?php
require_once 'CumpleDTO.php';
require_once 'CumpleMapper.php';

/**
 * Clase CumpleDAO
 *
 * Objeto de Acceso a Datos (DAO) para la entidad Cumple.
 * Gestiona operaciones relacionadas con el registro y seguimiento
 * de cumpleaños de clientes, así como su historial y estados.
 */
class CumpleDAO
{
    // Conexión a la base de datos
    private $conn;

    /**
     * Constructor
     * Recibe una conexión PDO y la asigna a la clase.
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener cumpleaños de la semana
     *
     * Llama al SP `ClienteCumpleSemanaOffset`, que devuelve los clientes
     * que cumplen años en la semana actual o en la siguiente (según offset).
     * 
     * @param int $offset 0 = semana actual, 1 = semana siguiente
     * @return CumpleDTO[] Lista de objetos DTO con los cumpleaños encontrados
     */
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

    /**
     * Actualizar el estado de un cumpleaños
     *
     * Nota: El campo 'estado' ya no existe en la tabla cliente,
     * por lo tanto este método no realiza ninguna acción.
     * Se mantiene por compatibilidad.
     */
    public function actualizarEstado($id, $estado)
    {
        return true;
    }

    /**
     * Obtener historial de cumpleaños
     *
     * Devuelve el historial de cumpleaños registrados en la tabla `cumple`,
     * realizando antes:
     *  - Actualización de vencidos (marca como vencidos los registros pasados).
     *  - Purga de registros con más de 30 días.
     *
     * @return CumpleDTO[] Lista de objetos DTO con el historial
     */
    public function obtenerHistorial()
    {
        try {
            // ✅ Actualiza los vencidos antes de consultar
            $this->marcarCumplesVencidos();

            // ✅ Purga los registros con más de 30 días
            $this->purgarHistorial30Dias();

            // ✅ Vuelve a actualizar los vencidos (seguridad extra)
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

    /**
     * Marcar cumpleaños vencidos
     *
     * Actualiza el campo `Vencido` en los registros cuya fecha límite ya pasó.
     */
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

    /**
     * Purgar historial con más de 30 días
     *
     * Llama al SP `PurgarHistorialCumple30Dias` para eliminar
     * registros antiguos y mantener la tabla optimizada.
     */
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
