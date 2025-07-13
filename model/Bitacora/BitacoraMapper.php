<?php
require_once __DIR__ . '/BitacoraDTO.php';

class BitacoraMapper {
    public static function map($row) {
        $dto = new BitacoraDTO();
        $dto->id = $row['Id'];
        $dto->idUsuario = $row['IdUsuario'] ?? null;
        $dto->usuario = $row['usuario'];
        $dto->rol = $row['rol'];
        $dto->horaEntrada = $row['HoraEntrada'];
        $dto->horaSalida = $row['HoraSalida'];
        $dto->duracion = $row['duracion'];
        $dto->fecha = $row['Fecha'];
        return $dto;
    }

    public static function mapAll($result) {
        $bitacoras = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $bitacoras[] = self::map($row);
        }
        return $bitacoras;
    }
}
