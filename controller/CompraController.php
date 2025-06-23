<?php
// Archivo: controller/CompraController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../model/compra/CompraDAO.php';
require_once __DIR__ . '/../model/compra/CompraDTO.php';
require_once __DIR__ . '/../model/compra/CompraMapper.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $dao = new CompraDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $compra = new CompraDTO();
            $compra->fechaCompra = $_POST['fechaCompra'] ?? date('Y-m-d');
            $compra->total = $_POST['total'] ?? 0;
            $compra->idCliente = $_POST['idCliente'] ?? null;
            $result = $dao->create($compra);
            echo json_encode(['success' => $result, 'message' => $result ? 'Compra registrada' : 'Error al registrar compra']);
            break;

        case 'read': // Buscar por IdCompra
            $idCompra = $_POST['idCompra'] ?? $_GET['idCompra'] ?? '';
            $compra = $dao->read($idCompra);
            echo json_encode(['success' => $compra !== null, 'data' => $compra]);
            break;

        case 'delete':
            $idCompra = $_POST['idCompra'] ?? $_GET['idCompra'] ?? '';
            $result = $dao->delete($idCompra);
            echo json_encode(['success' => $result, 'message' => $result ? 'Compra eliminada' : 'Error al eliminar']);
            break;

        case 'readByCliente': // Historial de compras por cliente
            $idCliente = $_POST['idCliente'] ?? $_GET['idCliente'] ?? '';
            $compras = $dao->readByCliente($idCliente);
            echo json_encode(['success' => true, 'data' => $compras]);
            break;

        case 'readAll':
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida en CompraController']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
