<?php
require_once __DIR__ . '/../controller/BitacoraController.php';

$controller = new BitacoraController();
$bitacoras = $controller->readAll();
$mostrarAlerta = $controller->mostrarAvisoExpiracion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bitácora</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background-color: #f9c41f; color: #000; }
    .alert { background: #fff3cd; padding: 15px; border: 1px solid #ffeeba; border-radius: 5px; color: #856404; margin-bottom: 20px; }
    .btn { padding: 10px 15px; border: none; border-radius: 4px; margin-right: 10px; cursor: pointer; }
    .btn-pdf { background: #d9534f; color: #fff; }
    .btn-excel { background: #5cb85c; color: #fff; }
  </style>
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
        <th>Usuario</th>
        <th>Rol</th>
        <th>Hora Entrada</th>
        <th>Hora Salida</th>
        <th>Duración</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bitacoras as $b): ?>
        <tr>
          <td><?= $b->id ?></td>
          <td><?= $b->usuario ?></td>
          <td><?= $b->rol ?></td>
          <td><?= $b->horaEntrada ?></td>
          <td><?= $b->horaSalida ?></td>
          <td><?= $b->duracion ?></td>
          <td><?= $b->fecha ?></td>
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
