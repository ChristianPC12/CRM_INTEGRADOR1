<?php
// Archivo: model/compra/CompraMapper.php

require_once 'CompraDTO.php';

/**
 * Clase CompraMapper
 *
 * Se encarga de transformar las filas devueltas por la base de datos
 * en objetos CompraDTO. De esta forma se separa la estructura interna
 * de la tabla de la lógica de la aplicación.
 */
class CompraMapper
{
    /**
     * Convierte una fila asociativa de la base de datos en un objeto CompraDTO.
     *
     * @param array $row Fila asociativa obtenida de la consulta SQL.
     * @return CompraDTO Objeto DTO con los valores mapeados.
     */
    public static function mapRowToDTO($row)
    {
        // Crear una nueva instancia del DTO
        $dto = new CompraDTO();

        // Asignar valores de la fila a las propiedades del DTO
        $dto->idCompra    = $row['IdCompra'];
        $dto->fechaCompra = $row['FechaCompra'];
        $dto->total       = $row['Total'];
        $dto->idCliente   = $row['IdCliente'];

        // Retornar el objeto DTO ya construido
        return $dto;
    }
}
