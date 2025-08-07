<?php
require_once __DIR__ . '/../config/Database.php';

$db = new Database();
$pdo = $db->getConnection();

$nombres = ["Juan", "María", "Carlos", "Ana", "Luis", "Sofía", "Pedro", "Laura"];
$lugares = ["SAN JOSÉ", "HEREDIA", "CARTAGO", "ALAJUELA", "GUÁCIMO", "GOLFITO"];

for ($i = 1; $i <= 5000; $i++) {
    $cedula = str_pad($i, 9, "0", STR_PAD_LEFT);
    $nombre = $nombres[array_rand($nombres)] . " TEST $i";
    $correo = "cliente$i@prueba.com";
    $telefono = "8" . str_pad($i % 10000000, 7, "0", STR_PAD_LEFT);
    $lugar = $lugares[array_rand($lugares)];
    $cumple = date("Y-m-d", strtotime("-" . rand(18, 65) . " years -" . rand(0, 365) . " days"));

    $stmt = $pdo->prepare("CALL ClienteCreate(?, ?, ?, ?, ?, ?, ?, ?)");

// Puedes ajustar los valores "TEST" si sabes qué campos son.
// Por ejemplo: usuarioRegistro y fechaRegistro
$stmt->execute([$cedula, $nombre, $correo, $telefono, $lugar, $cumple, 'PRUEBA', date('Y-m-d')]);

}

echo "✅ Se insertaron 5000 clientes correctamente.";
?>
