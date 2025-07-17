<?php
// Archivo: controller/SessionController.php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDTO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraMapper.php';
require_once __DIR__ . '/BitacoraController.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $action = $data['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
    $response = [];

    switch ($action) {

        case 'login':
            $idUsuario = $data['idUsuario'] ?? null;

            if ($idUsuario) {
                $_SESSION['authenticated'] = true;
                $_SESSION['idUsuario'] = $idUsuario;

                // Registrar entrada en bitácora
                $bitacora = new BitacoraController();
              $bitacora->registrarEntrada($idUsuario);


                $response = ['success' => true, 'message' => 'Sesión iniciada'];
            } else {
                $response = ['success' => false, 'message' => 'ID de usuario requerido'];
            }
            break;

        case 'close_session':
            if (isset($_SESSION['idUsuario'])) {
                $bitacora = new BitacoraController();
                $bitacora->registrarSalida($_SESSION['idUsuario']);

            }

            //  Destruir sesión correctamente
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
            $active = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
            $response = ['success' => true, 'active' => $active];
            break;

        default:
            $response = ['success' => false, 'message' => 'Acción no válida'];
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
