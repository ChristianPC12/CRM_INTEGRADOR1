<?php
// Archivo: model.tarea/TareaDAO.php

require_once 'TareaDTO.php';
require_once 'TareaMapper.php';

class TareaDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($tarea)
    {
        $stmt = $this->conn->prepare("CALL TareaCreate(?, ?)");
        return $stmt->execute([
            $tarea->descripcion,
            $tarea->estado
        ]);
    }

    public function read($id)
    {
        $stmt = $this->conn->prepare("CALL TareaRead(?)");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? TareaMapper::mapRowToDTO($row) : null;
    }

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

    public function update($id, $estado)
    {
        $stmt = $this->conn->prepare("CALL TareaUpdate(?, ?)");
        return $stmt->execute([$id, $estado]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("CALL TareaDelete(?)");
        return $stmt->execute([$id]);
    }
}
