<?php
require_once __DIR__ . '/../config/Database.php';

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->query("SELECT id FROM cliente LIMIT 100");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($clientes as $c) {
    $idCliente = $c['id'];
    for ($i = 0; $i < 5; $i++) {
        $fecha = date("Y-m-d", strtotime("-" . rand(0, 365) . " days"));
        $monto = rand(2000, 10000);
        $stmtVenta = $pdo->prepare("INSERT INTO venta (idCliente, fechaVenta, monto) VALUES (?, ?, ?)");
        $stmtVenta->execute([$idCliente, $fecha, $monto]);
    }
}

echo "✅ Ventas generadas.";
?>
