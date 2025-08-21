<?php
// Archivo: model.tarea/TareaMapper.php
require_once 'TareaDTO.php';

/**
 * Clase TareaMapper
 *
 * Se encarga de transformar filas obtenidas de la base de datos
 * en objetos TareaDTO. Permite desacoplar la estructura de la tabla
 * de la lógica de negocio de la aplicación.
 */
class TareaMapper
{
    /**
     * Convierte una fila asociativa de la base de datos en un objeto TareaDTO.
     *
     * @param array $row Fila asociativa devuelta por la consulta SQL.
     * @return TareaDTO Objeto DTO con los datos mapeados.
     */
    public static function mapRowToDTO($row)
    {
        // Crear nueva instancia del DTO
        $dto = new TareaDTO();

        // Asignar valores de la fila a las propiedades del DTO
        $dto->id            = $row['id'];
        $dto->descripcion   = $row['descripcion'];
        $dto->estado        = $row['estado'];
        $dto->fechaCreacion = $row['fecha_creacion'];

        // Retornar objeto DTO listo para uso
        return $dto;
    }
}
