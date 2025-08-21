<?php
// Archivo: model/cliente/ClienteMapper.php
require_once 'ClienteDTO.php';

/**
 * Clase ClienteMapper
 *
 * Se encarga de transformar filas de resultados obtenidos de la base de datos
 * en objetos ClienteDTO. De esta manera se desacopla la estructura de la BD
 * del resto de la aplicación, facilitando el transporte de datos.
 */
class ClienteMapper
{
    /**
     * Convierte una fila asociativa de la base de datos en un objeto ClienteDTO.
     *
     * @param array $row Fila obtenida de la base de datos (asociativa).
     * @return ClienteDTO Objeto DTO con los datos mapeados.
     */
    public static function mapRowToDTO($row)
    {
        // Se crea una nueva instancia del DTO
        $dto = new ClienteDTO();

        // Asignación de campos principales
        $dto->id              = $row['Id'];
        $dto->cedula          = $row['Cedula'];
        $dto->nombre          = $row['Nombre'];
        $dto->correo          = $row['Correo'];
        $dto->telefono        = $row['Telefono'];
        $dto->lugarResidencia = $row['LugarResidencia'];
        $dto->fechaCumpleanos = $row['FechaCumpleanos'];
        $dto->acumulado       = $row['Acumulado'];
        $dto->fechaRegistro   = $row['FechaRegistro']; 

        // Campos adicionales añadidos al DTO
        $dto->alergias         = $row['Alergias'];                 // <─ NUEVO
        $dto->gustosEspeciales = $row['GustosEspeciales'];         // <─ NUEVO
        $dto->totalHistorico   = $row['TotalHistorico'];           // <─ EXISTENTE

        // Se devuelve el objeto DTO completo
        return $dto;
    }
}
