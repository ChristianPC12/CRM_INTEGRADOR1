<?php
header('Content-Type: application/json; charset=utf-8');
// Asegurar respuestas JSON incluso ante errores inesperados
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error PHP: ' . $message,
        'where' => basename($file) . ':' . $line
    ]);
    exit;
});
set_exception_handler(function($ex) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Excepción: ' . $ex->getMessage()
    ]);
    exit;
});
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/cumple/CumpleDAO.php';
require_once __DIR__ . '/../model/cumple/CumpleDTO.php';
require_once __DIR__ . '/../model/cumple/CumpleMapper.php';
require_once __DIR__ . '/../config/CorreoHelper.php';
require_once __DIR__ . '/../LIB/phpmailer/WhatsAppService.php';

$action = $_POST['action'] ?? '';

try {
    error_reporting(E_ALL);
    // No imprimir HTML de errores en respuestas JSON
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

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

    case 'enviarWhatsCumple':
    $telefono = $_POST['telefono'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';
        $idCliente = $_POST['idCliente'] ?? null;

    if (empty($telefono) || empty($nombre) || empty($idCliente)) {
            echo json_encode([
                'success' => false,
        'message' => 'Datos incompletos para enviar WhatsApp'
            ]);
            break;
        }

        // Normalizar teléfono: quitar no dígitos y prefijar código país (CR=506) si falta
        $telefonoLimpio = preg_replace('/\D+/', '', $telefono);
        if (strlen($telefonoLimpio) < 8) {
            echo json_encode([
                'success' => false,
                'message' => 'Teléfono inválido para WhatsApp'
            ]);
            break;
        }
        $cc = getenv('DEFAULT_CC') ?: '506';
        if (strpos($telefonoLimpio, $cc) !== 0) {
            // Si es número local de 8 dígitos en CR, anteponer 506
            if (strlen($telefonoLimpio) === 8) {
                $telefonoLimpio = $cc . $telefonoLimpio;
            }
        }

        try {
            $base = getenv('WHATS_BASE') ?: 'http://localhost:3001';
            $svc = new WhatsApiService($base);

            // Verificar estado para mejor mensaje de error
            $status = $svc->status();
            if (!($status['ready'] ?? false)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'WhatsApp no está listo. Verifique que PM2 esté corriendo: pm2 status'
                ]);
                break;
            }

            // Mensaje por defecto si no viene desde el frontend
            $mensajeFinal = $mensaje ?: ("¡Hola $nombre! En Bastos sabemos que estás de cumpleaños. Visítanos para celebrarlo juntos y reclamar tu regalía 🎉🎁");
            $res = $svc->send($telefonoLimpio, $mensajeFinal);

            // Registrar en historial como en el flujo de correo
            $hoy = new DateTime();
            $diaSemana = $hoy->format('w'); // 0..6
            $diasHastaDomingo = 7 - $diaSemana;
            $vence = clone $hoy;
            $vence->modify("+{$diasHastaDomingo} days");
            $venceStr = $vence->format('Y-m-d');

            $sql = "INSERT INTO cumple (IdCliente, FechaLlamada, Vence, Vencido)
                    VALUES (:idCliente, CURDATE(), :vence, 'NO')";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':idCliente', $idCliente);
            $stmt->bindParam(':vence', $venceStr);
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'WhatsApp enviado correctamente',
                'result' => $res
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error enviando WhatsApp: ' . $e->getMessage()
            ]);
        }
        break;

    case 'registrarLlamadaCumple':
    $idCliente = $_POST['idCliente'] ?? null;

    if (empty($idCliente)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID del cliente no proporcionado'
        ]);
        break;
    }

    // Igual que en enviarCorreoCumple, calculamos fecha de vencimiento (domingo de la semana)
    $hoy = new DateTime();
    $diaSemana = $hoy->format('w'); // 0 (domingo) a 6 (sábado)
    $diasHastaDomingo = 7 - $diaSemana;
    $vence = clone $hoy;
    $vence->modify("+$diasHastaDomingo days");
    $venceStr = $vence->format('Y-m-d');

    // Insertamos en la tabla cumple, pero sin correo
    $sql = "INSERT INTO cumple (IdCliente, FechaLlamada, Vence, Vencido)
            VALUES (:idCliente, CURDATE(), :vence, 'NO')";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idCliente', $idCliente);
    $stmt->bindParam(':vence', $venceStr);
    $stmt->execute();

    // ACTUALIZA el estado del cliente a 'LISTA'

    echo json_encode([
        'success' => true,
        'message' => '¡Llamada registrada correctamente!'
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
