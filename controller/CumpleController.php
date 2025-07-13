<?php
require_once '../config/Database.php';
require_once '../model/cumple/CumpleDAO.php';
require_once '../model/cumple/CumpleDTO.php';
require_once '../model/cumple/CumpleMapper.php';
require_once '../config/CorreoHelper.php';

$action = $_POST['action'] ?? '';

try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $database = new Database();
    $conn = $database->getConnection();
    $dao = new CumpleDAO($conn);

    switch ($action) {
        case 'readSemana':
            $cumples = $dao->obtenerCumplesSemana();
            echo json_encode([
                'success' => true,
                'data' => $cumples
            ]);
            break;

        case 'cambiarEstado':
            $id = $_POST['id'] ?? null;
            $nuevoEstado = $_POST['estado'] ?? null;

            if ($id === null || $nuevoEstado === null) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos incompletos'
                ]);
                break;
            }

            $resultado = $dao->actualizarEstado($id, $nuevoEstado);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Estado actualizado' : 'Error al actualizar el estado'
            ]);
            break;

        case 'marcarComoEnviado':
            $id = $_POST['id'] ?? null;

            if ($id === null) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID no proporcionado'
                ]);
                break;
            }

            $resultado = $dao->actualizarEstado($id, 'LISTA');
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Estado cambiado a LISTA' : 'Error al actualizar'
            ]);
            break;

        case 'enviarCorreoCumple':
            $correo = $_POST['correo'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $mensaje = $_POST['mensaje'] ?? '';

            if (empty($correo) || empty($nombre) || empty($mensaje)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos incompletos para enviar el correo'
                ]);
                break;
            }

            $enviado = enviarCorreoCumple($correo, $nombre, $mensaje);

            echo json_encode([
                'success' => $enviado,
                'message' => $enviado ? 'Correo enviado correctamente' : 'Error al enviar el correo'
            ]);
            break;

        case 'hayPendientes':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM cliente WHERE Estado = 'PENDIENTE'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hayPendientes = isset($row['total']) && $row['total'] > 0;
            echo json_encode([
                'success' => true,
                'hayPendientes' => $hayPendientes,
                'total' => (int)($row['total'] ?? 0)
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage()
    ]);
}
