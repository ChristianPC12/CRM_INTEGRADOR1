<?php
// Archivo: model.usuario/UsuarioMapper.php
require_once 'UsuarioDTO.php';

/**
 * Clase UsuarioMapper
 *
 * Se encarga de transformar filas de la base de datos en objetos UsuarioDTO.
 * Permite desacoplar la estructura de la tabla de usuarios de la lógica de la aplicación.
 */
class UsuarioMapper
{
    /**
     * Convierte una fila asociativa en un objeto UsuarioDTO.
     *
     * @param array $row Fila obtenida de la base de datos.
     * @param bool $omitPassword Indica si debe omitirse el campo contraseña (true por defecto).
     * @return UsuarioDTO Objeto DTO con los datos mapeados.
     */
    public static function mapRowToDTO($row, $omitPassword = true)
    {
        $dto = new UsuarioDTO();
        $dto->id      = $row['Id'] ?? null;
        $dto->usuario = $row['Usuario'] ?? null;

        // Solo asigna la contraseña si no se omite (ej. login)
        if (!$omitPassword) {
            $dto->contrasena = $row['Contrasena'] ?? null;
        }

        $dto->rol = $row['Rol'] ?? null;
        return $dto;
    }
}
