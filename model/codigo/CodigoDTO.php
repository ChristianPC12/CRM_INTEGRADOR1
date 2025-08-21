<?php
// Archivo: model/codigo/CodigoDTO.php

/**
 * Clase CodigoDTO
 *
 * Objeto de Transferencia de Datos (DTO) para la entidad Código.
 * Contiene únicamente atributos públicos que representan los campos
 * relacionados con los códigos de barras asignados a clientes.
 *
 * Se utiliza para transportar información entre la base de datos
 * y las diferentes capas de la aplicación sin lógica adicional.
 */
class CodigoDTO
{
    // Identificador único del código en la base de datos
    public $id;

    // Relación con el cliente (número de tarjeta asignada)
    public $idCliente;

    // Valor del código de barras generado
    public $codigoBarra;

    // Cantidad de impresiones realizadas para este código
    public $cantImpresiones;

    // Estado actual del código (ej. Activo / Inactivo)
    public $estado;

    // Motivo registrado cuando el código cambia de estado
    public $motivoCambio;

    // Fecha en que se realizó el cambio de estado
    public $fechaCambio;
}
