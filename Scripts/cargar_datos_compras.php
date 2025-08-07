<?php
ini_set('max_execution_time', 600); // 10 minutos
require_once __DIR__ . '/../config/Database.php';

$pdo = (new Database())->getConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmtClientes = $pdo->prepare("SELECT Id FROM cliente");
    $stmtClientes->execute();
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_COLUMN);

    if (count($clientes) === 0) {
        die("No hay clientes en la base de datos.\n");
    }

    $stmtCompra = $pdo->prepare("INSERT INTO compra (FechaCompra, Total, IdCliente) VALUES (?, ?, ?)");

    $comprasInsertadas = 0;
    $maxPorCliente = 3;  // Mantén bajo este número
    $contador = 0;

    foreach ($clientes as $clienteId) {
        for ($i = 0; $i < $maxPorCliente; $i++) {
            $fecha = date("Y-m-d", strtotime("-" . rand(1, 365) . " days"));
            $monto = rand(500, 20000);
            $stmtCompra->execute([$fecha, $monto, $clienteId]);
            $comprasInsertadas++;
            $contador++;

            // Liberar CPU cada 500 inserciones
            if ($contador % 500 === 0) {
                echo "Insertadas $contador compras...\n";
                flush();
            }
        }
    }

    echo "✅ Se insertaron $comprasInsertadas compras aleatorias.\n";
} catch (PDOException $e) {
    echo "❌ Error al insertar compras: " . $e->getMessage();
}
