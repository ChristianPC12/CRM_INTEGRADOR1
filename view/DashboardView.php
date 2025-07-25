<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CRM Bastos - Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Dashboard.css" />
</head>

<body data-nombre="<?= $_SESSION['usuario'] ?? 'Usuario' ?>">


  <div id="container-dashboard">

    <!-- Panal de metricas-->
  </div>
  <div class="resumen-metricas">

    <!-- Bienvenida -->
    <div class="tarjeta-metrica">
      <i class="bi bi-person-circle icon-metrica"></i>
      <h4>Bienvenido</h4>
      <p class="valor" id="bienvenida-nombre">Usuario</p>
      <span class="subvalor">
        Rol: <span id="bienvenida-rol">Invitado</span><br>
        ¡Nos alegra tenerte de vuelta!
      </span>
    </div>



    <!-- Cumpleaños -->
    <div class="tarjeta-metrica">
      <i class="bi bi-gift-fill icon-metrica"></i>
      <h4>Clientes que Cumplen Años este mes</h4>
      <p id="cumple-texto" style="font-size: 0.9em;"></p>
      <a href="CRM_INT/CRM/index.php?view=cumple" class="link-metrica" style="display: block; margin-top: 0.5rem;">Ver
        análisis</a>
    </div>


    <!-- Cliente del Mes -->
    <div class="tarjeta-metrica">
      <i class="bi bi-award-fill icon-metrica"></i>
      <h4>Cliente del Mes</h4>
      <p class="valor" id="cliente-mes-nombre">N/A</p>
      <span class="subvalor" id="cliente-mes-valor">$0</span>
      <a href="CRM_INT/CRM/index.php?view=analisis&seccion=clientesMayorHistorial" class="link-metrica">Ver análisis</a>
    </div>

    <!-- Total Ventas -->
    <div class="tarjeta-metrica">
      <i class="bi bi-currency-dollar icon-metrica"></i>
      <h4>Ventas del Mes Actual</h4>
      <p class="valor" id="total-ventas">$0</p>
      <a href="CRM_INT/CRM/index.php?view=analisis&seccion=ventasPorMes" class="link-metrica">Ver análisis</a>
    </div>


    <!-- Total Clientes -->
    <div class="tarjeta-metrica">
      <i class="bi bi-people-fill icon-metrica"></i>
      <h4>Total Clientes</h4>
      <p class="valor" id="total-clientes">0</p>
    </div>

    <!-- Total Ventas por Año -->
    <div class="tarjeta-metrica">
      <i class="bi bi-calendar-year icon-metrica"></i>
      <h4>Ventas del Año Actual</h4>
      <p class="valor" id="total-ventas-anio">$0</p>
      <a href="CRM_INT/CRM/index.php?view=analisis&seccion=ventasPorAnio" class="link-metrica">Ver análisis</a>
    </div>
  </div>
  <!-- Sección de tareas -->
  <div class="seccion-tareas">
    <h3 class="todo-title">
      <i class="bi bi-pin-angle-fill" style="color: var(--amarillo)"></i> Tareas
    </h3>

    <form id="formTarea" class="formulario-tarea">
      <input type="text" name="descripcion" id="descripcion" placeholder="Escriba una tarea (máx. 220 caracteres)"
        maxlength="220" required />
      <div class="contador-caracteres">
        Caracteres restantes: <span id="contador-caracteres">220</span>
      </div>

      <button type="submit">
        <i class="bi bi-plus-circle-fill"></i> Agregar
      </button>
    </form>

    <!-- Lista UL de tarjetas -->
    <ul class="todo-list" id="contenedorTarjetas">

    </ul>
  </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="/CRM_INT/CRM/public/js/Tarea.js"></script>
  <script src="/CRM_INT/CRM/public/js/Dashboard.js"></script>
</body>

</html>