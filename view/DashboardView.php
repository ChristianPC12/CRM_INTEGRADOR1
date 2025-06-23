<?php
// ARCHIVO: DashboardView.php



date_default_timezone_set('America/Costa_Rica');

// 🔧 MODIFICADO: obtenemos el nombre de usuario desde sesión o mostramos 'Invitado'
$usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Invitado';

$hora = date('H');
$saludo = ($hora < 12) ? "Buenos días, $usuario" : (($hora < 18) ? "Buenas tardes, $usuario" : "Buenas noches, $usuario");
$fecha = date("d/m/Y");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Bastos - Dashboard</title>
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Dashboard.css">
</head>

<body>
    
    <div id="container-dashboard">
        <div class="diseño-todo">
            <div class="topbar-dashboard">

                <i class="bi bi-person-circle icon-user"></i>
                <?= htmlspecialchars($usuario) ?> <!-- 🔧 MODIFICADO: Mostrar nombre dinámicamente -->
                <span class="badge bg-warning text-dark ms-2">Admin</span>
            </div>
            <div class="topbar-dashboard" id="negro-1"></div>
            <div class="topbar-dashboard" id="amarillo-1"></div>
            <div class="topbar-dashboard" id="amarillo-2"></div>
            <div class="topbar-dashboard" id="negro-2"></div>
        </div>

        <!-- Panel de bienvenida -->
        <div class="diseño-welcome">
            <div class="todo-welcome">
                <div class="dashboard-welcome">
                    <h2><?= $saludo ?> 👋</h2>
                    <p>Bienvenido al CRM de Clientes VIP del Restaurante Bastos.</p>
                    <p>Fecha: <?= $fecha ?></p>
                </div>
                <div>
                    <img src="/CRM_INT/CRM/public/img/Principal_Negro.png" alt="Logo" class="img-dashboard">
                </div>
            </div>
            <div class="decorativo-welcome" id="negro-3"></div>
            <div class="decorativo-welcome" id="amarillo-3"></div>
        </div>

        <!-- Sección de tareas pendientes -->
        <div class="divisor-tareas">
            <section class="todo-section">
                <h3 class="todo-title">Tareas pendientes 📌</h3>

                <div class="tareas-grid">
                    <form id="formTarea" class="formulario-tarea">
                        <input type="text" name="descripcion" id="descripcion" placeholder="Escriba su tarea..." required>
                        <button type="submit" title="Agregar tarea">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                        <label id="descripcionInfo" class="info-descripcion"></label>
                        <label id="contadorCaracteres" class="contador-caracteres"></label>
                    </form>
                </div> <!-- 🔧 Cerrado correctamente -->

            </section>

            <div class="contenedor-tarjetas-grid">
                <button id="btnEliminarTarea" class="btn-eliminar-tarea" title="Eliminar tarea actual">
                    <i class="bi bi-trash3-fill"></i>
                </button> <!-- 🔧 Cerrado correctamente -->
                <button id="flecha-izquierda" class="btn-flecha">&#8592;</button>

                <div class="zona-central">
                    <div class="fecha-actual" id="fechaActual"></div>
                    <div id="contenedorTarjetas"></div>
                </div>

                <button id="flecha-derecha" class="btn-flecha">&#8594;</button>
            </div>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/CRM_INT/CRM/public/js/Tarea.js"></script>

</body>
</html>