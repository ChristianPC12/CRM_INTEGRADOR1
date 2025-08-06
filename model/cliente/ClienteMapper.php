<?php
// Archivo: model/cliente/ClienteMapper.php
require_once 'ClienteDTO.php';

class ClienteMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new ClienteDTO();
        $dto->id = $row['Id'];
        $dto->cedula = $row['Cedula'];
        $dto->nombre = $row['Nombre'];
        $dto->correo = $row['Correo'];
        $dto->telefono = $row['Telefono'];
        $dto->lugarResidencia = $row['LugarResidencia'];
        $dto->fechaCumpleanos = $row['FechaCumpleanos'];
        $dto->acumulado = $row['Acumulado'];
        $dto->fechaRegistro = $row['FechaRegistro']; 
        $dto->alergias = $row['Alergias'];                   // <─ NUEVO
        $dto->gustosEspeciales = $row['GustosEspeciales'];   // <─ NUEVO
        $dto->totalHistorico = $row['TotalHistorico'];       // <─ EXISTENTE
        return $dto;
    }
}
