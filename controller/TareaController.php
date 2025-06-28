<?php
// Archivo: controller/TareaController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../model/tarea/TareaDAO.php';
require_once __DIR__ . '/../model/tarea/TareaDTO.php';
require_once __DIR__ . '/../model/tarea/TareaMapper.php';
require_once __DIR__ . '/../config/Database.php';

// ✅ Detectar si el POST llega como JSON puro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $input = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $_POST = $input;
    }
}

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $dao = new TareaDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $descripcion = $_POST['descripcion'] ?? '';
            if (mb_strlen($descripcion, 'UTF-8') > 220) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La descripción supera el límite de 220 caracteres.'
                ]);
                exit;
            }

            $tarea = new TareaDTO();
            $tarea->descripcion = $descripcion;
            $tarea->estado = $_POST['estado'] ?? 'Pendiente';
            $tarea->fechaCreacion = date('Y-m-d H:i:s');

            $result = $dao->create($tarea);

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Tarea creada' : 'Error al crear tarea'
            ]);
            break;

        case 'readAll':
            echo json_encode([
                'success' => true,
                'data' => $dao->readAll()
            ]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $tarea = $dao->read($id);
            echo json_encode([
                'success' => $tarea !== null,
                'data' => $tarea
            ]);
            break;

        case 'update':
            $id = $_POST['id'] ?? '';
            $estado = $_POST['estado'] ?? '';
            $result = $dao->update($id, $estado);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Tarea actualizada' : 'Error al actualizar tarea'
            ]);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Tarea eliminada' : 'Error al eliminar tarea'
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
