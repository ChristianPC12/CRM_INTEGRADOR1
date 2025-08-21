<?php
// Archivo: controller/ClienteController.php
header('Content-Type: application/json');

require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/cliente/ClienteDTO.php';
require_once __DIR__ . '/../model/cliente/ClienteMapper.php';
require_once __DIR__ . '/../config/Database.php';

// Código (reasignación)
require_once __DIR__ . '/../model/codigo/CodigoDAO.php';
require_once __DIR__ . '/../model/codigo/CodigoDTO.php';

function generarCodigoBarra($cliente) {
    return ($cliente->cedula ?: '') . date('YmdHis');
}

function sanitize($v) {
    if ($v === null) return null;
    if (is_string($v) && strtolower(trim($v)) === 'null') return null;
    $t = is_string($v) ? trim($v) : $v;
    return ($t === '') ? null : $t;
}

try {
    $db = (new Database())->getConnection();
    if (!$db) throw new Exception("No se pudo conectar a la base de datos");

    $dao = new ClienteDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $cliente = new ClienteDTO();
            $cliente->cedula          = sanitize($_POST['cedula'] ?? '');
            $cliente->nombre          = sanitize($_POST['nombre'] ?? '');
            $cliente->correo          = sanitize($_POST['correo'] ?? '');
            $cliente->telefono        = sanitize($_POST['telefono'] ?? '');
            $cliente->lugarResidencia = sanitize($_POST['lugarResidencia'] ?? '');
            $cliente->fechaCumpleanos = sanitize($_POST['fechaCumpleanos'] ?? '');
            $cliente->alergias        = sanitize($_POST['alergias'] ?? '');
            $cliente->gustosEspeciales= sanitize($_POST['gustosEspeciales'] ?? '');

            if ($dao->existeTelefonoOCorreo($cliente->telefono, $cliente->correo, null)) {
                echo json_encode(['success'=>false,'message'=>'El teléfono o correo ya están registrados para otro cliente.']); exit;
            }
            if ($dao->existeCedula($cliente->cedula, null)) {
                echo json_encode(['success'=>false,'message'=>'La cédula ya está registrada.']); exit;
            }

            $result = $dao->create($cliente);

            if ($result) {
                $idCliente = $db->lastInsertId();
                $codigoDAO = new CodigoDAO($db);
                $codigoDTO = new CodigoDTO();
                $codigoDTO->idCliente = (int)$idCliente;
                $codigoDTO->codigoBarra = generarCodigoBarra($cliente);
                $codigoDTO->cantImpresiones = 0;
                $codigoDAO->create($codigoDTO);
            }

            echo json_encode(['success'=>$result,'message'=>$result?'Cliente creado exitosamente':'Error al crear cliente']);
            break;

        case 'readAll':
            echo json_encode(['success'=>true,'data'=>$dao->readAll()]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $cliente = $dao->read($id);
            echo json_encode(['success'=>$cliente!==null,'data'=>$cliente]);
            break;

        case 'update':
            $cliente = new ClienteDTO();
            $cliente->id              = (int)($_POST['id'] ?? 0);
            $cliente->cedula          = sanitize($_POST['cedula'] ?? '');
            $cliente->nombre          = sanitize($_POST['nombre'] ?? '');
            $cliente->correo          = sanitize($_POST['correo'] ?? '');
            $cliente->telefono        = sanitize($_POST['telefono'] ?? '');
            $cliente->lugarResidencia = sanitize($_POST['lugarResidencia'] ?? '');
            $cliente->fechaCumpleanos = sanitize($_POST['fechaCumpleanos'] ?? '');
            // Puede venir null si no lo cambian
            $cliente->acumulado       = isset($_POST['acumulado']) ? (($_POST['acumulado']===''||strtolower($_POST['acumulado'])==='null')? null : (int)$_POST['acumulado']) : null;
            $cliente->alergias        = sanitize($_POST['alergias'] ?? '');
            $cliente->gustosEspeciales= sanitize($_POST['gustosEspeciales'] ?? '');

            if ($dao->existeTelefonoOCorreo($cliente->telefono, $cliente->correo, $cliente->id)) {
                echo json_encode(['success'=>false,'message'=>'El teléfono o correo ya están registrados para otro cliente.']); exit;
            }
            if ($dao->existeCedula($cliente->cedula, $cliente->id)) {
                echo json_encode(['success'=>false,'message'=>'La cédula ya está registrada.']); exit;
            }

            $result = $dao->update($cliente);
            echo json_encode(['success'=>$result,'message'=>$result?'Cliente actualizado exitosamente':'Error al actualizar cliente']);
            break;

        /** ✅ NUEVO: actualizar solo saldo (para Descuento/Express) */
        case 'updateSaldo':
            $id = (int)($_POST['id'] ?? 0);
            $acumulado = (int)($_POST['acumulado'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID inválido']); break; }
            $ok = $dao->updateSaldo($id, $acumulado);
            echo json_encode(['success'=>$ok,'message'=>$ok?'Saldo actualizado':'Error al actualizar saldo']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode(['success'=>$result,'message'=>$result?'Cliente eliminado exitosamente':'Error al eliminar cliente']);
            break;

        case 'reassignCode':
            $idCliente = $_POST['idCliente'] ?? '';
            $motivo    = sanitize($_POST['motivo'] ?? '');
            if (empty($idCliente) || empty($motivo)) {
                echo json_encode(['success'=>false,'message'=>'Todos los campos son requeridos: ID Cliente y Motivo']); break;
            }
            $codigoDAO = new CodigoDAO($db);
            $result = $codigoDAO->reassignCode($idCliente, $motivo);
            echo json_encode($result);
            break;

        case 'getHistorialReasignaciones':
            $codigoDAO = new CodigoDAO($db);
            $resultCodigos = $codigoDAO->readAll();
            $resultClientes = $dao->readAll();

            if ($resultCodigos['success'] && $resultClientes) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'codigos'  => $resultCodigos['data'],
                        'clientes' => $resultClientes
                    ]
                ]);
            } else {
                echo json_encode(['success'=>false,'message'=>'Error al obtener datos']);
            }
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Acción no válida']);
    }

} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error del servidor: '.$e->getMessage()]);
}
