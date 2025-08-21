<?php

/**
 * Clase CumpleDTO
 *
 * Objeto de Transferencia de Datos (DTO) para la gestión de cumpleaños.
 * Se utiliza para transportar información relacionada con clientes que cumplen años,
 * permitiendo manejar recordatorios, envíos de correo, llamadas o estados de atención.
 */
class CumpleDTO
{
    public $id;
    public $cedula;
    public $nombre;
    public $correo;
    public $telefono;
    public $fechaCumpleanos;

    public $estado = 'PENDIENTE';
    public $fechaLlamada;
    public $vence;
    public $vencido;

    // 🔽 NUEVOS CAMPOS
    public $visitas = 0;
    public $totalHistorico = 0;
}

