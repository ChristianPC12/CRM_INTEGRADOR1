// BitacoraFunciones.js

function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.text("Bitácora de Usuarios", 10, 10);

  const table = document.getElementById("tablaBitacora");
  const rows = [];
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
