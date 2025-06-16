<?php
// Archivo: controller/TareaController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../model/tarea/TareaDAO.php';
require_once __DIR__ . '/../model/tarea/TareaDTO.php';
require_once __DIR__ . '/../model/tarea/TareaMapper.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $dao = new TareaDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $tarea = new TareaDTO();
            $tarea->descripcion = $_POST['descripcion'] ?? '';
            $tarea->estado = $_POST['estado'] ?? 'Pendiente';
            $tarea->fechaCreacion = date('Y-m-d H:i:s');
            $result = $dao->create($tarea);
            echo json_encode(['success' => $result, 'message' => $result ? 'Tarea creada' : 'Error al crear tarea']);
            break;

        case 'readAll':
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $tarea = $dao->read($id);
            echo json_encode(['success' => $tarea !== null, 'data' => $tarea]);
            break;

        case 'update':
            $tarea = new TareaDTO();
            $tarea->id = $_POST['id'] ?? '';
            $tarea->descripcion = $_POST['descripcion'] ?? '';
            $tarea->estado = $_POST['estado'] ?? 'Pendiente';
            $result = $dao->update($tarea);
            echo json_encode(['success' => $result, 'message' => $result ? 'Tarea actualizada' : 'Error al actualizar tarea']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Tarea eliminada' : 'Error al eliminar tarea']);
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
