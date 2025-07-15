<?php
// Archivo: model/bitacora/BitacoraMapper.php

require_once 'BitacoraDTO.php';

/**
 * Mapper para convertir filas de la base de datos en objetos BitacoraDTO.
 */
class BitacoraMapper
{
    /**
     * Convierte una fila asociativa de la base de datos en un objeto BitacoraDTO.
     * @param array $row Fila asociativa.
     * @return BitacoraDTO
     */
  public static function mapRowToDTO($row)
{
    $dto = new BitacoraDTO();
    $dto->id = $row['Id'];
    $dto->idUsuario = $row['IdUsuario'];
    $dto->horaEntrada = $row['HoraEntrada'];
    $dto->horaSalida = $row['HoraSalida'];
    $dto->fecha = $row['Fecha'];
    return $dto;
}

public static function mapDto($data)
    {
        $dto = new BitacoraDTO();
        $dto->id = $data['id'] ?? null;
        $dto->idUsuario = $data['idUsuario'] ?? null;
        $dto->horaEntrada = $data['horaEntrada'] ?? null;
        $dto->horaSalida = $data['horaSalida'] ?? null;
        $dto->fecha = $data['fecha'] ?? null;

        return $dto;
    }
  
}
