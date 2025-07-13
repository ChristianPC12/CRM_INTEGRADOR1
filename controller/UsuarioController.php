<?php
header('Content-Type: application/json');

require_once __DIR__ . '/BitacoraController.php';
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

        // ====================== CREAR USUARIO ========================
        case 'create':
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $rol = trim($_POST['rol'] ?? '');

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

            // Crear usuario con contraseña encriptada
            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->usuario = $usuario;
            $usuarioDTO->contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
            $usuarioDTO->rol = $rol;

            $result = $dao->create($usuarioDTO);
            if ($result === true) {
                echo json_encode(['success' => true, 'message' => 'Usuario guardado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => $result]);
            }
            break;

        // ========================= LOGIN ============================
        case 'login':
            session_start(); // ¡IMPORTANTE para sesiones!
            $usuarioInput = trim($_POST['usuario'] ?? '');
            $contrasenaInput = $_POST['contrasena'] ?? '';

            if (empty($usuarioInput) || empty($contrasenaInput)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario y contraseña son requeridos'
                ]);
                break;
            }

            // Aquí sí traemos el hash
            $usuarios = $dao->readAll(false);
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

            if (password_verify($contrasenaInput, $usuarioEncontrado->contrasena)) {
                // GUARDAR SESIÓN
                $_SESSION['authenticated'] = true;
                $_SESSION['usuario'] = $usuarioEncontrado->usuario;
                $_SESSION['rol'] = $usuarioEncontrado->rol;
                $_SESSION['login_time'] = time();

                echo json_encode([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'usuario' => $usuarioEncontrado->usuario,
                    'rol' => $usuarioEncontrado->rol,
                    'redirect' => '/CRM_INT/CRM/index.php?view=dashboard'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ]);
            }
            break;

        // ========================= LEER TODOS ======================
        case 'readAll':
            $usuarios = $dao->readAll(true); // true = NO enviar hash
            echo json_encode(['success' => true, 'data' => $usuarios]);
            break;

        // ======================== LEER UNO ========================
        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                break;
            }
            $usuario = $dao->read($id);
            if ($usuario)
                unset($usuario->contrasena);
            echo json_encode(['success' => $usuario !== null, 'data' => $usuario]);
            break;

        // ========================= ACTUALIZAR ======================
        case 'update':
            $id = trim($_POST['id'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $rol = trim($_POST['rol'] ?? '');

            if ($id === '' || $usuario === '' || $rol === '') {
                echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
                break;
            }

            $usuarioDTO = new UsuarioDTO();
            $usuarioDTO->id = $id;
            $usuarioDTO->usuario = $usuario;
            if ($contrasena !== '') {
                $usuarioDTO->contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
            } else {
                // Traer la contraseña actual
                $usuarioActual = $dao->read($id, false);
                $usuarioDTO->contrasena = $usuarioActual ? $usuarioActual->contrasena : '';
            }
            $usuarioDTO->rol = $rol;
            $result = $dao->update($usuarioDTO);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Usuario actualizado correctamente.' : 'Error al actualizar usuario.'
            ]);
            break;

        // ========================== ELIMINAR =======================
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

        // ========================== DEFAULT =======================
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
