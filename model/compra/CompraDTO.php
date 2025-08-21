<?php
// Archivo: model/compra/CompraDTO.php

/**
 * Clase CompraDTO
 *
 * Objeto de Transferencia de Datos (DTO) para la entidad Compra.
 * Representa una fila de la tabla 'compra' y se utiliza para
 * transportar información entre la base de datos y las capas
 * de la aplicación sin lógica adicional.
 */
class CompraDTO
{
    /** 
     * ID de la compra (clave primaria, autoincremental) 
     * Identifica de forma única cada compra registrada.
     */
    public $idCompra;

    /** 
     * Fecha en que se realizó la compra 
     * Usualmente en formato 'YYYY-MM-DD'.
     */
    public $fechaCompra;

    /** 
     * Monto total de la compra 
     * Representa el valor económico asociado a la transacción.
     */
    public $total;

    /** 
     * ID del cliente asociado a la compra (clave foránea) 
     * Permite relacionar la compra con un cliente en específico.
     */
    public $idCliente;
}
