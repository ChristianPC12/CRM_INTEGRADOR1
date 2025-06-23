<?php
require_once '../config/Database.php';
$pdo = (new Database())->getConnection();

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM usuario WHERE Id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
        } else {
            $response['message'] = "No se encontró el usuario.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Error al eliminar: " . $e->getMessage();
    }
} else {
    $response['message'] = "Petición inválida.";
}

header('Content-Type: application/json');
echo json_encode($response);
