<?php
// Llama al endpoint del controlador para conocer si debe mostrarse
// la alerta preventiva (registros por expirar en ~5 días).
// Sugerencia: reemplazar por cURL con timeout configurable.
$expiracionJson = file_get_contents('http://localhost/CRM_INT/CRM/controller/BitacoraController.php?action=verificarExpiracion');
$mostrarAlerta = json_decode($expiracionJson, true)['mostrarAlerta'] ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bitácora</title>
  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- Estilos del módulo (cache-busting de desarrollo) -->
  <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Bitacora.css?v=<?= time() ?>">
</head>
<body>
  <!-- aquí va el texto fijo que siempre se muestra, avisando que los registros se borran a los 30 días. -->
  <div class="alert">
    Los registros se eliminan automáticamente al cumplir 30 días. 
    Puede exportar un respaldo en PDF o Excel antes de esa fecha.
  </div>

  <?php if ($mostrarAlerta): ?>
    <!-- Alerta preventiva condicional (5 días antes) -->
    <div class="alert">
      <i class="fas fa-exclamation-triangle"></i>
      Algunos registros serán eliminados en 5 días. Se recomienda realizar un respaldo.
    </div>
  <?php endif; ?>

  <!-- Acciones de exportación a Pdf y Excell-->
  <button class="btn btn-pdf" onclick="exportToPDF()">Exportar PDF</button>
  <button class="btn btn-excel" onclick="exportToExcel()">Exportar Excel</button>

  <!-- en este div (una caja vacía en HTML) el archivo Bitacora.js 
   se encarga de poner la tabla con los registros cuando se cargue la página. -->
  <div id="contenedorTabla" class="table-container"></div>

  <!-- Librerías necesarias para PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  
  <!-- Lógica del módulo -->
  <script src="/CRM_INT/CRM/public/js/Bitacora.js?v=<?= time() ?>"></script>
</body>
</html>
