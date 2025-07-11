<?php
// controller/AnalisisController.php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/compra/CompraDAO.php'; // Asegúrate de tener este DAO y Mapper igual que los otros

try {
    $db = (new Database())->getConnection();
    if (!$db) throw new Exception("No se pudo conectar a la base de datos");

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    switch ($action) {
        case 'clientesFrecuentes':
            // Obtener todos los clientes
            $clienteDAO = new ClienteDAO($db);
            $clientes = $clienteDAO->readAll(); // array de objetos ClienteDTO

            // Obtener todas las compras
            require_once __DIR__ . '/../model/compra/CompraDAO.php';
            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll(); // array de objetos CompraDTO

            // Contar visitas y encontrar última compra
            $visitasPorCliente = [];
            foreach ($clientes as $cliente) {
                $visitasPorCliente[$cliente->id] = [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'cedula' => $cliente->cedula,
                    'telefono' => $cliente->telefono,
                    'visitas' => 0,
                    'ultimaVisita' => null,
                ];
            }

            foreach ($compras as $compra) {
                $cid = $compra->idCliente;
                if (isset($visitasPorCliente[$cid])) {
                    $visitasPorCliente[$cid]['visitas']++;
                    if (
                        !$visitasPorCliente[$cid]['ultimaVisita'] ||
                        $compra->fechaCompra > $visitasPorCliente[$cid]['ultimaVisita']
                    ) {
                        $visitasPorCliente[$cid]['ultimaVisita'] = $compra->fechaCompra;
                    }
                }
            }

            // Filtrar solo clientes con ≥1 visita y ordenarlos
            $ranking = array_filter($visitasPorCliente, fn($c) => $c['visitas'] > 0);
            usort($ranking, fn($a, $b) => $b['visitas'] <=> $a['visitas']);

            // Añadir ranking/posición
            $i = 1;
            foreach ($ranking as &$c) $c['posicion'] = $i++;

            // Top 10 para el gráfico
            $top = array_slice($ranking, 0, 10);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => array_values($ranking), // todos los que han comprado
                    'top' => array_values($top)
                ]
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
