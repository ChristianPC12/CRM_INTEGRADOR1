<?php
// Archivo: controller/CompraController.php

// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Requiere los modelos y DAOs necesarios para la gestión de compras
require_once __DIR__ . '/../model/compra/CompraDAO.php';
require_once __DIR__ . '/../model/compra/CompraDTO.php';
require_once __DIR__ . '/../model/compra/CompraMapper.php';
require_once __DIR__ . '/../config/Database.php';

try {
    // Conexión a la base de datos
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    // Instancia el DAO para compras
    $dao = new CompraDAO($db);
    // Determina la acción a ejecutar
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            // Crea una nueva compra con los datos recibidos
            $compra = new CompraDTO();
            $compra->fechaCompra = $_POST['fechaCompra'] ?? date('Y-m-d H:i:s');
            $compra->total = $_POST['total'] ?? 0;
            $compra->idCliente = $_POST['idCliente'] ?? null;
            $result = $dao->create($compra);
            echo json_encode(['success' => $result, 'message' => $result ? 'Compra registrada' : 'Error al registrar compra']);
            break;

        case 'read': // Buscar por IdCompra
            // Obtiene una compra por su ID
            $idCompra = $_POST['idCompra'] ?? $_GET['idCompra'] ?? '';
            $compra = $dao->read($idCompra);
            echo json_encode(['success' => $compra !== null, 'data' => $compra]);
            break;

        case 'delete':
            // Elimina una compra por su ID
            $idCompra = $_POST['idCompra'] ?? $_GET['idCompra'] ?? '';
            $result = $dao->delete($idCompra);
            echo json_encode(['success' => $result, 'message' => $result ? 'Compra eliminada' : 'Error al eliminar']);
            break;

        case 'readByCliente': // Historial de compras por cliente
            // Obtiene el historial de compras de un cliente
            $idCliente = $_POST['idCliente'] ?? $_GET['idCliente'] ?? '';
            $compras = $dao->readByCliente($idCliente);
            echo json_encode(['success' => true, 'data' => $compras]);
            break;

        case 'readAll':
            // Obtiene todas las compras registradas
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        default:
            // Acción no válida
            echo json_encode(['success' => false, 'message' => 'Acción no válida en CompraController']);
    }
} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
