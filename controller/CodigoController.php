<?php
// controller/CodigoController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/codigo/CodigoDAO.php';
require_once __DIR__ . '/../model/codigo/CodigoDTO.php';
require_once __DIR__ . '/../model/codigo/CodigoMapper.php';

// NUEVO: Incluye también los modelos de cliente para hacer el join manual
require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/cliente/ClienteDTO.php';
require_once __DIR__ . '/../model/cliente/ClienteMapper.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    $dao = new CodigoDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $codigo = new CodigoDTO();
            $codigo->idCliente = $_POST['idCliente'] ?? '';
            $codigo->codigoBarra = $_POST['codigoBarra'] ?? '';
            $codigo->cantImpresiones = $_POST['cantImpresiones'] ?? 0;

            $result = $dao->create($codigo);
            echo json_encode($result);
            break;

        case 'readAll':
            $result = $dao->readAll();
            // INICIO DEL JOIN MANUAL
            if ($result['success']) {
                $clienteDAO = new ClienteDAO($db);
                foreach ($result['data'] as &$codigo) {
                    $cliente = $clienteDAO->read($codigo->idCliente);
                    if ($cliente && is_object($cliente)) {
                        $codigo->cedula = $cliente->cedula ?? '-';
                        $codigo->nombre = $cliente->nombre ?? '-';
                        $codigo->fechaRegistro = $cliente->fechaRegistro ?? '-';
                    } else {
                        $codigo->cedula = '-';
                        $codigo->nombre = '-';
                        $codigo->fechaRegistro = '-';
                    }
                }
            }
            // FIN DEL JOIN MANUAL
            echo json_encode($result);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->read($id);
            echo json_encode($result);
            break;

        case 'readByCliente':
            $idCliente = $_POST['idCliente'] ?? $_GET['idCliente'] ?? '';
            $result = $dao->readByCliente($idCliente);
            echo json_encode($result);
            break;

        case 'update':
            $codigo = new CodigoDTO();
            $codigo->id = $_POST['id'] ?? '';
            $codigo->codigoBarra = $_POST['codigoBarra'] ?? '';
            $codigo->cantImpresiones = $_POST['cantImpresiones'] ?? 0;

            $result = $dao->update($codigo);
            echo json_encode($result);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode($result);
            break;

        case 'incrementarImpresiones':
            if (isset($_POST['idCliente'])) {
                $idCliente = $_POST['idCliente'];

                // Verificar si ya existe el registro
                $codigoExistente = $codigoDAO->obtenerPorIdCliente($idCliente);

                if ($codigoExistente) {
                    // Incrementar contador existente
                    $nuevaCantidad = $codigoExistente['cantImpresiones'] + 1;
                    $resultado = $codigoDAO->actualizarContadorImpresiones($idCliente, $nuevaCantidad);
                } else {
                    // Crear nuevo registro con contador en 1
                    $codigo = new Codigo();
                    $codigo->setIdCliente($idCliente);
                    $codigo->setCodigoBarra($idCliente); // o generar código único
                    $codigo->setCantImpresiones(1);
                    $resultado = $codigoDAO->create($codigo);
                }

                if ($resultado) {
                    echo json_encode(['success' => true, 'message' => 'Contador actualizado']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar contador']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de cliente requerido']);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida o no definida en CodigoController'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
