<?php
// Archivo: controller/ClienteController.php

// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Incluye los modelos y DAOs necesarios para la gestión de clientes y códigos
require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/cliente/ClienteDTO.php';
require_once __DIR__ . '/../model/cliente/ClienteMapper.php';
require_once __DIR__ . '/../config/Database.php';

// Importa el modelo de código para la creación automática de códigos de barra
require_once __DIR__ . '/../model/codigo/CodigoDAO.php';
require_once __DIR__ . '/../model/codigo/CodigoDTO.php';

// Genera un código de barra único para un cliente usando su cédula y la fecha/hora actual
function generarCodigoBarra($cliente)
{
    // Ejemplo: cédula + fecha y hora actual para unicidad
    return $cliente->cedula . date('YmdHis');
}

// Valida una fecha de cumpleaños con reglas estrictas de formato y rango
function validarFechaCumpleEstricto(string $s): bool {
    // Formato exacto AAAA-MM-DD
    $dt = DateTime::createFromFormat('Y-m-d', $s);
    if (!$dt || $dt->format('Y-m-d') !== $s) return false;

    // Rango permitido de fechas
    $min = new DateTime('1900-01-01');
    $hoy = new DateTime('today');
    if ($dt < $min || $dt > $hoy) return false;

    // Edad permitida entre 0 y 120 años
    $edad = (int)$dt->diff($hoy)->y;
    return $edad >= 0 && $edad <= 120;
}

try {
    // Obtiene la conexión a la base de datos
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $dao = new ClienteDAO($db);
    // Determina la acción a ejecutar a partir de POST o GET
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            // Crea un nuevo cliente a partir de los datos recibidos
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

            // Valida que el teléfono o correo no estén registrados para otro cliente
            if ($dao->existeTelefonoOCorreo($cliente->telefono, $cliente->correo)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono o correo ya están registrados para otro cliente.'
                ]);
                exit;
            }
            // Valida que la cédula no esté registrada para otro cliente
            if ($dao->existeCedula($cliente->cedula, null)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La cédula ya está registrada.'
                ]);
                exit;
            }
            // Crea el cliente en la base de datos
            $result = $dao->create($cliente);

            if ($result) {
                // Obtiene el último ID insertado para el cliente
                $idCliente = $db->lastInsertId();

                // Crea automáticamente el código de barras asociado a este cliente
                $codigoDAO = new CodigoDAO($db);
                $codigoDTO = new CodigoDTO();
                $codigoDTO->idCliente = $idCliente;
                $codigoDTO->codigoBarra = generarCodigoBarra($cliente);
                $codigoDTO->cantImpresiones = 0;
                $codigoDAO->create($codigoDTO);
            }

            // Devuelve el resultado de la operación
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Cliente creado exitosamente' : 'Error al crear cliente'
            ]);
            break;

        case 'readAll':
            // Devuelve todos los clientes registrados
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        case 'read':
            // Devuelve los datos de un cliente por su ID
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $cliente = $dao->read($id);
            echo json_encode(['success' => $cliente !== null, 'data' => $cliente]);
            break;

        case 'update':
            // Actualiza los datos de un cliente
            $cliente = new ClienteDTO();
            $cliente->id = $_POST['id'] ?? '';
            $cliente->cedula = $_POST['cedula'] ?? '';
            $cliente->nombre = $_POST['nombre'] ?? '';
            $cliente->correo = $_POST['correo'] ?? '';
            $cliente->telefono = $_POST['telefono'] ?? '';
            $cliente->lugarResidencia = $_POST['lugarResidencia'] ?? '';
            $cliente->fechaCumpleanos = $_POST['fechaCumpleanos'] ?? '';
            $cliente->acumulado = $_POST['acumulado'] ?? null;
            // Valida que el teléfono o correo no estén registrados para otro cliente
            if ($dao->existeTelefonoOCorreo($cliente->telefono, $cliente->correo, $cliente->id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono o correo ya están registrados para otro cliente.'
                ]);
                exit;
            }
            // Valida que la cédula no esté registrada para otro cliente
            if ($dao->existeCedula($cliente->cedula, $cliente->id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La cédula ya está registrada.'
                ]);
                exit;
            }
            // Actualiza el cliente en la base de datos
            $result = $dao->update($cliente);
            echo json_encode(['success' => $result, 'message' => $result ? 'Cliente actualizado exitosamente' : 'Error al actualizar cliente']);
            break;

        case 'delete':
            // Elimina un cliente por su ID
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            $result = $dao->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Cliente eliminado exitosamente' : 'Error al eliminar cliente']);
            break;

        case 'reassignCode':
            // Reasigna un código de barras a un cliente por motivo
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
            // Obtiene el historial de reasignaciones de códigos y todos los clientes
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
            // Acción no válida
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
