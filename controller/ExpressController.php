<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/CorreoExpress.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* ────────── 1. Generar y enviar ────────── */
    case 'generarCodigoExpress':
        $idCliente  = intval($_POST['idCliente'] ?? 0);
        $correo     = trim($_POST['correo']  ?? '');
        $nombre     = trim($_POST['nombre']  ?? '');

        if (!$idCliente || !$correo || !$nombre) {
            echo json_encode(['success'=>false,'message'=>'Datos incompletos.']);
            exit;
        }

        // Generar código 6 dígitos
        $codigo  = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
        $expires = time() + 300; // 5 min

        $_SESSION['express'] = [
            'idCliente' => $idCliente,
            'codigo'    => $codigo,
            'expires'   => $expires
        ];

        $enviado = enviarCodigoExpress($correo, $nombre, $codigo);

        echo json_encode(['success'=> $enviado]);
        exit;

    /* ────────── 2. Validar ────────── */
    case 'validarCodigoExpress':
        $idCliente = intval($_POST['idCliente'] ?? 0);
        $codigoCli = trim($_POST['codigo'] ?? '');

        if (!isset($_SESSION['express'])) {
            echo json_encode(['success'=>false,'message'=>'No hay código generado.']);
            exit;
        }

        $exp = $_SESSION['express'];

        if ($exp['expires'] < time()) {
            unset($_SESSION['express']);
            echo json_encode(['success'=>false,'message'=>'Código expirado.']);
            exit;
        }

        if ($exp['idCliente'] == $idCliente && $exp['codigo'] === $codigoCli) {
            unset($_SESSION['express']);
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>false,'message'=>'Código incorrecto.']);
        }
        exit;

    default:
        echo json_encode(['success'=>false,'message'=>'Acción no reconocida.']);
}
