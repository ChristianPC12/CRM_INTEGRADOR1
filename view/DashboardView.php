<?php
// ARCHIVO: DashboardView.php

date_default_timezone_set('America/Costa_Rica');
$hora = date('H');
$saludo = ($hora < 12) ? 'Buenos días + nombre de usuario' : (($hora < 18) ? 'Buenas tardes' : 'Buenas noches');
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
                NOMBRE DE USUARIO
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


        <!-- Sección de tareas pendientes (libretita visual) -->
        <!-- Sección de tareas pendientes (libretita visual) -->
        <div class="divisor-tareas">
            <section class="todo-section">
                <h3 class="todo-title">Tareas pendientes 📌</h3>

                <!-- Contenedor en GRID para evitar que se estire el form -->
                <div class="tareas-grid">
                    <!-- Formulario sin tocar -->
                    <form id="formTarea" class="formulario-tarea">
                        <input type="text" name="descripcion" id="descripcion" placeholder="Escriba su tarea..."
                            required>
                        <button type="submit" title="Agregar tarea">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                        <label id="descripcionInfo" class="info-descripcion"></label>
                        <label id="contadorCaracteres" class="contador-caracteres"></label>

                    </form>

            </section>

            <div class="contenedor-tarjetas-grid">
                <button id="btnEliminarTarea" class="btn-eliminar-tarea" title="Eliminar tarea actual">
                    <i class="bi bi-trash3-fill"></i>
                    <button id="flecha-izquierda" class="btn-flecha">&#8592;</button>

                    <div class="zona-central">
                        <div class="fecha-actual" id="fechaActual"></div>

                        <!-- AQUÍ es donde faltaba el contenedor para las tareas -->
                        <div id="contenedorTarjetas">
                            <!-- Aquí se insertarán dinámicamente las tarjetas de tareas -->
                        </div>

                        <!-- Estos botones probablemente deberían eliminarse ya que se crean dinámicamente -->
                        <!-- <button class="btn-estado">Estado</button>
        <button class="btn-cambiar">Cambiar estado</button> -->
                    </div>

                    <button id="flecha-derecha" class="btn-flecha">&#8594;</button>
            </div>



        </div>
        <!--<div class="topbar-dashboard" id="gris-1"></div>-->

    </div>




    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="/CRM_INT/CRM/public/js/Tarea.js"></script>

</body>

</html>