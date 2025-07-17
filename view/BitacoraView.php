<?php
$expiracionJson = file_get_contents('http://localhost/CRM_INT/CRM/controller/BitacoraController.php?action=verificarExpiracion');
$mostrarAlerta = json_decode($expiracionJson, true)['mostrarAlerta'] ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bitácora</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Bitacora.css?v=<?= time() ?>">
</head>
<body>
  <h2>Bitácora de Usuarios</h2>

  <div class="alert">
    Los registros se eliminan automáticamente al cumplir 30 días. 
    Puede exportar un respaldo en PDF o Excel antes de esa fecha.
  </div>

  <?php if ($mostrarAlerta): ?>
    <div class="alert">
      <i class="fas fa-exclamation-triangle"></i>
      Algunos registros serán eliminados en 5 días. Se recomienda realizar un respaldo.
    </div>
  <?php endif; ?>

  <button class="btn btn-pdf" onclick="exportToPDF()">Exportar PDF</button>
  <button class="btn btn-excel" onclick="exportToExcel()">Exportar Excel</button>

  <div id="contenedorTabla" class="table-container"></div>

  <!-- Librerías necesarias -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

  <!-- Tu JS personalizado -->
  <script src="/CRM_INT/CRM/public/js/Bitacora.js?v=<?= time() ?>"></script>
</body>
</html>
