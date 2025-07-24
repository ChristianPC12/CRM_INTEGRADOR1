<?php
class BitacoraDTO {
    public $id;
    public $idUsuario;
    public $horaEntrada;
    public $horaSalida;
    public $fecha;
    public $nombreUsuario; // <-- Nuevo campo

    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }
    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function getHoraEntrada() {
        return $this->horaEntrada;
    }
    public function setHoraEntrada($horaEntrada) {
        $this->horaEntrada = $horaEntrada;
    }

    public function getHoraSalida() {
        return $this->horaSalida;
    }
    public function setHoraSalida($horaSalida) {
        $this->horaSalida = $horaSalida;
    }

    public function getFecha() {
        return $this->fecha;
    }
    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function getNombreUsuario() {
        return $this->nombreUsuario;
    }
    public function setNombreUsuario($nombreUsuario) {
        $this->nombreUsuario = $nombreUsuario;
    }
}
?>
