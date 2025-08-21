<?php
// Archivo: model.tarea/TareaDTO.php

/**
 * Clase TareaDTO
 *
 * Objeto de Transferencia de Datos (DTO) para la entidad Tarea.
 * Representa una fila de la tabla `tarea` y se utiliza para transportar
 * información entre la base de datos y las distintas capas de la aplicación.
 */
class TareaDTO
{
    // Identificador único de la tarea (clave primaria)
    public $id;

    // Descripción o detalle de la tarea
    public $descripcion;

    // Estado actual de la tarea (ej. PENDIENTE, COMPLETADA)
    public $estado;

    // Fecha en que la tarea fue creada (asignada automáticamente en BD)
    public $fechaCreacion;
}
