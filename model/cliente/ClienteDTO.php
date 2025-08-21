<?php
// Archivo: model/cliente/ClienteDTO.php

/**
 * Clase ClienteDTO
 *
 * Objeto de Transferencia de Datos (DTO) para la entidad Cliente.
 * Se utiliza para transportar información de clientes entre
 * la base de datos y las distintas capas de la aplicación.
 *
 * Contiene únicamente atributos públicos, sin lógica adicional,
 * lo que permite que los mappers y DAOs asignen valores directamente.
 */
class ClienteDTO
{
    // Identificador único del cliente en la base de datos
    public $id;

    // Número de cédula del cliente
    public $cedula;

    // Nombre completo del cliente
    public $nombre;

    // Correo electrónico del cliente
    public $correo;

    // Número de teléfono del cliente
    public $telefono;

    // Lugar de residencia (domicilio)
    public $lugarResidencia;

    // Fecha de cumpleaños del cliente
    public $fechaCumpleanos;

    // Acumulado de compras u otros valores asociados
    public $acumulado;

    // Fecha en que se registró el cliente en el sistema
    public $fechaRegistro;

    // Total histórico de compras del cliente (dato calculado)  <─ NUEVO
    public $totalHistorico;

    // Información sobre alergias del cliente                  <─ NUEVO
    public $alergias;

    // Preferencias o gustos especiales del cliente            <─ NUEVO
    public $gustosEspeciales;
}
