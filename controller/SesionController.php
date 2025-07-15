<?php
// Archivo: controller/SessionController.php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDTO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraMapper.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $action = $data['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

    $db = (new Database())->getConnection();
    $bitacoraDAO = new BitacoraDAO($db);

    switch ($action) {

        case 'login':
            // Simulación: validar usuario y contraseña correctamente (debes usar tu lógica real aquí)
            $idUsuario = $data['idUsuario'] ?? null;

            if ($idUsuario) {
                $_SESSION['authenticated'] = true;
                $_SESSION['idUsuario'] = $idUsuario;

                // Registrar entrada en bitácora
                $bitacora = new BitacoraDTO();
                $bitacora->idUsuario = $idUsuario;
                $bitacora->horaEntrada = date('H:i:s');
                $bitacora->horaSalida = null;
                $bitacora->fecha = date('Y-m-d');

                $bitacoraDAO->create($bitacora);

                echo json_encode(['success' => true, 'message' => 'Sesión iniciada']);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
            }
            break;

        case 'close_session':
            // Registrar hora de salida
            if (isset($_SESSION['idUsuario'])) {
                $idUsuario = $_SESSION['idUsuario'];

                // Buscar último registro sin salida
                $stmt = $db->prepare("SELECT Id FROM bitacora WHERE IdUsuario = ? AND HoraSalida IS NULL ORDER BY Id DESC LIMIT 1");
                $stmt->execute([$idUsuario]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $bitacora = new BitacoraDTO();
                    $bitacora->id = $row['Id'];
                    $bitacora->idUsuario = $idUsuario;
                    $bitacora->horaEntrada = null; // No se actualiza
                    $bitacora->horaSalida = date('H:i:s');
                    $bitacora->fecha = date('Y-m-d');

                    $bitacoraDAO->update($bitacora);
                }
            }

            // Destruir sesión
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
