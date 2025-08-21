<?php
// controller/CodigoController.php

// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Requerimientos de modelos y DAOs para códigos y clientes
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/codigo/CodigoDAO.php';
require_once __DIR__ . '/../model/codigo/CodigoDTO.php';
require_once __DIR__ . '/../model/codigo/CodigoMapper.php';
require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/cliente/ClienteDTO.php';
require_once __DIR__ . '/../model/cliente/ClienteMapper.php';

try {
    // Conexión a la base de datos
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    // Instancia DAO para códigos
    $dao = new CodigoDAO($db);
    // Determina la acción a ejecutar
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            // Crea un nuevo código de barras
            $codigo = new CodigoDTO();
            $codigo->idCliente = $_POST['idCliente'] ?? '';
            $codigo->codigoBarra = $_POST['codigoBarra'] ?? '';
            $codigo->cantImpresiones = $_POST['cantImpresiones'] ?? 0;

            $result = $dao->create($codigo);
            echo json_encode($result);
            break;

        case 'readAll':
            // Obtiene todos los códigos de barras y agrega info básica del cliente
            $result = $dao->readAll();
            if ($result['success']) {
                $clienteDAO = new ClienteDAO($db);
                foreach ($result['data'] as &$codigo) {
                    // Busca el cliente correspondiente al código
                    $cliente = $clienteDAO->read($codigo->idCliente);
                    if ($cliente && is_object($cliente)) {
                        // Si existe, agrega datos relevantes
                        $codigo->cedula = $cliente->cedula ?? '-';
                        $codigo->nombre = $cliente->nombre ?? '-';
                        $codigo->fechaRegistro = $cliente->fechaRegistro ?? '-';
                    } else {
                        // Si no existe, coloca valores por defecto
                        $codigo->cedula = '-';
                        $codigo->nombre = '-';
                        $codigo->fechaRegistro = '-';
                    }
                }
            }
            echo json_encode($result);
            break;

        case 'read':
            // Obtiene un código de barras por su ID
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->read($id);
            echo json_encode($result);
            break;

        case 'readByCliente':
            // Obtiene los códigos de barras asociados a un cliente
            $idCliente = $_POST['idCliente'] ?? $_GET['idCliente'] ?? '';
            $result = $dao->readByCliente($idCliente);
            echo json_encode($result);
            break;

        case 'update':
            // Actualiza los datos de un código de barras
            $codigo = new CodigoDTO();
            $codigo->id = $_POST['id'] ?? '';
            $codigo->codigoBarra = $_POST['codigoBarra'] ?? '';
            $codigo->cantImpresiones = $_POST['cantImpresiones'] ?? 0;

            $result = $dao->update($codigo);
            echo json_encode($result);
            break;

        case 'delete':
            // Elimina un código de barras por su ID
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode($result);
            break;

        case 'incrementarImpresiones':
            // Incrementa el contador de impresiones de un código de barras
            if (isset($_POST['idCliente'])) {
                $idCliente = $_POST['idCliente'];

                $codigoExistente = $codigoDAO->obtenerPorIdCliente($idCliente);

                if ($codigoExistente) {
                    $nuevaCantidad = $codigoExistente['cantImpresiones'] + 1;
                    $resultado = $codigoDAO->actualizarContadorImpresiones($idCliente, $nuevaCantidad);
                } else {
                    $codigo = new Codigo();
                    $codigo->setIdCliente($idCliente);
                    $codigo->setCodigoBarra($idCliente);
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
            // Acción no válida o no definida
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida o no definida en CodigoController'
            ]);
    }
} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
