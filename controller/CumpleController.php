<?php
require_once '../config/Database.php';
require_once '../model/cumple/CumpleDAO.php';
require_once '../model/cumple/CumpleDTO.php';
require_once '../model/cumple/CumpleMapper.php';

$action = $_POST['action'] ?? '';

try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $database = new Database(); // CORRECTO
    $conn = $database->getConnection(); // CORRECTO
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
