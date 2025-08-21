<?php
// Controlador de sesión: gestiona login, cierre y verificación de sesión de usuario.

session_start(); // Inicia o reanuda la sesión PHP
header('Content-Type: application/json'); // Responde siempre en formato JSON

// Importa dependencias necesarias para bitácora y base de datos
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDTO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraMapper.php';
require_once __DIR__ . '/BitacoraController.php';

try {
    // Lee el cuerpo de la petición (JSON) y lo decodifica
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Determina la acción a ejecutar (puede venir por JSON, POST o GET)
    $action = $data['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
    $response = [];

    switch ($action) {

        case 'login':
            // Iniciar sesión: requiere idUsuario
            $idUsuario = $data['idUsuario'] ?? null;

            if ($idUsuario) {
                // Marca la sesión como autenticada y guarda el idUsuario
                $_SESSION['authenticated'] = true;
                $_SESSION['idUsuario'] = $idUsuario;

                // Registra la entrada del usuario en la bitácora
                $bitacora = new BitacoraController();
                $bitacora->registrarEntrada($idUsuario);

                $response = ['success' => true, 'message' => 'Sesión iniciada'];
            } else {
                // Si falta el idUsuario, responde con error
                $response = ['success' => false, 'message' => 'ID de usuario requerido'];
            }
            break;

        case 'close_session':
            // Cerrar sesión: registra salida en bitácora si hay usuario
            if (isset($_SESSION['idUsuario'])) {
                $bitacora = new BitacoraController();
                $bitacora->registrarSalida($_SESSION['idUsuario']);
            }

            // Destruye la sesión y elimina cookies asociadas
            session_unset();
            session_destroy();

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            $response = ['success' => true, 'message' => 'Sesión cerrada'];
            break;

        case 'check_session':
            // Verifica si la sesión está activa y autenticada
            $active = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
            $response = ['success' => true, 'active' => $active];
            break;

        default:
            // Acción no reconocida
            $response = ['success' => false, 'message' => 'Acción no válida'];
    }

    // Devuelve la respuesta en formato JSON
    echo json_encode($response);
} catch (Exception $e) {
    // Manejo de errores generales: responde con mensaje de error
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
