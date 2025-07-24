<?php
require_once 'BitacoraMapper.php';
require_once __DIR__ . '/../../config/Database.php';

class BitacoraDAO {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Crear nueva entrada en la bitácora
    public function create(BitacoraDTO $bitacora) {
        $stmt = $this->conn->prepare("CALL BitacoraCreate(?, ?, ?, ?)");
        return $stmt->execute([
            $bitacora->idUsuario,
            $bitacora->horaEntrada,
            $bitacora->horaSalida,
            $bitacora->fecha
        ]);
    }

    // Leer una entrada por ID (💡no aplica porque no hay PK, lo dejamos por compatibilidad)
    public function read($id) {
        return null; // no se puede implementar sin una PK única
    }

    // Leer todas las entradas
    // Leer todas las entradas con el nombre del usuario
public function readAll() {
    $sql = "SELECT b.*, u.Usuario AS nombreUsuario 
            FROM bitacora b 
            JOIN usuario u ON b.IdUsuario = u.Id
            ORDER BY b.Fecha DESC, b.HoraEntrada DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bitacoras = [];
    foreach ($result as $row) {
        $bitacoras[] = BitacoraMapper::mapRowToDTO($row);
    }

    return $bitacoras;
}


    // Actualizar la hora de salida (💡usamos múltiples condiciones para identificar la entrada)
    public function update(BitacoraDTO $dto) {
        $sql = "UPDATE bitacora 
                SET horaSalida = :horaSalida 
                WHERE IdUsuario = :idUsuario 
                AND Fecha = :fecha 
                AND horaEntrada = :horaEntrada";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':horaSalida', $dto->horaSalida);
        $stmt->bindParam(':idUsuario', $dto->idUsuario);
        $stmt->bindParam(':fecha', $dto->fecha);
        $stmt->bindParam(':horaEntrada', $dto->horaEntrada);
        return $stmt->execute();
    }

    // Leer todas las entradas de un usuario
    public function readByUsuario($idUsuario) {
        $stmt = $this->conn->prepare("SELECT * FROM bitacora WHERE IdUsuario = ? ORDER BY Fecha DESC, HoraEntrada DESC");
        $stmt->execute([$idUsuario]);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = BitacoraMapper::mapRowToDTO($row);
        }
        return $result;
    }

    // Eliminar registros expirados (más de 30 días)
    public function eliminarExpirados() {
        $sql = "DELETE FROM bitacora 
                WHERE (horaSalida IS NOT NULL AND horaSalida != '00:00:00')
                AND CONCAT(fecha, ' ', horaEntrada) < NOW() - INTERVAL 30 DAY";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }

    // Verificar si el usuario ya tiene una sesión activa sin salida
    public function tieneSesionActiva($idUsuario) {
        $sql = "SELECT COUNT(*) FROM bitacora 
                WHERE IdUsuario = ? AND (horaSalida IS NULL OR horaSalida = '00:00:00')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchColumn() > 0;
    }

    // Limpieza automática
    public static function ejecutarLimpiezaAutomatica() {
        $db = (new Database())->getConnection();
        $dao = new BitacoraDAO($db);
        return $dao->eliminarExpirados();
    }
}
?>
