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
            $clienteDAO = new ClienteDAO($db);
            $compraDAO = new CompraDAO($db);

            $clientes = $clienteDAO->readAll();   // ClienteDTO: id, nombre, cedula, telefono, ...
            $compras = $compraDAO->readAll();    // CompraDTO: idCliente, total, fechaCompra

            // Agregar compras por cliente (SUM y MAX)
            $agg = []; // idCliente => ['total' => n, 'ultima' => 'YYYY-MM-DD']
            foreach ($compras as $co) {
                $cid = (int) $co->idCliente;
                if (!isset($agg[$cid])) {
                    $agg[$cid] = ['total' => 0, 'ultima' => null];
                }
                $agg[$cid]['total'] += (float) $co->total;
                if ($agg[$cid]['ultima'] === null || $co->fechaCompra > $agg[$cid]['ultima']) {
                    $agg[$cid]['ultima'] = $co->fechaCompra;
                }
            }

            // Construir ranking solo con quienes tienen compras (>0)
            $ranking = [];
            foreach ($clientes as $c) {
                $cid = (int) $c->id;
                $total = isset($agg[$cid]) ? (float) $agg[$cid]['total'] : 0;
                if ($total <= 0)
                    continue;

                $ranking[] = [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'cedula' => $c->cedula,
                    'telefono' => $c->telefono,
                    'totalHistorico' => $total,
                    'ultimaCompra' => $agg[$cid]['ultima'] ?? null,
                ];
            }

            // Ordenar por total desc y luego por última compra desc
            usort($ranking, function ($a, $b) {
                if ($b['totalHistorico'] == $a['totalHistorico']) {
                    return strcmp($b['ultimaCompra'] ?? '', $a['ultimaCompra'] ?? '');
                }
                return $b['totalHistorico'] <=> $a['totalHistorico'];
            });

            // Posiciones y top
            $i = 1;
            foreach ($ranking as &$r) {
                $r['posicion'] = $i++;
            }
            unset($r);
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
            // Zona horaria consistente (Costa Rica)
            $tz = new DateTimeZone('America/Costa_Rica');
            $ahora = new DateTime('now', $tz);

            // Modo y umbrales - CAMBIO: 5 minutos para pruebas
            $usarSegundos = false;   // pruebas: 5 minutos
            //$limiteSegundos = 300;    // 5 min (cambiado de 120)
            $limiteDias = 30;     // producción si cambias a días

            $clienteDAO = new ClienteDAO($db);
            $compraDAO = new CompraDAO($db);
            $clientes = $clienteDAO->readAll();
            $compras = $compraDAO->readAll();

            // Indexa compras por cliente
            $comprasPorCliente = [];
            foreach ($compras as $compra) {
                // Asumimos $compra->idCliente y $compra->fechaCompra
                $comprasPorCliente[$compra->idCliente][] = $compra->fechaCompra;
            }

            $inactivos = [];

            foreach ($clientes as $c) {
                $tieneCompras = !empty($comprasPorCliente[$c->id]);

                $ultimaCompra = null;
                $diasSinComprar = null;
                $segundosSinComprar = null;
                $nuncaCompro = !$tieneCompras;

                if ($tieneCompras) {
                    // Normaliza fechas - Si es solo DATE, mantenemos sin hora para detectarlo
                    $fechas = array_map(static function ($f) use ($tz) {
                        $f = trim((string) $f);
                        if ($f === '')
                            return null;

                        $esSoloFecha = (strlen($f) === 10); // Y-m-d
                        if ($esSoloFecha) {
                            // No agregamos hora para poder detectar que es solo fecha
                            try {
                                $dt = new DateTime($f . ' 00:00:00', $tz);
                                $dt->esSoloFecha = true; // Marcamos que es solo fecha
                                return $dt;
                            } catch (Exception $e) {
                                return null;
                            }
                        } else {
                            // Tiene hora específica
                            try {
                                $dt = new DateTime($f, $tz);
                                $dt->esSoloFecha = false;
                                return $dt;
                            } catch (Exception $e) {
                                return null;
                            }
                        }
                    }, $comprasPorCliente[$c->id]);

                    // Filtra inválidas y toma la más reciente
                    $fechas = array_values(array_filter($fechas));
                    if (!empty($fechas)) {
                        usort($fechas, fn($a, $b) => $b <=> $a);
                        $ultimaDT = $fechas[0];

                        // CAMBIO: Solo guardamos la fecha para mostrar, no datetime
                        $ultimaCompra = $ultimaDT->format('Y-m-d');

                        // LÓGICA CORREGIDA: Detectar si es solo fecha (tipo DATE) vs datetime completo
                        $esHoy = ($ultimaDT->format('Y-m-d') === $ahora->format('Y-m-d'));
                        $esSoloFecha = isset($ultimaDT->esSoloFecha) && $ultimaDT->esSoloFecha;

                        if ($esHoy && $esSoloFecha) {
                            // Es compra de hoy pero solo tenemos la fecha (tipo DATE en BD)
                            // La consideramos como reciente - no debe aparecer como inactiva
                            $segundosSinComprar = 0;
                            $diasSinComprar = 0;
                        } else {
                            // Cálculo normal para fechas con hora específica o días anteriores
                            $segundosSinComprar = $ahora->getTimestamp() - $ultimaDT->getTimestamp();
                            $diasSinComprar = $ultimaDT->diff($ahora)->days;

                            if ($segundosSinComprar < 0) {
                                $segundosSinComprar = 0;
                                $diasSinComprar = 0;
                            }
                        }
                    } else {
                        // Todas las fechas resultaron inválidas
                        $ultimaCompra = null;
                        $segundosSinComprar = PHP_INT_MAX;
                        $diasSinComprar = PHP_INT_MAX;
                        $nuncaCompro = true;
                    }
                } else {
                    // Cliente sin compras
                    $ultimaCompra = null;
                    $segundosSinComprar = PHP_INT_MAX;
                    $diasSinComprar = PHP_INT_MAX;
                }

                // Regla de inactividad:
                // - Mostrar si NUNCA compró
                // - O si lleva >= 5 minutos sin comprar (modo segundos)
                // - (Si cambias a días, aplicará el límite de días)
                $esInactivo = false;
                if ($nuncaCompro) {
                    $esInactivo = true;
                } elseif ($usarSegundos && $segundosSinComprar !== null) {
                    $esInactivo = ($segundosSinComprar >= $limiteSegundos);
                } elseif (!$usarSegundos && $diasSinComprar !== null) {
                    $esInactivo = ($diasSinComprar >= $limiteDias);
                }

                if ($esInactivo) {
                    $inactivos[] = [
                        'id' => $c->id,
                        'nombre' => $c->nombre,
                        'cedula' => $c->cedula,
                        'telefono' => $c->telefono,
                        'ultimaCompra' => $ultimaCompra,        // null si nunca, solo fecha Y-m-d
                        'diasSinComprar' => $diasSinComprar,      // PHP_INT_MAX si nunca
                        'segundosSinComprar' => $segundosSinComprar,  // PHP_INT_MAX si nunca
                        'nuncaCompro' => $nuncaCompro,
                        'totalHistorico' => $c->totalHistorico,
                    ];
                }
            }

            // Ordenar: "Nunca" arriba (tratado como infinito) y luego por tiempo descendente
            usort(
                $inactivos,
                function ($a, $b) use ($usarSegundos) {
                    if ($usarSegundos) {
                        $va = $a['nuncaCompro'] ? PHP_INT_MAX : (int) $a['segundosSinComprar'];
                        $vb = $b['nuncaCompro'] ? PHP_INT_MAX : (int) $b['segundosSinComprar'];
                    } else {
                        $va = $a['nuncaCompro'] ? PHP_INT_MAX : (int) $a['diasSinComprar'];
                        $vb = $b['nuncaCompro'] ? PHP_INT_MAX : (int) $b['diasSinComprar'];
                    }
                    return $vb <=> $va; // descendente
                }
            );

            $top = array_slice($inactivos, 0, 7);

            echo json_encode([
                'success' => true,
                'usarSegundos' => $usarSegundos,
                'limite' => $usarSegundos ? $limiteSegundos : $limiteDias,
                'data' => [
                    'ranking' => array_values($inactivos),
                    'top' => array_values($top),
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
            $clienteDAO = new ClienteDAO($db);
            $compraDAO = new CompraDAO($db);

            $clientes = $clienteDAO->readAll();   // ClienteDTO con fechaRegistro
            $compras = $compraDAO->readAll();    // CompraDTO: idCliente, fechaCompra, total

            $hoy = new DateTime();

            // Agrupar compras por cliente
            $comprasPorCliente = [];
            foreach ($compras as $co) {
                $cid = (int) $co->idCliente;
                if (!isset($comprasPorCliente[$cid])) {
                    $comprasPorCliente[$cid] = [];
                }
                $comprasPorCliente[$cid][] = $co;
            }

            $ranking = [];
            foreach ($clientes as $cli) {
                $cid = (int) $cli->id;

                // Antigüedad = días desde el registro
                $fechaRegistro = new DateTime($cli->fechaRegistro);
                $antiguedadDias = $fechaRegistro->diff($hoy)->days;

                // Total histórico desde compras
                $totalHistorico = 0;
                $ultimaCompra = null;
                $diasSinComprar = $antiguedadDias; // por defecto igual a toda la antigüedad

                if (isset($comprasPorCliente[$cid])) {
                    foreach ($comprasPorCliente[$cid] as $co) {
                        $totalHistorico += (float) $co->total;
                        if ($ultimaCompra === null || $co->fechaCompra > $ultimaCompra) {
                            $ultimaCompra = $co->fechaCompra;
                        }
                    }
                    if ($ultimaCompra) {
                        $diasSinComprar = (new DateTime($ultimaCompra))->diff($hoy)->days;
                    }
                }

                $ranking[] = [
                    'id' => $cli->id,
                    'nombre' => $cli->nombre,
                    'cedula' => $cli->cedula,
                    'antiguedadDias' => $antiguedadDias,
                    'diasSinComprar' => $diasSinComprar,
                    'totalHistorico' => $totalHistorico,
                    'ultimaCompra' => $ultimaCompra,
                ];
            }

            // Ordenar por mayor antigüedad
            usort($ranking, fn($a, $b) => $b['antiguedadDias'] <=> $a['antiguedadDias']);

            // Asignar posiciones
            $i = 1;
            foreach ($ranking as &$r) {
                $r['posicion'] = $i++;
            }
            unset($r);

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
