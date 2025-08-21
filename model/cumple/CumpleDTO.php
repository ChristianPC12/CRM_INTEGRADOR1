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
    // Identificador único del registro de cumpleaños
    public $id;

    // Cédula del cliente
    public $cedula;

    // Nombre completo del cliente
    public $nombre;

    // Correo electrónico del cliente
    public $correo;

    // Número de teléfono del cliente
    public $telefono;

    // Fecha de cumpleaños del cliente
    public $fechaCumpleanos;

    // Estado actual del cumpleaños (por defecto "PENDIENTE")
    public $estado = 'PENDIENTE'; // Por defecto

    // Fecha en la que se realizó una llamada de seguimiento
    public $fechaLlamada;

    // Fecha límite o de vencimiento de la gestión
    public $vence;

    // Indica si el registro ya está vencido (true/false)
    public $vencido;
}
