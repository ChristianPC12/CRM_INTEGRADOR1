<?php
// Archivo: model/login/LoginMapper.php
require_once 'LoginDTO.php';

class LoginMapper
{
    public static function mapRowToDTO($row)
    {
        $dto = new LoginDTO();
        $dto->id = $row['Id'];
        $dto->usuario = $row['Usuario'];
        $dto->contrasena = $row['Contrasena'];
        $dto->rol = $row['Rol'];
        return $dto;
    }
}
