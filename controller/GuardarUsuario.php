<?php
// Mostrar errores (para depurar)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/Database.php';
$pdo = (new Database())->getConnection();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $rol = trim($_POST['rol'] ?? '');
    $privilegios = trim($_POST['privilegios'] ?? '');

    // Validación básica
    if ($usuario === '' || $contrasena === '' || $rol === '') {
        $response['message'] = 'Faltan campos obligatorios.';
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).{6,}$/', $contrasena)) {
        $response['message'] = 'La contraseña debe tener mínimo 6 caracteres, al menos 1 letra y 1 número.';
    } else {
        try {
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE Usuario = ?");
            $stmt->execute([$usuario]);

            if ($stmt->fetchColumn() > 0) {
                $response['message'] = 'El usuario ya existe.';
            } else {
                // Insertar nuevo usuario
                $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuario (Usuario, Contrasena, Rol) VALUES (?, ?, ?)");
                $stmt->execute([$usuario, $contrasenaHash, $rol]);

                $response['success'] = true;
                $response['message'] = 'Usuario guardado correctamente.';
            }
        } catch (PDOException $e) {
            $response['message'] = "Error en la base de datos: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Petición no válida.';
}

header('Content-Type: application/json');
echo json_encode($response);
