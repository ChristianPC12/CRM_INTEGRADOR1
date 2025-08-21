<?php
// Archivo: model/compra/CompraDAO.php

require_once 'CompraDTO.php';
require_once 'CompraMapper.php';

/**
 * Clase CompraDAO
 *
 * Objeto de Acceso a Datos (DAO) para la entidad Compra.
 * Maneja las operaciones CRUD relacionadas con las compras en la base de datos,
 * utilizando procedimientos almacenados (SP).
 */
class CompraDAO
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
     * Crear una compra
     * Llama al SP "SumarCompra", que se encarga de insertar la compra
     * y actualizar el acumulado del cliente.
     */
    public function create($compra)
    {
        try {
            // 1. Llamar al SP que hace todo (INSERT + UPDATE en acumulado)
            $stmt = $this->conn->prepare("CALL SumarCompra(?, ?)");
            return $stmt->execute([
                $compra->idCliente,
                $compra->total
            ]);
        } catch (PDOException $e) {
            error_log("Error al registrar compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Leer una compra por su ID
     * Retorna un objeto CompraDTO o null si no existe.
     */
    public function read($idCompra)
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraRead(?)");
            $stmt->execute([$idCompra]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? CompraMapper::mapRowToDTO($row) : null;
        } catch (PDOException $e) {
            error_log("Error al leer compra: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Eliminar una compra por su ID
     * Ejecuta el procedimiento almacenado CompraDelete.
     */
    public function delete($idCompra)
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraDelete(?)");
            return $stmt->execute([$idCompra]);
        } catch (PDOException $e) {
            error_log("Error al eliminar compra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Leer todas las compras
     * Llama al SP CompraReadAll y devuelve un array de CompraDTO.
     */
    public function readAll()
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraReadAll()");
            $stmt->execute();
            $compras = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $compras[] = CompraMapper::mapRowToDTO($row);
            }
            return $compras;
        } catch (PDOException $e) {
            error_log("Error al leer todas las compras: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Leer todas las compras de un cliente específico
     * Usa el SP CompraReadByCliente para devolver la lista filtrada.
     */
    public function readByCliente($idCliente)
    {
        try {
            $stmt = $this->conn->prepare("CALL CompraReadByCliente(?)");
            $stmt->execute([$idCliente]);
            $compras = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $compras[] = CompraMapper::mapRowToDTO($row);
            }
            return $compras;
        } catch (PDOException $e) {
            error_log("Error al leer compras por cliente: " . $e->getMessage());
            return [];
        }
    }
}
