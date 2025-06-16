<?php
// Archivo: model.tarea/TareaMapper.php
require_once 'TareaDTO.php';

class TareaMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new TareaDTO();
        $dto->id = $row['Id'];
        $dto->descripcion = $row['Descripcion'];
        $dto->estado = $row['Estado'];
        $dto->fechaCreacion = $row['FechaCreacion'];
        return $dto;
    }
}
