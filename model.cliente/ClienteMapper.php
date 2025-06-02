<?php
// Archivo: model.cliente/ClienteMapper.php
require_once 'ClienteDTO.php';

class ClienteMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new ClienteDTO();
        $dto->id = $row['Id'];
        $dto->nombre = $row['Nombre'];
        $dto->correo = $row['Correo'];
        $dto->telefono = $row['Telefono'];
        $dto->lugarResidencia = $row['LugarResidencia'];
        $dto->fechaCumpleanos = $row['FechaCumpleanos'];
        return $dto;
    }
}
