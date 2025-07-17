<?php
header('Content-Type: application/json');

// Carga clases necesarias
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDTO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraMapper.php';

class BitacoraController
{
    // Registra la entrada de un usuario
    public function registrarEntrada($idUsuario)
    {
        $db = (new Database())->getConnection();
        $dao = new BitacoraDAO($db);

        if ($dao->tieneSesionActiva($idUsuario)) {
            return false; // Ya tiene una sesión activa
        }

        $dto = new BitacoraDTO();
        $dto->idUsuario = $idUsuario;
        $dto->horaEntrada = date('H:i:s');
        $dto->horaSalida = '00:00:00';
        $dto->fecha = date('Y-m-d');

        return $dao->create($dto);
    }

    // Registra la salida en el último registro sin cerrar
    public function registrarSalida($idUsuario)
    {
        $db = (new Database())->getConnection();
        $dao = new BitacoraDAO($db);
        $bitacoras = $dao->readByUsuario($idUsuario);
        $ok = false;

        if (!empty($bitacoras)) {
            foreach ($bitacoras as $b) {
                if (empty($b->horaSalida) || $b->horaSalida === '00:00:00') {
                    $b->horaSalida = date('H:i:s');
                    $ok = $dao->update($b);
                    break;
                }
            }
        }

        return $ok;
    }
}

// ==== HTTP ====
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        BitacoraDAO::ejecutarLimpiezaAutomatica(); // ✅ reemplaza AutoCleanup

        $db = (new Database())->getConnection();
        if (!$db) throw new Exception("No se pudo conectar a la base de datos");

        $dao = new BitacoraDAO($db);
        $controller = new BitacoraController();
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $response = [];

        switch ($action) {
            case 'create':
                $response = ['success' => false, 'message' => "La acción 'create' directa no está permitida"];
                break;

            case 'read':
                $id = $_POST['id'] ?? $_GET['id'] ?? '';
                if ($id === '') throw new Exception('ID requerido');
                $bitacora = $dao->read($id);
                $response = ['success' => $bitacora !== null, 'data' => $bitacora];
                break;

            case 'readByUsuario':
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');
                $response = ['success' => true, 'data' => $dao->readByUsuario($idUsuario)];
                break;

            case 'readAll':
                $response = ['success' => true, 'data' => $dao->readAll()];
                break;

            case 'update':
                $response = ['success' => false, 'message' => "La acción 'update' directa no está permitida"];
                break;

            case 'registrarEntrada':
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');
                $ok = $controller->registrarEntrada($idUsuario);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Entrada registrada' : 'Ya tienes una sesión activa'
                ];
                break;

            case 'registrarSalida':
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');
                $ok = $controller->registrarSalida($idUsuario);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Salida registrada' : 'No se pudo registrar salida'
                ];
                break;
            case 'eliminarExpirados':
                $dao = new BitacoraDAO((new Database())->getConnection());
                $eliminados = $dao->eliminarExpirados();
                echo json_encode([
                    'success' => true,
                    'message' => "$eliminados registros eliminados correctamente.",
                    'eliminados' => $eliminados
                ]);
                return;


            default:
                $response = ['success' => false, 'message' => "Acción no válida en BitacoraController"];
        }

        echo json_encode($response);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error del servidor: ' . $e->getMessage()
        ]);
    }
}
?>
