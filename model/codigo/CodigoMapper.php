<?php
// model/codigo/CodigoMapper.php

require_once 'CodigoDTO.php';

class CodigoMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new CodigoDTO();
        $dto->id = $row['Id'];
        $dto->idCliente = $row['IdCliente'];
        $dto->codigoBarra = $row['CodigoBarra'];
        $dto->cantImpresiones = $row['CantImpresiones'];
        $dto->estado = $row['Estado'] ?? 'Activo';
        $dto->motivoCambio = $row['MotivoCambio'] ;
        $dto->fechaCambio = $row['FechaCambio'] ;
        return $dto;
    }
}
