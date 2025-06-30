<?php
// Archivo: model/cliente/ClienteDTO.php
class ClienteDTO
{
    public $id;
    public $cedula;
    public $nombre;
    public $correo;
    public $telefono;
    public $lugarResidencia;
    public $fechaCumpleanos;
    public $acumulado;
    public $fechaRegistro; // NUEVO: Fecha en que se registró el cliente
}
