<?php
// Archivo: model/codigo/CodigoMapper.php

require_once 'CodigoDTO.php';

/**
 * Clase CodigoMapper
 *
 * Se encarga de convertir filas obtenidas desde la base de datos
 * en objetos CodigoDTO. De esta forma se separa la estructura
 * de la BD de la lógica de la aplicación.
 */
class CodigoMapper
{
    /**
     * Convierte una fila de base de datos (array asociativo) en un objeto CodigoDTO.
     *
     * @param array $row Fila de datos proveniente de la consulta SQL.
     * @return CodigoDTO Objeto DTO con los datos mapeados.
     */
    public static function mapRowToDTO($row)
    {
        // Se crea una nueva instancia del DTO
        $dto = new CodigoDTO();

        // Asignación de campos básicos desde la BD
        $dto->id             = $row['Id'];
        $dto->idCliente      = $row['IdCliente'];
        $dto->codigoBarra    = $row['CodigoBarra'];
        $dto->cantImpresiones= $row['CantImpresiones'];

        // Asignación de campos de control del código
        $dto->estado        = $row['Estado'] ?? 'Activo';  // Valor por defecto "Activo" si no se especifica
        $dto->motivoCambio  = $row['MotivoCambio'];
        $dto->fechaCambio   = $row['FechaCambio'];

        // Se devuelve el DTO ya construido
        return $dto;
    }
}
