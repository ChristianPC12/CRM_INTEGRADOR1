<?php
// Controlador para gestión de cumpleaños de clientes
header('Content-Type: application/json; charset=utf-8');
// Asegura respuestas JSON incluso ante errores inesperados
set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error PHP: ' . $message,
        'where' => basename($file) . ':' . $line
    ]);
    exit;
});
set_exception_handler(function ($ex) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Excepción: ' . $ex->getMessage()
    ]);
    exit;
});
// Requiere modelos y utilidades necesarios
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/cumple/CumpleDAO.php';
require_once __DIR__ . '/../model/cumple/CumpleDTO.php';
require_once __DIR__ . '/../model/cumple/CumpleMapper.php';
require_once __DIR__ . '/../config/CorreoHelper.php';
require_once __DIR__ . '/../LIB/phpmailer/WhatsAppService.php';

$action = $_POST['action'] ?? '';

try {
    // Configuración de errores y logs
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // No imprimir HTML de errores
    ini_set('log_errors', 1);

    // Conexión y DAO
    $database = new Database();
    $conn = $database->getConnection();
    $dao = new CumpleDAO($conn);

    switch ($action) {
        case 'readSemana': {
            // Lee los cumpleaños de la semana (offset 0=actual, 1=siguiente)
            $offset = isset($_REQUEST['offset']) ? (int) $_REQUEST['offset'] : 0;
            if ($offset < 0)
                $offset = 0;
            if ($offset > 1)
                $offset = 1;

            $cumples = $dao->obtenerCumplesSemana($offset);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'data' => $cumples]);
            break;
        }

        case 'cambiarEstado':
            // Cambia el estado de un registro de cumpleaños
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
            // Marca un cumpleaños como enviado (estado LISTA)
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
            // Envía un correo de cumpleaños y registra el evento
            $correo = $_POST['correo'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $mensaje = $_POST['mensaje'] ?? '';
            $idCliente = $_POST['idCliente'] ?? null;

            if (empty($correo) || empty($nombre) || empty($mensaje) || empty($idCliente)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos incompletos para enviar el correo'
                ]);
                break;
            }

            $enviado = enviarCorreoCumple($correo, $nombre, $mensaje);

            if ($enviado) {
                // Calcula fecha de vencimiento (domingo de la semana)
                $hoy = new DateTime();
                $diaSemana = $hoy->format('w');
                $diasHastaDomingo = 7 - $diaSemana;
                $vence = clone $hoy;
                $vence->modify("+$diasHastaDomingo days");
                $venceStr = $vence->format('Y-m-d');

                // Inserta en historial de cumpleaños
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
            // Envía un WhatsApp de cumpleaños y registra el evento
            $telefono = $_POST['telefono'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $mensaje = '';
            $idCliente = $_POST['idCliente'] ?? null;

            if (empty($telefono) || empty($nombre) || empty($idCliente)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos incompletos para enviar WhatsApp'
                ]);
                break;
            }

            // Normaliza el teléfono y agrega código país si falta
            $telefonoLimpio = preg_replace('/\D+/', '', $telefono);
            if (strlen($telefonoLimpio) < 8) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Teléfono inválido para WhatsApp'
                ]);
                break;
            }

            $cc = getenv('DEFAULT_CC') ?: '506'; // Costa Rica por defecto
            if (strpos($telefonoLimpio, $cc) !== 0) {
                if (strlen($telefonoLimpio) === 8) {
                    $telefonoLimpio = $cc . $telefonoLimpio;
                }
            }

            try {
                $base = getenv('WHATS_BASE') ?: 'http://localhost:3002';
                $svc = new WhatsApiService($base);

                // Verifica que el servicio de WhatsApp esté listo
                $status = $svc->status();
                if (!($status['ready'] ?? false)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'WhatsApp no está listo. Verifique que el servicio esté corriendo'
                    ]);
                    break;
                }

                // Mensaje final (usa el nuevo default si $mensaje está vacío)
                if (empty($mensaje)) {
                    $mensajeFinal =
                        "🥳 ¡Hola $nombre!\n" .
                        "En Bastos nos encanta ser parte de tu cumpleaños 🎂.\n" .
                        "Queremos invitarte como *Cliente VIP* a celebrarlo con nosotros y disfrutar de tu regalía especial 🎉.\n\n" .
                        "👉 Más info en www.bastoscr.com\n\n" .
                        "📌 Promoción exclusiva para nuestros *clientes VIP*. Vigente durante la semana de tu cumpleaños.";

                } else {
                    $mensajeFinal = $mensaje;
                }

                // Enviar WhatsApp
                $res = $svc->send($telefonoLimpio, $mensajeFinal);

                // Registrar en historial
                $hoy = new DateTime();
                $diaSemana = $hoy->format('w');
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
            // Registra una llamada de cumpleaños y calcula vencimiento
            $idCliente = $_POST['idCliente'] ?? null;

            if (empty($idCliente)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID del cliente no proporcionado'
                ]);
                break;
            }

            // Calcula fecha de vencimiento (domingo de la semana)
            $hoy = new DateTime();
            $diaSemana = $hoy->format('w');
            $diasHastaDomingo = 7 - $diaSemana;
            $vence = clone $hoy;
            $vence->modify("+$diasHastaDomingo days");
            $venceStr = $vence->format('Y-m-d');

            // Inserta en la tabla cumple
            $sql = "INSERT INTO cumple (IdCliente, FechaLlamada, Vence, Vencido)
            VALUES (:idCliente, CURDATE(), :vence, 'NO')";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':idCliente', $idCliente);
            $stmt->bindParam(':vence', $venceStr);
            $stmt->execute();

            // (Opcional) Actualiza el estado del cliente a 'LISTA'

            echo json_encode([
                'success' => true,
                'message' => '¡Llamada registrada correctamente!'
            ]);
            break;

        case 'readHistorial':
            // Obtiene el historial de cumpleaños
            $historial = $dao->obtenerHistorial();
            echo json_encode($historial);
            break;

        default:
            // Acción no válida
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }

} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage()
    ]);
}
