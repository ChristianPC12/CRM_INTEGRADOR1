<?php
// Controlador de tareas: gestiona la creación, consulta, actualización y eliminación de tareas.

header('Content-Type: application/json'); // Responde siempre en formato JSON

// Importa clases necesarias para el manejo de tareas y la base de datos
require_once __DIR__ . '/../model/tarea/TareaDAO.php';
require_once __DIR__ . '/../model/tarea/TareaDTO.php';
require_once __DIR__ . '/../model/tarea/TareaMapper.php';
require_once __DIR__ . '/../config/Database.php';

// Detecta si la petición POST llega como JSON puro y la decodifica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $input = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $_POST = $input; // Sobrescribe $_POST con los datos decodificados
    }
}

try {
    // Obtiene la conexión a la base de datos
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    // Instancia el DAO de tareas
    $dao = new TareaDAO($db);
    // Determina la acción a ejecutar (puede venir por POST o GET)
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            // Crear una nueva tarea
            $descripcion = $_POST['descripcion'] ?? '';
            // Valida longitud máxima de la descripción
            if (mb_strlen($descripcion, 'UTF-8') > 220) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La descripción supera el límite de 220 caracteres.'
                ]);
                exit;
            }

            // Crea el DTO de tarea y asigna valores
            $tarea = new TareaDTO();
            $tarea->descripcion = $descripcion;
            $tarea->estado = $_POST['estado'] ?? 'Pendiente';
            $tarea->fechaCreacion = date('Y-m-d H:i:s'); // Fecha actual

            // Inserta la tarea en la base de datos
            $result = $dao->create($tarea);

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Tarea creada' : 'Error al crear tarea'
            ]);
            break;

        case 'readAll':
            // Devuelve todas las tareas
            echo json_encode([
                'success' => true,
                'data' => $dao->readAll()
            ]);
            break;

        case 'read':
            // Devuelve una tarea específica por ID
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $tarea = $dao->read($id);
            echo json_encode([
                'success' => $tarea !== null,
                'data' => $tarea
            ]);
            break;

        case 'update':
            // Actualiza el estado de una tarea
            $id = $_POST['id'] ?? '';
            $estado = $_POST['estado'] ?? '';
            $result = $dao->update($id, $estado);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Tarea actualizada' : 'Error al actualizar tarea'
            ]);
            break;

        case 'delete':
            // Elimina una tarea por ID
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Tarea eliminada' : 'Error al eliminar tarea'
            ]);
            break;

        default:
            // Acción no reconocida
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    // Manejo de errores generales: responde con mensaje de error
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
