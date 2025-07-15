<?php

require_once 'CumpleDTO.php';

class CumpleMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new CumpleDTO();
        $dto->id = $row['Id'] ?? null;
        $dto->cedula = $row['Cedula'] ?? '';
        $dto->nombre = $row['Nombre'] ?? '';
        $dto->correo = $row['Correo'] ?? '';
        $dto->telefono = $row['Telefono'] ?? '';
        $dto->fechaCumpleanos = $row['FechaCumpleanos'] ?? null;
        $dto->estado = $row['Estado'] ?? 'PENDIENTE'; // <-- CORREGIDO
        $dto->fechaLlamada = $row['FechaLlamada'] ?? null;
        $dto->vence = $row['Vence'] ?? null;
        $dto->vencido = $row['Vencido'] ?? null;
        return $dto;
    }
}
