<?php
header('Content-Type: application/json');
date_default_timezone_set("America/Costa_Rica");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/usuario/UsuarioDAO.php';
require_once __DIR__ . '/../model/usuario/UsuarioDTO.php';
require_once __DIR__ . '/../model/usuario/UsuarioMapper.php';
require_once __DIR__ . '/BitacoraController.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) throw new Exception("No se pudo conectar a la base de datos");

    $dao = new UsuarioDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        // Crear nuevo usuario
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

            foreach ($dao->readAll() as $u) {
                if (strtolower($u->usuario) === strtolower($usuario)) {
                    echo json_encode(['success' => false, 'message' => 'El usuario ya existe.']);
                    return;
                }
            }

            $dto = new UsuarioDTO();
            $dto->usuario = $usuario;
            $dto->contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
            $dto->rol = $rol;

            $ok = $dao->create($dto);
            echo json_encode([
                'success' => $ok === true,
                'message' => $ok ? 'Usuario guardado correctamente.' : $ok
            ]);
            return;

        // Iniciar sesión
        case 'login':
            session_start();
            $usuarioInput = trim($_POST['usuario'] ?? '');
            $contrasenaInput = $_POST['contrasena'] ?? '';

            if ($usuarioInput === '' || $contrasenaInput === '') {
                echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
                return;
            }

            $usuarioEncontrado = null;
            foreach ($dao->readAll(false) as $u) {
                if ($u->usuario === $usuarioInput) {
                    $usuarioEncontrado = $u;
                    break;
                }
            }

            if (!$usuarioEncontrado) {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
                return;
            }

            if (!password_verify($contrasenaInput, $usuarioEncontrado->contrasena)) {
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
                return;
            }

            $_SESSION['authenticated'] = true;
            $_SESSION['usuario'] = $usuarioEncontrado->usuario;
            $_SESSION['rol'] = $usuarioEncontrado->rol;
            $_SESSION['idUsuario'] = $usuarioEncontrado->id;
            $_SESSION['login_time'] = time();
          (new BitacoraController())->registrarEntrada($usuarioEncontrado->id);
          
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'usuario' => $usuarioEncontrado->nombre,
                'rol' => $usuarioEncontrado->rol,
                'redirect' => '/CRM_INT/CRM/index.php?view=dashboard'
            ]);
            return;

        // Leer todos los usuarios (sin contraseñas)
        case 'readAll':
            $usuarios = $dao->readAll(true);
            echo json_encode(['success' => true, 'data' => $usuarios]);
            return;

        // Leer un usuario por ID
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

        // Actualizar un usuario
        case 'update':
            $id = trim($_POST['id'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $rol = trim($_POST['rol'] ?? '');

            if ($id === '' || $usuario === '' || $rol === '') {
                echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
                return;
            }

            $dto = new UsuarioDTO();
            $dto->id = $id;
            $dto->usuario = $usuario;
            $dto->rol = $rol;
            $dto->contrasena = $contrasena !== ''
                ? password_hash($contrasena, PASSWORD_DEFAULT)
                : ($dao->read($id, false)->contrasena ?? '');

            $ok = $dao->update($dto);
            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Usuario actualizado correctamente.' : 'Error al actualizar usuario.'
            ]);
            return;

        // Eliminar usuario
        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                return;
            }

            $ok = $dao->delete($id);
            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Usuario eliminado correctamente.' : 'Error al eliminar usuario.'
            ]);
            return;

            
        // Cerrar sesión y registrar salida
        case 'logout':
            session_start();
            if (isset($_SESSION['idUsuario'])) {
                (new BitacoraController())->registrarSalida($_SESSION['idUsuario']);
            }
            session_destroy();

            // Si es una petición Beacon (al cerrar pestaña), no redireccionamos
            if (!isset($_SERVER['HTTP_SEC_FETCH_MODE']) || $_SERVER['HTTP_SEC_FETCH_MODE'] !== 'no-cors') {
                header('Location: /CRM_INT/CRM/index.php');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Sesión cerrada correctamente.'
            ]);
            return;

        // Acción no válida
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
