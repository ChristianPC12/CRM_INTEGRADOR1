<?php
require_once 'UsuarioDTO.php';

class UsuarioMapper {
    public static function mapRowToDTO($row) {
        $dto = new UsuarioDTO();
        $dto->id = $row['Id'] ?? null;
        $dto->usuario = $row['Usuario'] ?? null;
        $dto->contrasena = $row['Contrasena'] ?? null;
        $dto->rol = $row['Rol'] ?? null;
        return $dto;
    }
}
