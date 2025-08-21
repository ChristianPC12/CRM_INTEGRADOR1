<?php

require_once 'CumpleDTO.php';

/**
 * Clase CumpleMapper
 *
 * Se encarga de transformar filas devueltas por la base de datos
 * en objetos CumpleDTO. Esto permite desacoplar la estructura
 * interna de la tabla de cumpleaños respecto al resto de la aplicación.
 */
class CumpleMapper
{
    /**
     * Convierte una fila asociativa de la base de datos en un objeto CumpleDTO.
     *
     * @param array $row Fila de datos devuelta por una consulta SQL.
     * @return CumpleDTO Objeto DTO con los valores mapeados.
     */
    public static function mapRowToDTO($row)
    {
        // Crear nueva instancia del DTO
        $dto = new CumpleDTO();

        // Asignación de valores básicos
        $dto->id             = $row['Id'] ?? null;
        $dto->cedula         = $row['Cedula'] ?? '';
        $dto->nombre         = $row['Nombre'] ?? '';
        $dto->correo         = $row['Correo'] ?? '';
        $dto->telefono       = $row['Telefono'] ?? '';
        $dto->fechaCumpleanos= $row['FechaCumpleanos'] ?? null;

        // Asignación de campos de control del cumpleaños
        $dto->estado         = $row['Estado'] ?? 'Activo';
        $dto->fechaLlamada   = $row['FechaLlamada'] ?? null;
        $dto->vence          = $row['Vence'] ?? null;
        $dto->vencido        = $row['Vencido'] ?? null;

        // Devolver objeto DTO listo para uso en la aplicación
        return $dto;
    }
}
