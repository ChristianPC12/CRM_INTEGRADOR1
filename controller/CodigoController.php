<?php
// Archivo: controller/CodigoController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../model/codigo/CodigoDAO.php';
require_once __DIR__ . '/../model/codigo/CodigoDTO.php';
require_once __DIR__ . '/../model/codigo/CodigoMapper.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $dao = new CodigoDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $codigo = new CodigoDTO();
            $codigo->idCliente = $_POST['idCliente'] ?? '';
            $codigo->fechaRegistro = $_POST['fechaRegistro'] ?? '';
            $codigo->cantImpresiones = $_POST['cantImpresiones'] ?? 0;
            $result = $dao->create($codigo);
            echo json_encode(['success' => $result, 'message' => $result ? 'Tarjeta creada exitosamente' : 'Error al crear tarjeta']);
            break;

        case 'readAll':
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $codigo = $dao->read($id);
            echo json_encode(['success' => $codigo !== null, 'data' => $codigo]);
            break;

        case 'update':
            $codigo = new CodigoDTO();
            $codigo->id = $_POST['id'] ?? '';
            $codigo->idCliente = $_POST['idCliente'] ?? '';
            $codigo->fechaRegistro = $_POST['fechaRegistro'] ?? '';
            $codigo->cantImpresiones = $_POST['cantImpresiones'] ?? 0;
            $result = $dao->update($codigo);
            echo json_encode(['success' => $result, 'message' => $result ? 'Tarjeta actualizada exitosamente' : 'Error al actualizar tarjeta']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Tarjeta eliminada exitosamente' : 'Error al eliminar tarjeta']);
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
