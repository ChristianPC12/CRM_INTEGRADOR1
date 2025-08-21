<?php
// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Incluye la configuración de la base de datos y las clases necesarias para la bitácora
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDTO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraMapper.php';

// Controlador para gestionar la bitácora de sesiones de usuario
class BitacoraController
{
    // Registra la entrada de un usuario en la bitácora
    public function registrarEntrada($idUsuario)
    {
        $db = (new Database())->getConnection();
        $dao = new BitacoraDAO($db);

        // Verifica si el usuario ya tiene una sesión activa
        if ($dao->tieneSesionActiva($idUsuario)) {
            return false;
        }

        // Crea un nuevo registro de entrada
        $dto = new BitacoraDTO();
        $dto->idUsuario = $idUsuario;
        $dto->horaEntrada = date('H:i:s');
        $dto->horaSalida = '00:00:00';
        $dto->fecha = date('Y-m-d');

        return $dao->create($dto);
    }

    // Registra la salida de un usuario en la bitácora
    public function registrarSalida($idUsuario)
    {
        $db = (new Database())->getConnection();
        $dao = new BitacoraDAO($db);
        $bitacoras = $dao->readByUsuario($idUsuario);
        $ok = false;

        // Busca el registro de sesión activa y actualiza la hora de salida
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
// Si el archivo se ejecuta directamente vía HTTP (no CLI), procesa la petición
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    ob_start();

    try {
        // Ejecuta limpieza automática de registros expirados
        BitacoraDAO::ejecutarLimpiezaAutomatica();

        // Conexión a la base de datos
        $db = (new Database())->getConnection();
        if (!$db) throw new Exception("No se pudo conectar a la base de datos");

        $dao = new BitacoraDAO($db);
        $controller = new BitacoraController();
        // Determina la acción a ejecutar
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $response = [];

        switch ($action) {
            case 'create':
                // No se permite crear registros directamente por esta acción
                $response = ['success' => false, 'message' => "La acción 'create' directa no está permitida"];
                break;

            case 'read':
                // Lee un registro de bitácora por ID
                $id = $_POST['id'] ?? $_GET['id'] ?? '';
                if ($id === '') throw new Exception('ID requerido');
                $bitacora = $dao->read($id);
                $response = ['success' => $bitacora !== null, 'data' => $bitacora];
                break;

            case 'readByUsuario':
                // Lee todos los registros de bitácora de un usuario
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');
                $response = ['success' => true, 'data' => $dao->readByUsuario($idUsuario)];
                break;

            case 'readAll':
                // Lee todos los registros de bitácora
                $bitacoras = $dao->readAll();
                $data = [];

                // Formatea los datos para la respuesta
                foreach ($bitacoras as $b) {
                    $data[] = [
                        'idUsuario' => $b->idUsuario,
                        'nombreUsuario' => $b->nombreUsuario ?? '',
                        'horaEntrada' => $b->horaEntrada,
                        'horaSalida' => $b->horaSalida,
                        'fecha' => $b->fecha
                    ];
                }

                $response = ['success' => true, 'data' => $data];
                break;

            case 'update':
                // No se permite actualizar registros directamente por esta acción
                $response = ['success' => false, 'message' => "La acción 'update' directa no está permitida"];
                break;

            case 'registrarEntrada':
                // Registra la entrada de un usuario
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');
                $ok = $controller->registrarEntrada($idUsuario);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Entrada registrada' : 'Ya tienes una sesión activa'
                ];
                break;

            case 'registrarSalida':
                // Registra la salida de un usuario
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');
                $ok = $controller->registrarSalida($idUsuario);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Salida registrada' : 'No se pudo registrar salida'
                ];
                break;

            case 'eliminarExpirados':
                // Elimina registros expirados de la bitácora
                $eliminados = $dao->eliminarExpirados();
                $response = [
                    'success' => true,
                    'message' => "$eliminados registros eliminados correctamente.",
                    'eliminados' => $eliminados
                ];
                break;

            default:
                // Acción no válida
                $response = ['success' => false, 'message' => "Acción no válida en BitacoraController"];
        }

        // Devuelve la respuesta en formato JSON
        echo json_encode($response);

    } catch (Exception $e) {
        // Manejo de errores generales
        echo json_encode([
            'success' => false,
            'message' => 'Error del servidor: ' . $e->getMessage()
        ]);
    }

    ob_end_flush();
}
?>

