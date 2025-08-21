<?php
// Archivo: model.cliente/ClienteDAO.php

require_once 'ClienteDTO.php';
require_once 'ClienteMapper.php';

/**
 * Clase ClienteDAO
 *
 * Objeto de Acceso a Datos (DAO) para la entidad Cliente.
 * Se encarga de realizar todas las operaciones de inserción, lectura,
 * actualización y eliminación en la base de datos relacionadas con clientes.
 * Utiliza procedimientos almacenados para la mayoría de las operaciones
 * y mappers para transformar los resultados en objetos DTO.
 */
class ClienteDAO
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
     * Crear un nuevo cliente
     * Inserta un cliente en la base de datos mediante el procedimiento almacenado ClienteCreate.
     */
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
            // Se captura cualquier error PDO y se devuelve en formato JSON
            echo json_encode([
                'success' => false,
                'message' => 'Error PDO: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Leer un cliente por su ID
     * Retorna un objeto ClienteDTO o null si no se encuentra.
     */
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

    /**
     * Leer todos los clientes
     * Retorna una lista de ClienteDTO con los clientes obtenidos.
     * Se calcula también el total histórico de compras de cada cliente.
     */
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

    /**
     * Obtener el total histórico de compras de un cliente
     * Consulta la tabla de compra y suma los montos por cliente.
     */
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

    /**
     * Verifica si ya existe un teléfono o correo registrado en otro cliente
     * Se utiliza un procedimiento almacenado para detectar duplicados.
     */
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

    /**
     * Verifica si ya existe una cédula registrada en otro cliente
     * Se usa un procedimiento almacenado para la verificación.
     */
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

    /**
     * Actualizar un cliente existente
     * Modifica los datos de un cliente en la base de datos con el procedimiento almacenado ClienteUpdate.
     */
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

    /**
     * Eliminar un cliente por su ID
     * Utiliza el procedimiento almacenado ClienteDelete.
     */
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
