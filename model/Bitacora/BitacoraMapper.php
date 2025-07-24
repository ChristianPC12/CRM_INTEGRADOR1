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

        // Como NO hay Id en la tabla, asignamos null o dejamos vacío
        $dto->id = null;

        // Asignamos los campos existentes, validando con ?? null
        $dto->idUsuario = $row['IdUsuario'] ?? null;
        $dto->horaEntrada = $row['HoraEntrada'] ?? null;
        $dto->horaSalida = $row['HoraSalida'] ?? null;
        $dto->fecha = $row['Fecha'] ?? null;
        $dto->nombreUsuario = $row['nombreUsuario'] ?? ''; // ← Añadido

        return $dto;
    }
}
