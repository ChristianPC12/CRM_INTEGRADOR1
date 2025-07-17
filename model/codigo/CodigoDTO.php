<?php
// model/codigo/CodigoDTO.php

class CodigoDTO
{
    public $id;
    public $idCliente;        // Número de tarjeta
    public $codigoBarra;
    public $cantImpresiones;
    public $estado;           // Estado del código (Activo/Inactivo)
    public $motivoCambio;     // Motivo del cambio de estado
    public $fechaCambio;      // Fecha del cambio de estado
}
