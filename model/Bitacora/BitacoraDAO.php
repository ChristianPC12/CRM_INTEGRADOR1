<?php
// Se incluyen los archivos necesarios para mapear la bitácora y la conexión a la base de datos
require_once 'BitacoraMapper.php';
require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase BitacoraDAO
 *
 * Se encarga de manejar todas las operaciones relacionadas con la tabla "bitacora".
 * Implementa un patrón DAO (Data Access Object) para mantener separada la lógica
 * de acceso a datos respecto al resto de la aplicación.
 */
class BitacoraDAO {
    // Conexión a la base de datos
    private $conn;

    /**
     * Constructor
     * Recibe la conexión PDO a la base de datos y la asigna a la clase.
     */
    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Crear nueva entrada en la bitácora
     * Inserta un registro en la tabla utilizando un procedimiento almacenado.
     */
    public function create(BitacoraDTO $bitacora) {
        $stmt = $this->conn->prepare("CALL BitacoraCreate(?, ?, ?, ?)");
        return $stmt->execute([
            $bitacora->idUsuario,
            $bitacora->horaEntrada,
            $bitacora->horaSalida,
            $bitacora->fecha
        ]);
    }

    /**
     * Leer una entrada por ID
     * (No se implementa ya que la tabla no tiene una clave primaria única).
     */
    public function read($id) {
        return null; // no se puede implementar sin una PK única
    }

    /**
     * Leer todas las entradas de la bitácora
     * Incluye también el nombre del usuario mediante un JOIN.
     * Se ordena mostrando primero las sesiones abiertas (sin hora de salida).
     */
    public function readAll() {
        $sql = "SELECT b.*, u.Usuario AS nombreUsuario 
                FROM bitacora b 
                JOIN usuario u ON b.IdUsuario = u.Id
                ORDER BY 
                    CASE 
                        WHEN b.horaSalida IS NULL OR b.horaSalida = '00:00:00' THEN 0 
                        ELSE 1 
                    END,
                    b.Fecha DESC, 
                    b.HoraEntrada DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $bitacoras = [];
        foreach ($result as $row) {
            $bitacoras[] = BitacoraMapper::mapRowToDTO($row);
        }

        return $bitacoras;
    }

    /**
     * Actualizar la hora de salida
     * Se localiza el registro usando varias condiciones (usuario, fecha y hora de entrada).
     */
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

    /**
     * Leer todas las entradas asociadas a un usuario
     * Se devuelve una lista ordenada por fecha y hora de entrada.
     */
    public function readByUsuario($idUsuario) {
        $stmt = $this->conn->prepare("SELECT * FROM bitacora WHERE IdUsuario = ? ORDER BY Fecha DESC, HoraEntrada DESC");
        $stmt->execute([$idUsuario]);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = BitacoraMapper::mapRowToDTO($row);
        }
        return $result;
    }

    /**
     * Eliminar registros expirados
     * Borra registros cuya hora de salida esté definida y cuya fecha/hora sea mayor a 30 días.
     */
    public function eliminarExpirados() {
        $sql = "DELETE FROM bitacora 
                WHERE (horaSalida IS NOT NULL AND horaSalida != '00:00:00')
                AND CONCAT(fecha, ' ', horaEntrada) < NOW() - INTERVAL 30 DAY";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Verificar si el usuario ya tiene una sesión activa
     * Retorna true si el usuario tiene una entrada sin hora de salida registrada.
     */
    public function tieneSesionActiva($idUsuario) {
        $sql = "SELECT COUNT(*) FROM bitacora 
                WHERE IdUsuario = ? AND (horaSalida IS NULL OR horaSalida = '00:00:00')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Limpieza automática
     * Método estático que crea una nueva conexión y elimina registros expirados.
     */
    public static function ejecutarLimpiezaAutomatica() {
        $db = (new Database())->getConnection();
        $dao = new BitacoraDAO($db);
        return $dao->eliminarExpirados();
    }
}
?>
