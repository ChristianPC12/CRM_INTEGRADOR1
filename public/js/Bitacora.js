function esperarContenedorYcargar() {
  const contenedor = document.getElementById("contenedorTabla");

  if (!contenedor) {
    console.warn("⏳ Esperando a que cargue #contenedorTabla...");
    return setTimeout(esperarContenedorYcargar, 100);
  }

  console.log("📦 contenedorTabla encontrado, cargando bitácora...");
  cargarBitacora();
}

document.addEventListener("DOMContentLoaded", esperarContenedorYcargar);

async function cargarBitacora() {
  try {
    const res = await fetch("http://localhost/CRM_INT/CRM/controller/BitacoraController.php?action=readAll");
    const text = await res.text();
    console.log("📦 Respuesta cruda:", text);
    const json = JSON.parse(text);

    const contenedor = document.getElementById("contenedorTabla");
    if (!contenedor) return;

    if (!json.success || !json.data || json.data.length === 0) {
      contenedor.innerHTML = "<p>No hay datos para mostrar.</p>";
      return;
    }

    function calcularDuracion(horaEntrada, horaSalida) {
      if (!horaSalida || horaSalida === "00:00:00") return "En curso";

      const [hEntH, hEntM, hEntS] = horaEntrada.split(":").map(Number);
      const [hSalH, hSalM, hSalS] = horaSalida.split(":").map(Number);

      const entrada = new Date(0, 0, 0, hEntH, hEntM, hEntS);
      const salida = new Date(0, 0, 0, hSalH, hSalM, hSalS);

      let diff = salida - entrada;
      if (diff < 0) return "Error";

      const horas = Math.floor(diff / 3600000);
      const minutos = Math.floor((diff % 3600000) / 60000);
      const segundos = Math.floor((diff % 60000) / 1000);

      return `${horas}h ${minutos}m ${segundos}s`;
    }

    let tablaHTML = `
      <div class="table-wrapper">
        <table id="tablaBitacora" border="1" cellpadding="5" cellspacing="0">
          <thead>
            <tr>
              <th>ID Usuario</th>
              <th>Hora Entrada</th>
              <th>Hora Salida</th>
              <th>Fecha</th>
              <th>Duración</th>
            </tr>
          </thead>
          <tbody>
            ${json.data.map(b => `
              <tr>
                <td>${b.idUsuario}</td>
                <td>${b.horaEntrada}</td>
                <td>${b.horaSalida}</td>
                <td>${b.fecha}</td>
                <td>${calcularDuracion(b.horaEntrada, b.horaSalida)}</td>
              </tr>
            `).join("")}
          </tbody>
        </table>
      </div>
    `;

    contenedor.innerHTML = tablaHTML;
  } catch (e) {
    console.error("❌ Error al cargar bitácora:", e);
    const contenedor = document.getElementById("contenedorTabla");
    if (contenedor) contenedor.innerHTML = "<p>Error al cargar los datos.</p>";
  }
}

function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.text("Bitácora de Usuarios", 10, 10);

  const table = document.getElementById("tablaBitacora");
  if (!table) return;

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
  if (!table) return;

  const html = table.outerHTML.replace(/ /g, "%20");
  const a = document.createElement("a");
  a.href = "data:application/vnd.ms-excel," + html;
  a.download = "bitacora.xls";
  a.click();
}

// ✅ ¡Fin del archivo sin líneas sueltas ni retornos de objetos!
