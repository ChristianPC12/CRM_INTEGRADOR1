<?php
require_once __DIR__ . '/../../config/Database.php';

class BitacoraDAO {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection(); // Asegúrate de usar mysqli
    }

   public function getAll() {
    $sql = "SELECT b.Id, u.Usuario AS usuario, u.Rol AS rol,
                   b.HoraEntrada, b.HoraSalida,
                   TIMEDIFF(b.HoraSalida, b.HoraEntrada) AS duracion,
                   b.Fecha
            FROM bitacora b
            JOIN usuario u ON b.IdUsuario = u.Id";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt; // ← Ahora sí funciona correctamente con PDO
}

   public function getById($id) {
    $stmt = $this->conn->prepare("SELECT b.Id, u.Usuario AS usuario, u.Rol AS rol,
                                         b.HoraEntrada, b.HoraSalida,
                                         TIMEDIFF(b.HoraSalida, b.HoraEntrada) AS duracion,
                                         b.Fecha
                                  FROM bitacora b
                                  JOIN usuario u ON b.IdUsuario = u.Id
                                  WHERE b.Id = ?");
    $stmt->execute([$id]); // en PDO se pasan los parámetros como array
    return $stmt; // devuelve PDOStatement, lo usas luego con fetch o fetchAll
    }

   public function delete($id): bool {
    $stmt = $this->conn->prepare("DELETE FROM bitacora WHERE Id = ?");
    return $stmt->execute([$id]); 
}

   public function limpiarAntiguos(): bool {
    $stmt = $this->conn->prepare("DELETE FROM bitacora WHERE Fecha < (CURDATE() - INTERVAL 1 MONTH)");
    return $stmt->execute(); // esto sí devuelve un bool en PDO
}

   public function hayPorExpirar(): bool {
    $sql = "SELECT COUNT(*) AS total
            FROM bitacora
            WHERE Fecha BETWEEN (CURDATE() - INTERVAL 26 DAY) AND (CURDATE() - INTERVAL 25 DAY)";
    $stmt = $this->conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC); 
    return $row['total'] > 0;
}
public function registrarSalida($idUsuario): bool {
    $stmt = $this->conn->prepare("CALL BitacoraUpdateHoraSalida(?)");
    return $stmt->execute([$idUsuario]);
}
}
