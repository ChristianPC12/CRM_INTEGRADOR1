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


function generarCodigoBarra($cliente)
{
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
            $cliente->alergias = $_POST['alergias'] ?? '';
            $cliente->gustosEspeciales = $_POST['gustosEspeciales'] ?? '';

            if ($dao->existeTelefonoOCorreo($cliente->telefono, $cliente->correo)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono o correo ya están registrados para otro cliente.'
                ]);
                exit;
            }
            if ($dao->existeCedula($cliente->cedula, null)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La cédula ya está registrada.'
                ]);
                exit;
            }
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
            if ($dao->existeTelefonoOCorreo($cliente->telefono, $cliente->correo, $cliente->id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono o correo ya están registrados para otro cliente.'
                ]);
                exit;
            }
            if ($dao->existeCedula($cliente->cedula, $cliente->id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La cédula ya está registrada.'
                ]);
                exit;
            }
            $result = $dao->update($cliente);
            echo json_encode(['success' => $result, 'message' => $result ? 'Cliente actualizado exitosamente' : 'Error al actualizar cliente']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Cliente eliminado exitosamente' : 'Error al eliminar cliente']);
            break;

        case 'reassignCode':
            $idCliente = $_POST['idCliente'] ?? '';
            $motivo = $_POST['motivo'] ?? '';

            if (empty($idCliente) || empty($motivo)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Todos los campos son requeridos: ID Cliente y Motivo'
                ]);
                break;
            }

            $codigoDAO = new CodigoDAO($db);
            $result = $codigoDAO->reassignCode($idCliente, $motivo);
            echo json_encode($result);
            break;

        case 'getHistorialReasignaciones':
            $codigoDAO = new CodigoDAO($db);

            // Obtener TODOS los códigos usando el procedimiento existente
            $resultCodigos = $codigoDAO->readAll();

            // Obtener TODOS los clientes
            $resultClientes = $dao->readAll();

            if ($resultCodigos['success'] && $resultClientes) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'codigos' => $resultCodigos['data'],
                        'clientes' => $resultClientes
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al obtener datos'
                ]);
            }
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
