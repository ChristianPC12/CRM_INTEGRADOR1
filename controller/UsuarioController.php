<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../model/usuario/UsuarioDAO.php';
require_once __DIR__ . '/../model/usuario/UsuarioDTO.php';
require_once __DIR__ . '/../model/usuario/UsuarioMapper.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $dao = new UsuarioDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $rol = trim($_POST['rol'] ?? '');

            // Validación básica
            if ($usuario === '' || $contrasena === '' || $rol === '') {
                echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
                break;
            }
            if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).{6,}$/', $contrasena)) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener mínimo 6 caracteres, al menos 1 letra y 1 número.']);
                break;
            }

            // Validar usuario único
            $usuariosExistentes = $dao->readAll();
            $usuarioExiste = false;
            foreach ($usuariosExistentes as $u) {
                if (strtolower($u->usuario) === strtolower($usuario)) {
                    $usuarioExiste = true;
                    break;
                }
            }
            if ($usuarioExiste) {
                echo json_encode(['success' => false, 'message' => 'El usuario ya existe.']);
                break;
            }

            // Crear usuario
            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->id = uniqid(); // O puedes usar otro generador de ID si lo prefieres
            $usuarioDTO->usuario = $usuario;
            $usuarioDTO->contrasena = $contrasena;
            $usuarioDTO->rol = $rol;

            $result = $dao->create($usuarioDTO);
            if ($result === true) {
                echo json_encode(['success' => true, 'message' => 'Usuario guardado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => $result]);
            }
            break;

        case 'readAll':
            $usuarios = $dao->readAll();
            echo json_encode(['success' => true, 'data' => $usuarios]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                break;
            }
            $usuario = $dao->read($id);
            echo json_encode(['success' => $usuario !== null, 'data' => $usuario]);
            break;

        case 'update':
            $id = trim($_POST['id'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $rol = trim($_POST['rol'] ?? '');

            if ($id === '' || $usuario === '' || $rol === '') {
                echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
                break;
            }
            // Si se proporciona una nueva contraseña, validar y hashear
            $contrasenaHash = $contrasena;

            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->id = $id;
            $usuarioDTO->usuario = $usuario;
            $usuarioDTO->contrasena = $contrasenaHash;
            $usuarioDTO->rol = $rol;
            $result = $dao->update($usuarioDTO);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Usuario actualizado correctamente.' : 'Error al actualizar usuario.'
            ]);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                break;
            }
            $result = $dao->delete($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Usuario eliminado correctamente.' : 'Error al eliminar usuario.'
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
} 