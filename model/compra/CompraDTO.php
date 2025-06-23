<?php
// Archivo: model/compra/CompraDTO.php

/**
 * Data Transfer Object (DTO) para la entidad Compra.
 * Representa una fila de la tabla 'compra'.
 */
class CompraDTO
{
    /** @var int $idCompra ID de la compra (PK, autoincremental) */
    public $idCompra;

    /** @var string $fechaCompra Fecha en que se realizó la compra (formato 'YYYY-MM-DD') */
    public $fechaCompra;

    /** @var int $total Monto total de la compra */
    public $total;

    /** @var int $idCliente ID del cliente asociado a la compra (FK) */
    public $idCliente;
}
