<?php
// Archivo: model.tarea/TareaMapper.php
require_once 'TareaDTO.php';

class TareaMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new TareaDTO();
        $dto->id = $row['id'];
        $dto->descripcion = $row['descripcion'];
        $dto->estado = $row['estado'];
        $dto->fechaCreacion = $row['fecha_creacion'];
        return $dto;
    }
}
