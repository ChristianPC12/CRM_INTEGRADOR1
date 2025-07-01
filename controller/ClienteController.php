<?php
// Archivo: controller/ClienteController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/cliente/ClienteDTO.php';
require_once __DIR__ . '/../model/cliente/ClienteMapper.php';
require_once __DIR__ . '/../config/Database.php';

// Importar modelo de código para la creación automática
require_once __DIR__ . '/../model/codigo/CodigoDAO.php';
require_once __DIR__ . '/../model/codigo/CodigoDTO.php';

/**
 * Genera un valor único para el código de barras.
 * Personaliza esta lógica según tu necesidad.
 */
function generarCodigoBarra($cliente) {
    // Ejemplo: cédula + fecha y hora actual para unicidad
    return $cliente->cedula . date('YmdHis');
}

try {
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $dao = new ClienteDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $cliente = new ClienteDTO();
            $cliente->cedula = $_POST['cedula'] ?? '';
            $cliente->nombre = $_POST['nombre'] ?? '';
            $cliente->correo = $_POST['correo'] ?? '';
            $cliente->telefono = $_POST['telefono'] ?? '';
            $cliente->lugarResidencia = $_POST['lugarResidencia'] ?? '';
            $cliente->fechaCumpleanos = $_POST['fechaCumpleanos'] ?? '';
            $cliente->acumulado = $_POST['acumulado'] ?? 0;
            $result = $dao->create($cliente);

            if ($result) {
                // Obtener el último ID insertado para cliente
                $idCliente = $db->lastInsertId();

                // Crear automáticamente el código de barras asociado a este cliente
                $codigoDAO = new CodigoDAO($db);
                $codigoDTO = new CodigoDTO();
                $codigoDTO->idCliente = $idCliente;
                $codigoDTO->codigoBarra = generarCodigoBarra($cliente);
                $codigoDTO->cantImpresiones = 0;
                $codigoDAO->create($codigoDTO);
            }

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Cliente creado exitosamente' : 'Error al crear cliente'
            ]);
            break;

        case 'readAll':
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $cliente = $dao->read($id);
            echo json_encode(['success' => $cliente !== null, 'data' => $cliente]);
            break;

        case 'update':
            $cliente = new ClienteDTO();
            $cliente->id = $_POST['id'] ?? '';
            $cliente->cedula = $_POST['cedula'] ?? '';
            $cliente->nombre = $_POST['nombre'] ?? '';
            $cliente->correo = $_POST['correo'] ?? '';
            $cliente->telefono = $_POST['telefono'] ?? '';
            $cliente->lugarResidencia = $_POST['lugarResidencia'] ?? '';
            $cliente->fechaCumpleanos = $_POST['fechaCumpleanos'] ?? '';
            $cliente->acumulado = $_POST['acumulado'] ?? null;
            $result = $dao->update($cliente);
            echo json_encode(['success' => $result, 'message' => $result ? 'Cliente actualizado exitosamente' : 'Error al actualizar cliente']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Cliente eliminado exitosamente' : 'Error al eliminar cliente']);
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
