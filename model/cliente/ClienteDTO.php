<?php
// Archivo: model.cliente/ClienteDTO.php
class ClienteDTO
{
    public $id;
    public $cedula;
    public $nombre;
    public $correo;
    public $telefono;
    public $lugarResidencia;
    public $fechaCumpleanos;
    public $acumulado; // NUEVO: Campo para el saldo/acumulado
}
