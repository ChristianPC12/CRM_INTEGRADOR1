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
    public $fechaRegistro;

    public $totalHistorico;        // <─ NUEVO

    public $alergias;             // <─ NUEVO
    public $gustosEspeciales;     // <─ NUEVO

}
