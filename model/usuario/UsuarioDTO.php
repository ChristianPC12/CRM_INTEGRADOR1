<?php
// Archivo: model.usuario/UsuarioDTO.php

/**
 * Clase UsuarioDTO
 *
 * Objeto de Transferencia de Datos (DTO) para la entidad Usuario.
 * Representa un registro de la tabla `usuario` y se utiliza
 * para transportar información entre la base de datos y la aplicación.
 */
class UsuarioDTO
{
    // Identificador único del usuario (PK)
    public $id;

    // Nombre de usuario (login)
    public $usuario;

    // Contraseña (almacenada en hash en la BD)
    public $contrasena;

    // Rol del usuario dentro del sistema (ej. ADMIN, USUARIO)
    public $rol;
}
