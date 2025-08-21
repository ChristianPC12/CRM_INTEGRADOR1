<?php
// Archivo: model.tarea/TareaDAO.php

require_once 'TareaDTO.php';
require_once 'TareaMapper.php';

/**
 * Clase TareaDAO
 *
 * Objeto de Acceso a Datos (DAO) para la entidad Tarea.
 * Encargada de gestionar las operaciones CRUD sobre la base de datos
 * mediante procedimientos almacenados (SP).
 */
class TareaDAO
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
     * Crear una nueva tarea
     * Llama al procedimiento almacenado TareaCreate.
     */
    public function create($tarea)
    {
        $stmt = $this->conn->prepare("CALL TareaCreate(?, ?)");
        return $stmt->execute([
            $tarea->descripcion,
            $tarea->estado
        ]);
    }

    /**
     * Leer una tarea por su ID
     * Devuelve un objeto TareaDTO o null si no existe.
     */
    public function read($id)
    {
        $stmt = $this->conn->prepare("CALL TareaRead(?)");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? TareaMapper::mapRowToDTO($row) : null;
    }

    /**
     * Leer todas las tareas
     * Llama al procedimiento almacenado TareaReadAll
     * y devuelve una lista de objetos TareaDTO.
     */
    public function readAll()
    {
        $stmt = $this->conn->prepare("CALL TareaReadAll()");
        $stmt->execute();
        $tareas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tareas[] = TareaMapper::mapRowToDTO($row);
        }
        return $tareas;
    }

    /**
     * Actualizar el estado de una tarea
     * Llama al procedimiento almacenado TareaUpdate.
     */
    public function update($id, $estado)
    {
        $stmt = $this->conn->prepare("CALL TareaUpdate(?, ?)");
        return $stmt->execute([$id, $estado]);
    }

    /**
     * Eliminar una tarea por su ID
     * Llama al procedimiento almacenado TareaDelete.
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("CALL TareaDelete(?)");
        return $stmt->execute([$id]);
    }
}
