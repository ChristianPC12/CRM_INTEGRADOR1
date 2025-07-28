<div class="container guia-container mt-4">
  <h2 class="guia-titulo text-center mb-4">
    <i class="bi bi-info-circle"></i> Manual de uso del sistema
  </h2>

  <div class="row row-cols-1 row-cols-md-2 g-4">
    <!-- DASHBOARD -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-house"></i>
          <span class="guia-link-seccion" data-ir-a="?view=dashboard">Dashboard</span>
        </div>
        <div class="guia-body">
          Aquí ves datos rápidos como ventas y cumpleaños.  
          Propietarios y admins ven métricas exclusivas.  
          También puedes gestionar tareas pendientes.  
          Saloneros solo acceden a sus tareas del día.
        </div>
      </div>
    </div>

    <!-- CLIENTES VIP -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-award-fill"></i>
          <span class="guia-link-seccion" data-ir-a="?view=clientes">Clientes VIP</span>
        </div>
        <div class="guia-body">
          Puedes registrar, editar y reasignar tarjetas.  
          Admins solo pueden ver y editar información.  
          Saloneros pueden ver, pero no modificar nada.  
          Las tarjetas reasignadas cambian su código.
        </div>
      </div>
    </div>

    <!-- USUARIOS -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-person"></i>
          <span class="guia-link-seccion" data-ir-a="?view=usuarios">Usuarios</span>
        </div>
        <div class="guia-body">
          Solo el propietario accede a esta sección.  
          Aquí se crean usuarios con roles y claves.  
          Puedes editar, eliminar y buscar usuarios.  
          Ni admins ni saloneros pueden entrar aquí.
        </div>
      </div>
    </div>

    <!-- BENEFICIOS -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-tag"></i>
          <span class="guia-link-seccion" data-ir-a="?view=compras">Beneficios</span>
        </div>
        <div class="guia-body">
          Busca un cliente para registrar su compra.  
          Puedes acumular puntos o aplicar descuentos.  
          El historial se guarda debajo automáticamente.  
          Solo el propietario puede eliminar registros.
        </div>
      </div>
    </div>

    <!-- CÓDIGO DE BARRAS -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-upc-scan"></i>
          <span class="guia-link-seccion" data-ir-a="?view=codigo">Código de barras</span>
        </div>
        <div class="guia-body">
          Imprime o guarda el código de cada tarjeta.  
          Escanearlo abre directamente el perfil cliente.  
          El sistema detecta la tarjeta automáticamente.  
          Ahorra tiempo y evita errores en la búsqueda.
        </div>
      </div>
    </div>

    <!-- ANÁLISIS -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-graph-up-arrow"></i>
          <span class="guia-link-seccion" data-ir-a="?view=analisis">Análisis</span>
        </div>
        <div class="guia-body">
          Solo propietarios y admins pueden ingresar.  
          Muestra gráficos de clientes y estadísticas.  
          Puedes buscar por nombre o zona de origen.  
          Incluye ventas por mes, año y más métricas.
        </div>
      </div>
    </div>

    <!-- CUMPLEAÑOS -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-gift"></i>
          <span class="guia-link-seccion" data-ir-a="?view=cumple">Cumpleaños</span>
        </div>
        <div class="guia-body">
          Muestra clientes que cumplen años esta semana.  
          Puedes enviar correos o registrar llamadas.  
          El estado cambia a “lista” si ya fue atendido.  
          Todos los roles pueden acceder sin restricción.
        </div>
      </div>
    </div>

    <!-- BITÁCORA -->
    <div class="col">
      <div class="guia-card shadow">
        <div class="guia-header">
          <i class="bi bi-journal-text"></i>
          <span class="guia-link-seccion" data-ir-a="?view=Bitacora">Bitácora</span>
        </div>
        <div class="guia-body">
          Solo el propietario puede ver esta sección.  
          Muestra quién entró, cuándo y cuánto duró.  
          Puedes descargar en Word o en PDF si deseas.  
          Los datos se eliminan automáticamente al mes.
        </div>
      </div>
    </div>

   <!-- LOGO DE BASTOS -->
<div class="col">
  <div class="guia-card shadow">
    <div class="guia-header">
      <i class="bi bi-box-arrow-in-down-left"></i>
      <span class="guia-link-seccion" onclick="abrirModal()">Logo de Bastos</span>
    </div>
    <div class="guia-body">
      Al hacer clic en el logo se abre una ventana.  
      Puedes ingresar el número de una tarjeta.  
      El sistema busca al cliente automáticamente.  
      Te lleva directo a la sección de beneficios.
    </div>
  </div>
</div>


<!-- CERRAR SESIÓN -->
<div class="col">
  <div class="guia-card shadow">
    <div class="guia-header">
      <i class="bi bi-box-arrow-right"></i>
      <span class="guia-link-seccion" onclick="window.location.href='/CRM_INT/CRM/controller/UsuarioController.php?action=logout'">
        Cerrar Sesión
      </span>
    </div>
    <div class="guia-body">
      Este botón cierra tu sesión de forma segura.  
      Si estás inactivo por 30 minutos, se cerrará.  
      Para volver a entrar, pide tu contraseña.  
      Ayuda a proteger la información del sistema.
    </div>
  </div>
</div>


<!-- Script del manual -->
<script src="/CRM_INT/CRM/public/js/Manual.js?v=1"></script>
