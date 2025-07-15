<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDAO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraDTO.php';
require_once __DIR__ . '/../model/bitacora/BitacoraMapper.php';

class BitacoraController
{
    public function registrarEntradaInterna($idUsuario)
    {
        $db = (new Database())->getConnection();
        $dao = new BitacoraDAO($db);

        $dto = new BitacoraDTO();
        $dto->idUsuario = $idUsuario;
        $dto->horaEntrada = date('H:i:s');
        $dto->horaSalida = null;
        $dto->fecha = date('Y-m-d');

        return $dao->create($dto);
    }
}

// ✅ Solo ejecutar el bloque si se accede directamente desde el navegador o una llamada HTTP directa
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {

    try {
        $db = (new Database())->getConnection();
        if (!$db) throw new Exception("No se pudo conectar a la base de datos");

        $dao = new BitacoraDAO($db);
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $response = [];

        switch ($action) {
            case 'create':
                $dto = BitacoraMapper::mapDto($_POST);
                $response = [
                    'success' => $dao->create($dto),
                    'message' => 'Bitácora guardada correctamente'
                ];
                break;

            case 'read':
                $id = $_POST['id'] ?? $_GET['id'] ?? '';
                if ($id === '') throw new Exception('ID requerido');
                $bitacora = $dao->read($id);
                $response = [
                    'success' => $bitacora !== null,
                    'data' => $bitacora
                ];
                break;

            case 'readByUsuario':
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');
                $response = [
                    'success' => true,
                    'data' => $dao->readByUsuario($idUsuario)
                ];
                break;

            case 'readAll':
                $response = [
                    'success' => true,
                    'data' => $dao->readAll()
                ];
                break;

            case 'update':
                $dto = BitacoraMapper::mapDto($_POST);
                $ok = $dao->update($dto);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Bitácora actualizada correctamente' : 'Error al actualizar'
                ];
                break;

            case 'verificarExpiracion':
                $mostrar = $dao->hayPorExpirar();
                $response = [
                    'success' => true,
                    'mostrarAlerta' => $mostrar
                ];
                break;

            case 'registrarEntrada':
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');

                $dto = new BitacoraDTO();
                $dto->idUsuario = $idUsuario;
                $dto->horaEntrada = date('H:i:s');
                $dto->horaSalida = null;
                $dto->fecha = date('Y-m-d');

                $ok = $dao->create($dto);
                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Entrada registrada' : 'No se pudo registrar entrada'
                ];
                break;

            case 'registrarSalida':
                $idUsuario = $_POST['idUsuario'] ?? $_GET['idUsuario'] ?? '';
                if ($idUsuario === '') throw new Exception('ID de usuario requerido');

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

                $response = [
                    'success' => $ok,
                    'message' => $ok ? 'Salida registrada' : 'No se pudo registrar salida'
                ];
                break;

            default:
                throw new Exception("Acción no válida en BitacoraController");
        }

        echo json_encode($response);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error del servidor: ' . $e->getMessage()
        ]);
    }
}
