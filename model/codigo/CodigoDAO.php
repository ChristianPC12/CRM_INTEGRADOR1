<?php
// Archivo: model/codigo/CodigoDAO.php

require_once 'CodigoDTO.php';
require_once 'CodigoMapper.php';

/**
 * Clase CodigoDAO
 *
 * Objeto de Acceso a Datos (DAO) para la entidad Código.
 * Se encarga de ejecutar operaciones en la base de datos utilizando
 * procedimientos almacenados para crear, leer, actualizar, eliminar
 * y reasignar códigos de barras asociados a clientes.
 */
class CodigoDAO
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
     * Crear un nuevo código de barras
     * Inserta un registro usando el procedimiento almacenado CodigoCreate.
     */
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

    /**
     * Leer un código por su ID
     * Devuelve un DTO con los datos o un mensaje de error si no se encuentra.
     */
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

    /**
     * Leer todos los códigos
     * Ejecuta el procedimiento almacenado CodigoReadAll y devuelve la lista completa.
     */
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

    /**
     * Actualizar un código existente
     * Modifica los datos mediante el procedimiento almacenado CodigoUpdate.
     */
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

    /**
     * Eliminar un código por su ID
     * Ejecuta el procedimiento almacenado CodigoDelete.
     */
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

    /**
     * Leer un código asociado a un cliente
     * Busca mediante el procedimiento almacenado CodigoSelectByCliente.
     */
    public function readByCliente($idCliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL CodigoSelectByCliente(?)");
            $stmt->execute([$idCliente]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return [
                    'success' => false,
                    'message' => "No se encontró código activo para el cliente $idCliente"
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

    /**
     * Reasignar un código de barras
     * Llama al procedimiento almacenado CodigoReassign,
     * el cual genera internamente un nuevo código para el cliente.
     */
    public function reassignCode($idCliente, $motivo)
    {
        try {
            // El procedimiento se encarga de localizar el código activo y reasignarlo
            $stmt = $this->conn->prepare("CALL CodigoReassign(?, ?)");
            $result = $stmt->execute([$idCliente, $motivo]);
            
            if ($result) {
                // Obtener el nuevo código generado desde la respuesta
                $nuevoCodigoResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $nuevoCodigo = $nuevoCodigoResult['NuevoCodigoGenerado'] ?? '';
                
                return [
                    'success' => true,
                    'nuevoCodigo' => $nuevoCodigo,
                    'message' => 'Código reasignado exitosamente'
                ];
            } else {
                $errorInfo = $stmt->errorInfo();
                return [
                    'success' => false,
                    'message' => 'Error al reasignar código: ' . $errorInfo[2]
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error PDO (reassignCode): ' . $e->getMessage()
            ];
        }
    }
}
