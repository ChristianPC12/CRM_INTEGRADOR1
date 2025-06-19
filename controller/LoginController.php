<?php
// Archivo: controller/LoginController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../model/login/LoginDAO.php';
require_once __DIR__ . '/../model/login/LoginDTO.php';
require_once __DIR__ . '/../model/login/LoginMapper.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $dao = new LoginDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $usuario = new LoginDTO();
            $usuario->usuario = $_POST['usuario'] ?? '';
            $usuario->contrasena = password_hash($_POST['contrasena'] ?? '', PASSWORD_DEFAULT);
            $usuario->rol = $_POST['rol'] ?? '';
            $result = $dao->create($usuario);
            echo json_encode(['success' => $result, 'message' => $result ? 'Usuario creado exitosamente' : 'Error al crear usuario']);
            break;

        case 'readAll':
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $usuario = $dao->read($id);
            echo json_encode(['success' => $usuario !== null, 'data' => $usuario]);
            break;

        case 'update':
            $usuario = new LoginDTO();
            $usuario->id = $_POST['id'] ?? '';
            $usuario->usuario = $_POST['usuario'] ?? '';
            $usuario->contrasena = password_hash($_POST['contrasena'] ?? '', PASSWORD_DEFAULT);
            $usuario->rol = $_POST['rol'] ?? '';
            $result = $dao->update($usuario);
            echo json_encode(['success' => $result, 'message' => $result ? 'Usuario actualizado exitosamente' : 'Error al actualizar usuario']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Usuario eliminado exitosamente' : 'Error al eliminar usuario']);
            break;

        case 'login':
            $usuarioInput = trim($_POST['usuario'] ?? '');
            $contrasenaInput = $_POST['contrasena'] ?? '';

            if (empty($usuarioInput) || empty($contrasenaInput)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario y contraseña son requeridos'
                ]);
                break;
            }

            $usuarios = $dao->readAll();
            $usuarioEncontrado = null;

            foreach ($usuarios as $u) {
                if ($u->usuario === $usuarioInput) {
                    $usuarioEncontrado = $u;
                    break;
                }
            }

            if (!$usuarioEncontrado) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
                break;
            }

            // Verificar contraseña - maneja tanto texto plano como hash
            $contrasenaValida = false;

            // Si la contraseña en BD empieza con $2y$ es un hash de password_hash()
            if (strpos($usuarioEncontrado->contrasena, '$2y$') === 0) {
                // Es un hash, usar password_verify
                $contrasenaValida = password_verify($contrasenaInput, $usuarioEncontrado->contrasena);
            } else {
                // Es texto plano, comparar directamente
                $contrasenaValida = ($contrasenaInput === $usuarioEncontrado->contrasena);
            }

            if ($contrasenaValida) {
                // Iniciar sesión
                session_start();

                // Establecer todas las variables de sesión necesarias
                $_SESSION['usuario_id'] = $usuarioEncontrado->id;
                $_SESSION['usuario'] = $usuarioEncontrado->usuario;
                $_SESSION['rol'] = $usuarioEncontrado->rol;
                $_SESSION['authenticated'] = true; // Variable clave para el index.php
                $_SESSION['login_time'] = time();

                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);

                echo json_encode([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'usuario' => $usuarioEncontrado->usuario,
                    'rol' => $usuarioEncontrado->rol,
                    'redirect' => '/CRM_INT/CRM/index.php?view=dashboard' // URL de redirección
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    error_log("Error en LoginController: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>