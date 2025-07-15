<?php
header('Content-Type: application/json');
date_default_timezone_set("America/Costa_Rica");

require_once __DIR__ . '/../model/usuario/UsuarioDAO.php';
require_once __DIR__ . '/../model/usuario/UsuarioDTO.php';
require_once __DIR__ . '/../model/usuario/UsuarioMapper.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/BitacoraController.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) throw new Exception("No se pudo conectar a la base de datos");

    $dao = new UsuarioDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $rol = trim($_POST['rol'] ?? '');

            if ($usuario === '' || $contrasena === '' || $rol === '') {
                echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
                return;
            }

            if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).{6,}$/', $contrasena)) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener mínimo 6 caracteres, al menos 1 letra y 1 número.']);
                return;
            }

            $usuariosExistentes = $dao->readAll();
            foreach ($usuariosExistentes as $u) {
                if (strtolower($u->usuario) === strtolower($usuario)) {
                    echo json_encode(['success' => false, 'message' => 'El usuario ya existe.']);
                    return;
                }
            }

            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->usuario = $usuario;
            $usuarioDTO->contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
            $usuarioDTO->rol = $rol;

            $result = $dao->create($usuarioDTO);
            echo json_encode([
                'success' => $result === true,
                'message' => $result ? 'Usuario guardado correctamente.' : $result
            ]);
            return;

        case 'login':
            session_start();
            $usuarioInput = trim($_POST['usuario'] ?? '');
            $contrasenaInput = $_POST['contrasena'] ?? '';

            if (empty($usuarioInput) || empty($contrasenaInput)) {
                echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
                return;
            }

            $usuarios = $dao->readAll(false);
            $usuarioEncontrado = null;
            foreach ($usuarios as $u) {
                if ($u->usuario === $usuarioInput) {
                    $usuarioEncontrado = $u;
                    break;
                }
            }

            if (!$usuarioEncontrado) {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
                return;
            }

            if (password_verify($contrasenaInput, $usuarioEncontrado->contrasena)) {
                $_SESSION['authenticated'] = true;
                $_SESSION['usuario'] = $usuarioEncontrado->usuario;
                $_SESSION['rol'] = $usuarioEncontrado->rol;
                $_SESSION['idUsuario'] = $usuarioEncontrado->id;
                $_SESSION['login_time'] = time();

                $bitacora = new BitacoraController();
                $bitacora->registrarEntradaInterna($usuarioEncontrado->id);

                echo json_encode([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'usuario' => $usuarioEncontrado->usuario,
                    'rol' => $usuarioEncontrado->rol,
                    'redirect' => '/CRM_INT/CRM/index.php?view=dashboard'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
            }
            return;

        case 'readAll':
            $usuarios = $dao->readAll(true);
            echo json_encode(['success' => true, 'data' => $usuarios]);
            return;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                return;
            }
            $usuario = $dao->read($id);
            if ($usuario) unset($usuario->contrasena);
            echo json_encode(['success' => $usuario !== null, 'data' => $usuario]);
            return;

        case 'update':
            $id = trim($_POST['id'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $rol = trim($_POST['rol'] ?? '');

            if ($id === '' || $usuario === '' || $rol === '') {
                echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
                return;
            }

            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->id = $id;
            $usuarioDTO->usuario = $usuario;
            $usuarioDTO->rol = $rol;

            if ($contrasena !== '') {
                $usuarioDTO->contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
            } else {
                $actual = $dao->read($id, false);
                $usuarioDTO->contrasena = $actual ? $actual->contrasena : '';
            }

            $result = $dao->update($usuarioDTO);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Usuario actualizado correctamente.' : 'Error al actualizar usuario.'
            ]);
            return;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                return;
            }
            $result = $dao->delete($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Usuario eliminado correctamente.' : 'Error al eliminar usuario.'
            ]);
            return;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            return;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
