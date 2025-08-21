<?php
// controller/AnalisisController.php

// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');
// Incluye la configuración de la base de datos y los DAOs necesarios
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/cliente/ClienteDAO.php';
require_once __DIR__ . '/../model/compra/CompraDAO.php'; // Asegúrate de tener este DAO y Mapper igual que los otros

try {
    // Obtiene la conexión a la base de datos
    $db = (new Database())->getConnection();
    if (!$db)
        throw new Exception("No se pudo conectar a la base de datos");

    // Determina la acción a ejecutar a partir de POST o GET
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'clientesFrecuentes':
            // Obtiene todos los clientes
            $clienteDAO = new ClienteDAO($db);
            $clientes = $clienteDAO->readAll(); // array de objetos ClienteDTO

            // Obtiene todas las compras
            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll(); // array de objetos CompraDTO

            // Inicializa la estructura para contar visitas y registrar la última visita por cliente
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

            // Recorre todas las compras y actualiza el contador de visitas y la fecha de última visita
            foreach ($compras as $compra) {
                $cid = $compra->idCliente;
                if (isset($visitasPorCliente[$cid])) {
                    $visitasPorCliente[$cid]['visitas']++;
                    // Actualiza la última visita si es más reciente
                    if (
                        !$visitasPorCliente[$cid]['ultimaVisita'] ||
                        $compra->fechaCompra > $visitasPorCliente[$cid]['ultimaVisita']
                    ) {
                        $visitasPorCliente[$cid]['ultimaVisita'] = $compra->fechaCompra;
                    }
                }
            }

            // Filtra solo los clientes que han realizado al menos una compra
            $ranking = array_filter($visitasPorCliente, fn($c) => $c['visitas'] > 0);
            // Ordena el ranking por cantidad de visitas descendente
            usort($ranking, fn($a, $b) => $b['visitas'] <=> $a['visitas']);
            // Asigna la posición en el ranking
            $i = 1;
            foreach ($ranking as &$c)
                $c['posicion'] = $i++;

            // Obtiene el top 5 de clientes para el gráfico
            $top = array_slice($ranking, 0, 5);

            // Devuelve el resultado en formato JSON
            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => array_values($ranking), // todos los que han comprado
                    'top' => array_values($top)
                ]
            ]);
            break;

        case 'clientesMayorHistorial':
            // Obtiene clientes y compras
            $clienteDAO = new ClienteDAO($db);
            $compraDAO = new CompraDAO($db);

            $clientes = $clienteDAO->readAll();
            // Filtra solo clientes con totalHistorico > 0
            $ranking = array_filter($clientes, fn($c) => $c->totalHistorico > 0);
            // Ordena por totalHistorico descendente
            usort($ranking, fn($a, $b) => $b->totalHistorico <=> $a->totalHistorico);

            // Obtiene todas las compras
            $compras = $compraDAO->readAll();

            // Indexa las compras por cliente (idCliente => array de fechas)
            $comprasPorCliente = [];
            foreach ($compras as $compra) {
                $comprasPorCliente[$compra->idCliente][] = $compra->fechaCompra;
            }

            // Asigna posición y última compra a cada cliente en el ranking
            $i = 1;
            foreach ($ranking as &$c) {
                $c->posicion = $i++;
                // Busca la fecha de la última compra
                if (!empty($comprasPorCliente[$c->id])) {
                    $c->ultimaCompra = max($comprasPorCliente[$c->id]);
                } else {
                    $c->ultimaCompra = null;
                }
            }
            unset($c); // Limpia la referencia

            // Obtiene el top 7 para el gráfico
            $top = array_slice($ranking, 0, 7);

            // Devuelve el resultado en formato JSON
            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => array_values($ranking),
                    'top' => array_values($top)
                ]
            ]);
            break;
        case 'clientesInactivos':
            // Define el período de inactividad (en días). Para pruebas, se usa un valor muy bajo (30 segundos)
            $diasInactivo = 30; // <--- CAMBIA AQUÍ EL PERÍODO
            //$diasInactivo = 0.0003; // 30 segundos PARA PRUEBA

            $clienteDAO = new ClienteDAO($db);
            $compraDAO = new CompraDAO($db);
            $clientes = $clienteDAO->readAll();
            $compras = $compraDAO->readAll();

            // Indexa las compras por cliente
            $comprasPorCliente = [];
            foreach ($compras as $compra) {
                $comprasPorCliente[$compra->idCliente][] = $compra->fechaCompra;
            }

            $inactivos = [];
            $hoy = new DateTime();

            // Recorre todos los clientes y determina si están inactivos
            foreach ($clientes as $c) {
                // Busca la última compra (la más reciente)
                $ultimaCompra = null;
                if (!empty($comprasPorCliente[$c->id])) {
                    $ultimaCompra = max($comprasPorCliente[$c->id]);
                    $diasSinComprar = (new DateTime($ultimaCompra))->diff($hoy)->days;
                } else {
                    // Si nunca ha comprado, se puede excluir o incluir según la lógica deseada
                    $diasSinComprar = null;
                }

                // Si el cliente supera el período de inactividad, se agrega al listado
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

            // Ordena los clientes inactivos por mayor tiempo sin comprar
            usort($inactivos, fn($a, $b) => $b['diasSinComprar'] <=> $a['diasSinComprar']);

            // Obtiene el top 7 de inactivos
            $top = array_slice($inactivos, 0, 7);

            // Devuelve el resultado en formato JSON
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
            // Obtiene todos los clientes
            $clienteDAO = new ClienteDAO($db);
            $clientes = $clienteDAO->readAll();

            // Agrupa los clientes por residencia
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

            // Construye el ranking de residencias más frecuentes
            $ranking = [];
            foreach ($porResidencia as $res => $clientesRes) {
                $ranking[] = [
                    'residencia' => $res,
                    'cantidad' => count($clientesRes),
                    'clientes' => $clientesRes
                ];
            }

            // Ordena por cantidad de clientes descendente
            usort($ranking, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

            // Obtiene el top 7 de residencias
            $top = array_slice($ranking, 0, 7);

            // Devuelve el resultado en formato JSON
            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => $ranking,
                    'top' => $top
                ]
            ]);
            break;

        case 'clientesAntiguos':
            // Obtiene todos los clientes
            $clienteDAO = new ClienteDAO($db);
            $clientes = $clienteDAO->readAll();

            // Obtiene todas las compras
            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll();

            $hoy = new DateTime();

            // Indexa las compras por cliente
            $comprasPorCliente = [];
            foreach ($compras as $c) {
                $cid = $c->idCliente;
                if (!isset($comprasPorCliente[$cid]))
                    $comprasPorCliente[$cid] = [];
                $comprasPorCliente[$cid][] = $c;
            }

            $ranking = [];
            foreach ($clientes as $cli) {
                // Calcula la antigüedad del cliente en días desde su registro
                $fechaRegistro = new DateTime($cli->fechaRegistro);
                $antiguedadDias = $fechaRegistro->diff($hoy)->days;

                // Busca la última compra del cliente
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

                // Agrega el cliente al ranking con los datos calculados
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

            // Ordena el ranking por mayor antigüedad
            usort($ranking, fn($a, $b) => $b['antiguedadDias'] <=> $a['antiguedadDias']);

            // Asigna posición y obtiene el top 7
            $i = 1;
            foreach ($ranking as &$c)
                $c['posicion'] = $i++;
            $top = array_slice($ranking, 0, 7);

            // Devuelve el resultado en formato JSON
            echo json_encode([
                'success' => true,
                'data' => [
                    'ranking' => array_values($ranking),
                    'top' => array_values($top)
                ]
            ]);
            break;

        case 'ventasPorMes':
            // Obtiene todas las compras
            $compraDAO = new CompraDAO($db);
            $compras = $compraDAO->readAll();

            // Arreglo de nombres de meses en español
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

            // Agrupa las compras por mes y año
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

            // Ordena los resultados por año y mes descendente
            usort($ventasPorMes, function ($a, $b) {
                return ($b['año'] * 100 + $b['mes']) - ($a['año'] * 100 + $a['mes']);
            });

            // Calcula la variación porcentual respecto al mes anterior
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

            // Limita a los últimos 12 meses
            $ventasConVar = array_slice($ventasConVar, 0, 12);

            // Devuelve el resultado en formato JSON
            echo json_encode([
                'success' => true,
                'data' => [
                    'ventas' => $ventasConVar,
                ],
            ]);
            break;

        case 'ventasPorAnio':
            // Obtiene todas las compras
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

            // Ordena los años de mayor a menor
            krsort($ventasPorAnio);

            // Calcula la variación porcentual anual respecto al año anterior
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

            // Limita a los últimos 10 años
            $ventasConVar = array_slice($ventasConVar, 0, 10);

            // Devuelve el resultado en formato JSON
            echo json_encode([
                'success' => true,
                'data' => [
                    'ventas' => $ventasConVar,
                ],
            ]);
            break;



        default:
            // Acción no válida
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
