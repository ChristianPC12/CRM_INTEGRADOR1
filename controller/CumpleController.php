<?php
require_once '../config/Database.php';
require_once '../model/cumple/CumpleDAO.php';
require_once '../model/cumple/CumpleDTO.php';
require_once '../model/cumple/CumpleMapper.php';
require_once '../config/CorreoHelper.php';

$action = $_POST['action'] ?? '';

try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $database = new Database();
    $conn = $database->getConnection();
    $dao = new CumpleDAO($conn);

    switch ($action) {
        case 'readSemana':
            $cumples = $dao->obtenerCumplesSemana();
            echo json_encode([
                'success' => true,
                'data' => $cumples
            ]);
            break;

        case 'cambiarEstado':
            $id = $_POST['id'] ?? null;
            $nuevoEstado = $_POST['estado'] ?? null;

            if ($id === null || $nuevoEstado === null) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos incompletos'
                ]);
                break;
            }

            $resultado = $dao->actualizarEstado($id, $nuevoEstado);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Estado actualizado' : 'Error al actualizar el estado'
            ]);
            break;

        case 'marcarComoEnviado':
            $id = $_POST['id'] ?? null;

            if ($id === null) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID no proporcionado'
                ]);
                break;
            }

            $resultado = $dao->actualizarEstado($id, 'LISTA');
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Estado cambiado a LISTA' : 'Error al actualizar'
            ]);
            break;

        case 'enviarCorreoCumple':
    $correo = $_POST['correo'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';
    $idCliente = $_POST['idCliente'] ?? null; // ✅ NUEVO: lo obtenemos del formulario

    if (empty($correo) || empty($nombre) || empty($mensaje) || empty($idCliente)) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos para enviar el correo'
        ]);
        break;
    }

    $enviado = enviarCorreoCumple($correo, $nombre, $mensaje);

    if ($enviado) {
        // ✅ NUEVO: calcular fecha de vencimiento como domingo de esta semana
        $hoy = new DateTime();
        $diaSemana = $hoy->format('w'); // 0 (domingo) a 6 (sábado)
        $diasHastaDomingo = 7 - $diaSemana;
        $vence = clone $hoy;
        $vence->modify("+$diasHastaDomingo days");
        $venceStr = $vence->format('Y-m-d');

        // ✅ Insertar en historial con relación a cliente
        $sql = "INSERT INTO cumple (IdCliente, FechaLlamada, Vence, Vencido)
                VALUES (:idCliente, CURDATE(), :vence, 'NO')";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente);
        $stmt->bindParam(':vence', $venceStr);
        $stmt->execute();
    }

    echo json_encode([
        'success' => $enviado,
        'message' => $enviado ? 'Correo enviado correctamente' : 'Error al enviar el correo'
    ]);
    break;


        case 'readHistorial':
            $historial = $dao->obtenerHistorial();
            echo json_encode($historial);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage()
    ]);
}
