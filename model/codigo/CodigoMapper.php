<?php
// Archivo: model/codigo/CodigoMapper.php
require_once 'CodigoDTO.php';

class CodigoMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new CodigoDTO();
        $dto->id = $row['Id'];
        $dto->idCliente = $row['IdCliente'];
        $dto->fechaRegistro = $row['FechaRegistro'];
        $dto->cantImpresiones = $row['CantImpresiones'];
        return $dto;
    }
}