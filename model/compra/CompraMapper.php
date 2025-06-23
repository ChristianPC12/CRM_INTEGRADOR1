<?php
// Archivo: model/compra/CompraMapper.php

require_once 'CompraDTO.php';

/**
 * Mapper para convertir filas de la base de datos en objetos CompraDTO.
 */
class CompraMapper
{
    /**
     * Convierte una fila asociativa a un objeto CompraDTO.
     * @param array $row Fila asociativa de la base de datos.
     * @return CompraDTO
     */
    public static function mapRowToDTO($row)
    {
        $dto = new CompraDTO();
        $dto->idCompra = $row['IdCompra'];
        $dto->fechaCompra = $row['FechaCompra'];
        $dto->total = $row['Total'];
        $dto->idCliente = $row['IdCliente'];
        return $dto;
    }
}
