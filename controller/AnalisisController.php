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

        case 'ventasPorMes':
            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll();

            // Arreglo de meses en español
            $meses_es = [
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Septiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre'
            ];

            // Agrupar por mes/año
            $ventasPorMes = [];
            foreach ($compras as $compra) {
                $fecha = new DateTime($compra->fechaCompra);
                $mes = (int) $fecha->format('m');
                $año = (int) $fecha->format('Y');
                $key = $año . '-' . $mes;
                if (!isset($ventasPorMes[$key])) {
                    $ventasPorMes[$key] = [
                        'mes' => $mes,
                        'año' => $año,
                        'total' => 0,
                    ];
                }
                $ventasPorMes[$key]['total'] += $compra->total;
            }

            // Ordenar por año, mes descendente
            usort($ventasPorMes, function ($a, $b) {
                return ($b['año'] * 100 + $b['mes']) - ($a['año'] * 100 + $a['mes']);
            });

            // Calcular variación mensual
            $ventasConVar = [];
            for ($i = 0; $i < count($ventasPorMes); $i++) {
                $actual = $ventasPorMes[$i];
                $anterior = $ventasPorMes[$i + 1] ?? null;
                $variacion = null;
                if ($anterior && $anterior['total'] > 0) {
                    $variacion = round((($actual['total'] - $anterior['total']) / $anterior['total']) * 100, 1);
                }
                $ventasConVar[] = [
                    'posicion' => $i + 1,
                    'mes' => $meses_es[$actual['mes']],
                    'año' => $actual['año'],
                    'total' => $actual['total'],
                    'variacion' => $variacion,
                ];
            }

            // Solo los 12 últimos meses
            $ventasConVar = array_slice($ventasConVar, 0, 12);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ventas' => $ventasConVar,
                ],
            ]);
            break;

        case 'ventasPorAnio':
            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll();

            // Agrupa las ventas por año
            $ventasPorAnio = [];
            foreach ($compras as $compra) {
                $fecha = new DateTime($compra->fechaCompra);
                $anio = (int) $fecha->format('Y');
                if (!isset($ventasPorAnio[$anio])) {
                    $ventasPorAnio[$anio] = [
                        'año' => $anio,
                        'total' => 0,
                    ];
                }
                $ventasPorAnio[$anio]['total'] += $compra->total;
            }

            // Ordena de mayor a menor año
            krsort($ventasPorAnio);

            // Calcula la variación anual
            $ventasArray = array_values($ventasPorAnio);
            $ventasConVar = [];
            for ($i = 0; $i < count($ventasArray); $i++) {
                $actual = $ventasArray[$i];
                $anterior = $ventasArray[$i + 1] ?? null;
                $variacion = null;
                if ($anterior && $anterior['total'] > 0) {
                    $variacion = round((($actual['total'] - $anterior['total']) / $anterior['total']) * 100, 1);
                }
                $ventasConVar[] = [
                    'posicion' => $i + 1,
                    'año' => $actual['año'],
                    'total' => $actual['total'],
                    'variacion' => $variacion,
                ];
            }

            // Limita a los últimos 10 años (si hay más)
            $ventasConVar = array_slice($ventasConVar, 0, 10);

            echo json_encode([
                'success' => true,
                'data' => [
                    'ventas' => $ventasConVar,
                ],
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
