<?php
// Archivo: controller/BitacoraController.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDTO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraMapper.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) throw new Exception("No se pudo conectar a la base de datos");

    $dao = new BitacoraDAO($db);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {

        case 'create':
            $dto = BitacoraMapper::mapDto($_POST);
            $result = $dao->create($dto);
            echo json_encode([
                'success' => $result === true,
                'message' => $result ? 'Bitácora guardada correctamente' : 'Error al guardar'
            ]);
            break;

        case 'read':
            $id = $_POST['id'] ?? $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                break;
            }
            $bitacora = $dao->read($id);
            echo json_encode(['success' => $bitacora !== null, 'data' => $bitacora]);
            break;

        case 'readByUsuario':
            $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
            if ($idUsuario === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
                break;
            }
            $data = $dao->readByUsuario($idUsuario);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'readAll':
            echo json_encode(['success' => true, 'data' => $dao->readAll()]);
            break;

        case 'update':
            $dto = BitacoraMapper::mapDto($_POST);
            $result = $dao->update($dto);
            echo json_encode([
                'success' => $result === true,
                'message' => $result ? 'Bitácora actualizada correctamente' : 'Error al actualizar'
            ]);
            break;

        case 'verificarExpiracion':
            $mostrar = $dao->hayPorExpirar();
            echo json_encode(['success' => true, 'mostrarAlerta' => $mostrar]);
            break;

        case 'registrarEntrada':
            $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
            if ($idUsuario === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
                break;
            }

            $dto = new BitacoraDTO();
            $dto->idUsuario = $idUsuario;
            $dto->horaEntrada = date('H:i:s');
            $dto->horaSalida = null;
            $dto->fecha = date('Y-m-d');

            $ok = $dao->create($dto);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Entrada registrada' : 'No se pudo registrar entrada']);
            break;

        case 'registrarSalida':
            $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
            if ($idUsuario === '') {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
                break;
            }

            $bitacoras = $dao->readByUsuario($idUsuario);
            $ok = false;

            if (!empty($bitacoras)) {
                foreach (array_reverse($bitacoras) as $b) {
                    if (empty($b->horaSalida)) {
                        $b->horaSalida = date('H:i:s');
                        $ok = $dao->update($b);
                        break;
                    }
                }
            }

            echo json_encode(['success' => $ok, 'message' => $ok ? 'Salida registrada' : 'No se pudo registrar salida']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida en BitacoraController']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
