<?php
/**
 * Clase BitacoraDTO
 *
 * Objeto de Transferencia de Datos (DTO) para la entidad Bitácora.
 * Se utiliza para transportar información entre capas de la aplicación.
 * Contiene atributos básicos de la tabla "bitacora" y métodos
 * getter/setter para manipularlos.
 */
class BitacoraDTO {
    // Identificador único de la bitácora (si aplica)
    public $id;

    // Relación con el usuario que generó el registro
    public $idUsuario;

    // Hora de entrada del usuario
    public $horaEntrada;

    // Hora de salida del usuario
    public $horaSalida;

    // Fecha del registro
    public $fecha;

    // Nombre del usuario asociado (dato adicional para mostrar en vistas)
    public $nombreUsuario; // <-- Nuevo campo

    /**
     * Obtiene el ID del registro de la bitácora
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Asigna el ID al registro de la bitácora
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Obtiene el ID del usuario
     */
    public function getIdUsuario() {
        return $this->idUsuario;
    }

    /**
     * Asigna el ID del usuario
     */
    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    /**
     * Obtiene la hora de entrada
     */
    public function getHoraEntrada() {
        return $this->horaEntrada;
    }

    /**
     * Asigna la hora de entrada
     */
    public function setHoraEntrada($horaEntrada) {
        $this->horaEntrada = $horaEntrada;
    }

    /**
     * Obtiene la hora de salida
     */
    public function getHoraSalida() { 
        return $this->horaSalida;
    }

    /**
     * Asigna la hora de salida
     */
    public function setHoraSalida($horaSalida) {
        $this->horaSalida = $horaSalida;
    }

    /**
     * Obtiene la fecha del registro
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Asigna la fecha del registro
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    /**
     * Obtiene el nombre del usuario asociado
     */
    public function getNombreUsuario() {
        return $this->nombreUsuario;
    }

    /**
     * Asigna el nombre del usuario asociado
     */
    public function setNombreUsuario($nombreUsuario) {
        $this->nombreUsuario = $nombreUsuario;
    }
}
?>
