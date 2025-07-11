<?php
// controller/AnalisisController.php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/compra/CompraDAO.php'; // Asegúrate de tener este DAO y Mapper igual que los otros

try {
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'clientesFrecuentes':
            // Obtener todos los clientes
            $clienteDAO = new ClienteDAO($db);
            $clientes = $clienteDAO->readAll(); // array de objetos ClienteDTO

            // Obtener todas las compras
            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll(); // array de objetos CompraDTO

            // Inicializa estructura de visitas y última visita
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

            // Recorre compras y actualiza visitas y última visita
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

            // Filtra solo clientes con al menos 1 visita
            $ranking = array_filter($visitasPorCliente, fn($c) => $c['visitas'] > 0);
            // Ordena por visitas descendente
            usort($ranking, fn($a, $b) => $b['visitas'] <=> $a['visitas']);
            // Asigna posición
            $i = 1;
            foreach ($ranking as &$c)
                $c['posicion'] = $i++;

            // Top 5 para el gráfico
            $top = array_slice($ranking, 0, 5);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => array_values($ranking), // todos los que han comprado
                    'top' => array_values($top)
                ]
            ]);
            break;

        case 'clientesMayorHistorial':
            $clienteDAO = new ClienteDAO($db);
            $compraDAO = new CompraDAO($db);

            $clientes = $clienteDAO->readAll();
            // Filtra solo clientes con totalHistorico > 0 (opcional)
            $ranking = array_filter($clientes, fn($c) => $c->totalHistorico > 0);
            // Ordena por totalHistorico descendente
            usort($ranking, fn($a, $b) => $b->totalHistorico <=> $a->totalHistorico);

            // Obtiene todas las compras
            $compras = $compraDAO->readAll();

            // Indexa compras por cliente
            $comprasPorCliente = [];
            foreach ($compras as $compra) {
                $comprasPorCliente[$compra->idCliente][] = $compra->fechaCompra;
            }

            // Asigna posición y última compra a cada cliente
            $i = 1;
            foreach ($ranking as &$c) {
                $c->posicion = $i++;
                // Busca última compra (la fecha más reciente)
                if (!empty($comprasPorCliente[$c->id])) {
                    $c->ultimaCompra = max($comprasPorCliente[$c->id]);
                } else {
                    $c->ultimaCompra = null;
                }
            }
            unset($c); // Por si acaso

            // Top 5 para el gráfico
            $top = array_slice($ranking, 0, 7);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => array_values($ranking),
                    'top' => array_values($top)
                ]
            ]);
            break;
        case 'clientesInactivos':
            //$diasInactivo = 30; // <--- CAMBIA AQUÍ EL PERÍODO
            $diasInactivo = 0.0003; // 30 segundos PARA PRUEBA

            $clienteDAO = new ClienteDAO($db);
            $compraDAO = new CompraDAO($db);
            $clientes = $clienteDAO->readAll();
            $compras = $compraDAO->readAll();

            // Indexa compras por cliente
            $comprasPorCliente = [];
            foreach ($compras as $compra) {
                $comprasPorCliente[$compra->idCliente][] = $compra->fechaCompra;
            }

            $inactivos = [];
            $hoy = new DateTime();

            foreach ($clientes as $c) {
                // Última compra (la más reciente)
                $ultimaCompra = null;
                if (!empty($comprasPorCliente[$c->id])) {
                    $ultimaCompra = max($comprasPorCliente[$c->id]);
                    $diasSinComprar = (new DateTime($ultimaCompra))->diff($hoy)->days;
                } else {
                    // Nunca ha comprado, puedes excluir o incluir con lógica especial
                    $diasSinComprar = null;
                }

                if ($diasSinComprar !== null && $diasSinComprar > $diasInactivo) {
                    $inactivos[] = [
                        'id' => $c->id,
                        'nombre' => $c->nombre,
                        'cedula' => $c->cedula,
                        'telefono' => $c->telefono,
                        'ultimaCompra' => $ultimaCompra,
                        'diasSinComprar' => $diasSinComprar,
                        'totalHistorico' => $c->totalHistorico,
                    ];
                }
            }

            // Ordenar por más tiempo sin comprar
            usort($inactivos, fn($a, $b) => $b['diasSinComprar'] <=> $a['diasSinComprar']);

            // Top 7
            $top = array_slice($inactivos, 0, 7);

            echo json_encode([
                'success' => true,
                'diasInactivo' => $diasInactivo,
                'data' => [
                    'ranking' => array_values($inactivos),
                    'top' => array_values($top)
                ]
            ]);
            break;


        case 'residenciasFrecuentes':
            $clienteDAO = new ClienteDAO($db);
            $clientes = $clienteDAO->readAll();

            // Agrupar clientes por residencia
            $porResidencia = [];
            foreach ($clientes as $c) {
                $res = trim($c->lugarResidencia) ?: '(SIN RESIDENCIA)';
                if (!isset($porResidencia[$res]))
                    $porResidencia[$res] = [];
                $porResidencia[$res][] = [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'cedula' => $c->cedula
                ];
            }

            // Armar ranking (residencia, cantidad, clientes)
            $ranking = [];
            foreach ($porResidencia as $res => $clientesRes) {
                $ranking[] = [
                    'residencia' => $res,
                    'cantidad' => count($clientesRes),
                    'clientes' => $clientesRes
                ];
            }

            usort($ranking, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

            $top = array_slice($ranking, 0, 7);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => $ranking,
                    'top' => $top
                ]
            ]);
            break;

        case 'clientesAntiguos':
            $clienteDAO = new ClienteDAO($db);
            $clientes = $clienteDAO->readAll();

            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll();

            $hoy = new DateTime();

            // Armar array de compras por cliente
            $comprasPorCliente = [];
            foreach ($compras as $c) {
                $cid = $c->idCliente;
                if (!isset($comprasPorCliente[$cid]))
                    $comprasPorCliente[$cid] = [];
                $comprasPorCliente[$cid][] = $c;
            }

            $ranking = [];
            foreach ($clientes as $cli) {
                // Antigüedad en días
                $fechaRegistro = new DateTime($cli->fechaRegistro);
                $antiguedadDias = $fechaRegistro->diff($hoy)->days;

                // Buscar última compra
                $ultCompra = null;
                if (isset($comprasPorCliente[$cli->id]) && count($comprasPorCliente[$cli->id])) {
                    $ultCompraObj = array_reduce(
                        $comprasPorCliente[$cli->id],
                        function ($carry, $item) {
                            return (!$carry || $item->fechaCompra > $carry->fechaCompra) ? $item : $carry;
                        }
                    );
                    $ultCompra = $ultCompraObj->fechaCompra;
                    $diasSinComprar = (new DateTime($ultCompra))->diff($hoy)->days;
                } else {
                    $ultCompra = null;
                    $diasSinComprar = $antiguedadDias;
                }

                $ranking[] = [
                    'id' => $cli->id,
                    'nombre' => $cli->nombre,
                    'cedula' => $cli->cedula,
                    'antiguedadDias' => $antiguedadDias,
                    'diasSinComprar' => $diasSinComprar,
                    'totalHistorico' => $cli->totalHistorico,
                    'ultimaCompra' => $ultCompra,
                ];
            }

            // Ordenar de mayor a menor antigüedad
            usort($ranking, fn($a, $b) => $b['antiguedadDias'] <=> $a['antiguedadDias']);

            // Posiciones y top
            $i = 1;
            foreach ($ranking as &$c)
                $c['posicion'] = $i++;
            $top = array_slice($ranking, 0, 7);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => array_values($ranking),
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
