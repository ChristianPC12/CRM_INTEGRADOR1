<?php
require_once 'UsuarioDTO.php';
require_once 'UsuarioMapper.php';

class UsuarioDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAll()
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioReadAll()");
            $stmt->execute();
            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = UsuarioMapper::mapRowToDTO($row);
            }
            return $usuarios;
        } catch (PDOException $e) {
            error_log("Error al leer todos los usuarios: " . $e->getMessage());
            return [];
        }
    }

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

    public function read($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioRead(?)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? UsuarioMapper::mapRowToDTO($row) : null;
        } catch (PDOException $e) {
            error_log("Error al leer usuario: " . $e->getMessage());
            return null;
        }
    }

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
