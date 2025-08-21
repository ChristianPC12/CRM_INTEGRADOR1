<?php
// Archivo: model.usuario/UsuarioDAO.php
require_once 'UsuarioDTO.php';
require_once 'UsuarioMapper.php';

/**
 * Clase UsuarioDAO
 *
 * Objeto de Acceso a Datos (DAO) para la entidad Usuario.
 * Gestiona las operaciones CRUD sobre la base de datos utilizando
 * procedimientos almacenados (SP).
 */
class UsuarioDAO
{
    // Conexión a la base de datos
    private $conn;

    /**
     * Constructor
     * @param PDO $db Conexión PDO activa
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener todos los usuarios
     *
     * @param bool $omitPassword True (por defecto) para ocultar el hash de contraseña,
     *                           False para incluirlo (ej. login).
     * @return UsuarioDTO[] Lista de usuarios
     */
    public function readAll($omitPassword = true)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioReadAll()");
            $stmt->execute();
            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = UsuarioMapper::mapRowToDTO($row, $omitPassword);
            }
            return $usuarios;
        } catch (PDOException $e) {
            error_log("Error al leer todos los usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear un nuevo usuario
     *
     * @param UsuarioDTO $usuario Objeto con los datos del usuario
     * @return bool|string True si se creó, mensaje de error si falla
     */
    public function create($usuario)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioCreate(?, ?, ?)");
            return $stmt->execute([
                $usuario->usuario,
                $usuario->contrasena,
                $usuario->rol
            ]);
        } catch (PDOException $e) {
            return "Error PDO: " . $e->getMessage();
        }
    }

    /**
     * Eliminar un usuario por su ID
     */
    public function delete($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioDelete(?)");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener un usuario por su ID
     *
     * @param int $id Identificador del usuario
     * @param bool $omitPassword True para no incluir contraseña (default),
     *                           False para incluirla.
     * @return UsuarioDTO|null Usuario encontrado o null si no existe
     */
    public function read($id, $omitPassword = true)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioRead(?)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? UsuarioMapper::mapRowToDTO($row, $omitPassword) : null;
        } catch (PDOException $e) {
            error_log("Error al leer usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar un usuario existente
     *
     * @param UsuarioDTO $usuario Objeto con los datos actualizados
     * @return bool True si se actualizó correctamente
     */
    public function update($usuario)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioUpdate(?, ?, ?, ?)");
            return $stmt->execute([
                $usuario->id,
                $usuario->usuario,
                $usuario->contrasena,
                $usuario->rol
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }
}
