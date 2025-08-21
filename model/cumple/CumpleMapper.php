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
    public static function mapRowToDTO($row)
    {
        $dto = new CumpleDTO();

        $dto->id              = $row['Id'] ?? null;          // <- Tarjeta
        $dto->cedula          = $row['Cedula'] ?? '';
        $dto->nombre          = $row['Nombre'] ?? '';
        $dto->correo          = $row['Correo'] ?? '';
        $dto->telefono        = $row['Telefono'] ?? '';
        $dto->fechaCumpleanos = $row['FechaCumpleanos'] ?? null;

        $dto->estado        = $row['Estado'] ?? 'Activo';
        $dto->fechaLlamada  = $row['FechaLlamada'] ?? null;
        $dto->vence         = $row['Vence'] ?? null;
        $dto->vencido       = $row['Vencido'] ?? null;

        // 🔽 NUEVOS CAMPOS
        $dto->visitas        = isset($row['Visitas']) ? (int)$row['Visitas'] : 0;
        $dto->totalHistorico = isset($row['TotalHistorico']) ? (int)$row['TotalHistorico'] : 0;

        return $dto;
    }
}

