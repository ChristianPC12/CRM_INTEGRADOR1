<?php
// Archivo: model/bitacora/BitacoraMapper.php

require_once 'BitacoraDTO.php';

/**
 * Clase BitacoraMapper
 *
 * Su función es transformar las filas obtenidas de la base de datos
 * en objetos de tipo BitacoraDTO. De esta manera, se desacopla
 * la estructura de la base de datos del resto de la aplicación.
 */
class BitacoraMapper
{
    /**
     * Convierte una fila asociativa de la base de datos en un objeto BitacoraDTO.
     *
     * Este método recibe un array asociativo (resultado de una consulta)
     * y crea un nuevo objeto BitacoraDTO con los valores correspondientes.
     * En caso de que alguna columna no exista, se asigna null o un valor vacío.
     */
    public static function mapRowToDTO($row)
    {
        // Creamos una nueva instancia del DTO
        $dto = new BitacoraDTO();

        // Como la tabla no maneja un campo Id único, lo dejamos como null
        $dto->id = null;

        // Se asignan los campos desde la fila de la BD, 
        // utilizando el operador ?? para manejar valores inexistentes
        $dto->idUsuario     = $row['IdUsuario'] ?? null;
        $dto->horaEntrada   = $row['HoraEntrada'] ?? null;
        $dto->horaSalida    = $row['HoraSalida'] ?? null;
        $dto->fecha         = $row['Fecha'] ?? null;

        // Campo adicional: nombre del usuario asociado (proveniente del JOIN con usuario)
        $dto->nombreUsuario = $row['nombreUsuario'] ?? ''; // ← Añadido

        // Se devuelve el objeto ya construido
        return $dto;
    }
}
