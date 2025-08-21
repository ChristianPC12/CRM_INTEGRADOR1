<?php
// Controlador para generación y validación de códigos express
session_start(); // Inicia la sesión para almacenar códigos temporales
header('Content-Type: application/json'); // Respuestas en formato JSON

// Requiere configuración de base de datos y utilidades de correo
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/CorreoExpress.php';

// Determina la acción a ejecutar
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* ────────── 1. Generar y enviar ────────── */
    case 'generarCodigoExpress':
    // Obtiene datos del cliente
    $idCliente  = intval($_POST['idCliente'] ?? 0);
    $correo     = trim($_POST['correo']  ?? '');
    $nombre     = trim($_POST['nombre']  ?? '');

    // Valida que los datos sean correctos
    if (!$idCliente || !$correo || !$nombre) {
        echo json_encode(['success'=>false,'message'=>'Datos incompletos.']);
        exit;
    }

    // Genera un código de 6 dígitos aleatorio
    $codigo  = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
    $expires = time() + 300; // El código expira en 5 minutos

    // Guarda el código y datos en la sesión
    $_SESSION['express'] = [
        'idCliente' => $idCliente,
        'codigo'    => $codigo,
        'expires'   => $expires
    ];

    // Envía el código por correo
    $enviado = enviarCodigoExpress($correo, $nombre, $codigo);

    echo json_encode(['success'=> $enviado]);
    exit;

    /* ────────── 2. Validar ────────── */
    case 'validarCodigoExpress':
    // Obtiene datos enviados por el usuario
    $idCliente = intval($_POST['idCliente'] ?? 0);
    $codigoCli = trim($_POST['codigo'] ?? '');

    // Verifica que exista un código generado en sesión
    if (!isset($_SESSION['express'])) {
        echo json_encode(['success'=>false,'message'=>'No hay código generado.']);
        exit;
    }

    $exp = $_SESSION['express'];

    // Verifica si el código ha expirado
    if ($exp['expires'] < time()) {
        unset($_SESSION['express']);
        echo json_encode(['success'=>false,'message'=>'Código expirado.']);
        exit;
    }

    // Valida que el código y el cliente coincidan
    if ($exp['idCliente'] == $idCliente && $exp['codigo'] === $codigoCli) {
        unset($_SESSION['express']);
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Código incorrecto.']);
    }
    exit;

    default:
    // Acción no reconocida
    echo json_encode(['success'=>false,'message'=>'Acción no reconocida.']);
}
