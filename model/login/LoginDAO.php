<?php
// Archivo: model/login/LoginDAO.php

require_once 'LoginDTO.php';
require_once 'LoginMapper.php';

class LoginDAO
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($login)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioCreate(?, ?, ?, ?)");
            return $stmt->execute([
                $login->id,
                $login->usuario,
                $login->contrasena,
                $login->rol
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error PDO: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function read($id)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioRead(?)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? LoginMapper::mapRowToDTO($row) : null;
        } catch (PDOException $e) {
            error_log("Error al leer usuario: " . $e->getMessage());
            return null;
        }
    }

    public function readAll()
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioReadAll()");
            $stmt->execute();
            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = LoginMapper::mapRowToDTO($row);
            }
            return $usuarios;
        } catch (PDOException $e) {
            error_log("Error al leer todos los usuarios: " . $e->getMessage());
            return [];
        }
    }

    public function update($login)
    {
        try {
            $stmt = $this->conn->prepare("CALL UsuarioUpdate(?, ?, ?, ?)");
            return $stmt->execute([
                $login->id,
                $login->usuario,
                $login->contrasena,
                $login->rol
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
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
}
