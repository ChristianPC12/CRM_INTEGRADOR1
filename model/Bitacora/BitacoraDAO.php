<?php
require_once 'BitacoraMapper.php';

class BitacoraDAO {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function handle($action, $data) {
        switch ($action) {
            case 'create':
                return $this->create($data);
            case 'read':
                return $this->read($data['id']);
            case 'readAll':
                return $this->readAll();
            case 'selectByUsuario':
                return $this->selectByUsuario($data['idUsuario']);
            case 'update':
                return $this->update($data);
            default:
                throw new Exception("Acción no válida: $action");
        }
    }

    public function create(BitacoraDTO $bitacora) {
        $stmt = $this->conn->prepare("CALL BitacoraCreate(?, ?, ?, ?)");
        return $stmt->execute([
            $bitacora->idUsuario,
            $bitacora->horaEntrada,
            $bitacora->horaSalida,
            $bitacora->fecha
        ]);
    }

    public function read($id) {
        $stmt = $this->conn->prepare("CALL BitacoraRead(?)");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? BitacoraMapper::mapRowToDTO($row): null;
    }

   public function readAll()
{
    $stmt = $this->conn->prepare("CALL BitacoraReadAll()");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $bitacoras = [];

    foreach ($result as $row) {
        $bitacoras[] = BitacoraMapper::mapRowToDTO($row);

    }

    return $bitacoras;
}

    public function selectByUsuario($idUsuario) {
        $stmt = $this->conn->prepare("CALL BitacoraSelectByUsuario(?)");
        $stmt->execute([$idUsuario]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = BitacoraMapper::mapRowToDTO($row);

        }
        return $result;
    }

    public function update(BitacoraDTO $bitacora) {
        $stmt = $this->conn->prepare("CALL BitacoraUpdate(?, ?, ?, ?, ?)");
        return $stmt->execute([
            $bitacora->id,
            $bitacora->idUsuario,
            $bitacora->horaEntrada,
            $bitacora->horaSalida,
            $bitacora->fecha
        ]);
    }

    public function readByUsuario($idUsuario)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM bitacora WHERE IdUsuario = ?");
            $stmt->execute([$idUsuario]);

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = BitacoraMapper::mapRowToDTO($row);
            }
            return $result;
        } catch (PDOException $e) {
            return "Error al leer bitácoras por usuario: " . $e->getMessage();
        }
    }
    public function hayPorExpirar(): bool
{
    $sql = "SELECT COUNT(*) as total FROM bitacora 
            WHERE Fecha <= DATE_SUB(CURDATE(), INTERVAL 25 DAY)";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row && $row['total'] > 0;
}
}
?>