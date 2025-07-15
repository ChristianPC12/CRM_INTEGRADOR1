<?php
// Archivo: view/BitacoraView.php

// Obtener los datos por HTTP desde el controlador
$bitacoraJson = file_get_contents('http://localhost/CRM_INT/CRM/controller/BitacoraController.php?action=readAll');
$expiracionJson = file_get_contents('http://localhost/CRM_INT/CRM/controller/BitacoraController.php?action=verificarExpiracion');

$bitacoraArray = json_decode($bitacoraJson, true);
$mostrarAlerta = json_decode($expiracionJson, true)['mostrarAlerta'] ?? false;
$bitacoras = $bitacoraArray['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bitácora</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <h2>Bitácora de Usuarios</h2>
  <div class="alert">Los registros se eliminan automáticamente al cumplir 30 días. Puede exportar un respaldo en PDF o Excel antes de esa fecha.</div>
  <?php if ($mostrarAlerta): ?>
    <div class="alert">
      <i class="fas fa-exclamation-triangle"></i>
      Algunos registros serán eliminados en 5 días. Se recomienda realizar un respaldo.
    </div>
  <?php endif; ?>
  <button class="btn btn-pdf" onclick="exportToPDF()">Exportar PDF</button>
  <button class="btn btn-excel" onclick="exportToExcel()">Exportar Excel</button>

 <table id="tablaBitacora">
  <thead>
    <tr>
      <th>ID</th>
      <th>ID Usuario</th>
      <th>Hora Entrada</th>
      <th>Hora Salida</th>
      <th>Fecha</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($bitacoras as $b): ?>
      <tr>
        <td><?= $b['id'] ?></td>
        <td><?= $b['idUsuario'] ?></td>
        <td><?= $b['horaEntrada'] ?></td>
        <td><?= $b['horaSalida'] ?></td>
        <td><?= $b['fecha'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <script>
    async function exportToPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.text("Bitácora de Usuarios", 10, 10);
      const rows = [];
      const table = document.getElementById("tablaBitacora");
      for (let i = 1; i < table.rows.length; i++) {
        const row = [];
        for (let j = 0; j < table.rows[i].cells.length; j++) {
          row.push(table.rows[i].cells[j].innerText);
        }
        rows.push(row);
      }
      doc.autoTable({
        head: [[...Array.from(table.rows[0].cells).map(th => th.innerText)]],
        body: rows,
        startY: 20
      });
      doc.save("bitacora.pdf");
    }

    function exportToExcel() {
      const table = document.getElementById("tablaBitacora");
      const html = table.outerHTML.replace(/ /g, "%20");
      const a = document.createElement("a");
      a.href = "data:application/vnd.ms-excel," + html;
      a.download = "bitacora.xls";
      a.click();
    }
  </script>
</body>
</html>
