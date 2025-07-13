<?php
// Archivo: controller/SessionController.php (MODIFICADO)

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../controller/BitacoraController.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $action = $data['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'close_session':
            // Registrar salida en bitácora antes de destruir la sesión
            if (isset($_SESSION['idUsuario'])) {
                $bitacoraController = new BitacoraController();
                $bitacoraController->registrarSalida($_SESSION['idUsuario']);
            }

            // Destruir la sesión completamente
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

            echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
            break;

        case 'check_session':
            $active = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
            echo json_encode(['success' => true, 'active' => $active]);
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
