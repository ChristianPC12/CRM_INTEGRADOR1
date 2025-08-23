/**
 * ============================
 * Gestión de Bitácora de Usuarios
 * ============================
 *
 * Funciones principales:
 * - Cargar la bitácora de usuarios desde el backend.
 * - Mostrar los registros en tabla HTML.
 * - Calcular duración de sesiones.
 * - Exportar la tabla a PDF y Excel.
 */

/**
 * Espera a que el contenedor de la tabla esté disponible en el DOM
 * antes de cargar los datos de la bitácora.
 */
function esperarContenedorYcargar() {
  const contenedor = document.getElementById("contenedorTabla");

  if (!contenedor) {
    console.warn("⏳ Esperando a que cargue #contenedorTabla...");
    return setTimeout(esperarContenedorYcargar, 100);
  }

  console.log("📦 contenedorTabla encontrado, cargando bitácora...");
  cargarBitacora();
}

// Ejecutar cuando se cargue el DOM
document.addEventListener("DOMContentLoaded", esperarContenedorYcargar);

/**
 * Carga los registros de bitácora desde el backend,
 * construye la tabla y la inserta en el contenedor.
 */
async function cargarBitacora() {
  try {
    const res = await fetch(
      `http://${location.hostname}/CRM_INT/CRM/controller/BitacoraController.php?action=readAll`
    );

    const text = await res.text();
    console.log("📦 Respuesta cruda:", text);
    const json = JSON.parse(text);

    const contenedor = document.getElementById("contenedorTabla");
    if (!contenedor) return;

    if (!json.success || !json.data || json.data.length === 0) {
      contenedor.innerHTML = "<p>No hay datos para mostrar.</p>";
      return;
    }

    /**
     * Calcula la duración de la sesión a partir de la hora de entrada y salida.
     * @param {string} horaEntrada - Hora de entrada en formato HH:mm:ss
     * @param {string} horaSalida - Hora de salida en formato HH:mm:ss
     * @returns {string} Duración en formato `Xh Xm Xs` o `EN CURSO`
     */
    function calcularDuracion(horaEntrada, horaSalida) {
  if (!horaSalida || horaSalida === "00:00:00") return "EN CURSO";

  const [hEntH, hEntM, hEntS] = horaEntrada.split(":").map(Number);
  const [hSalH, hSalM, hSalS] = horaSalida.split(":").map(Number);

  // Usamos una fecha base fija
  const entrada = new Date(2000, 0, 1, hEntH, hEntM, hEntS);
  let salida = new Date(2000, 0, 1, hSalH, hSalM, hSalS);

  // Si salida < entrada, asumimos que cruzó medianoche (+1 día)
  if (salida < entrada) {
    salida.setDate(salida.getDate() + 1);
  }

  const diffMs = salida - entrada;
  if (diffMs < 0 || !Number.isFinite(diffMs)) return "Error";

  const horas   = Math.floor(diffMs / 3600000);
  const minutos = Math.floor((diffMs % 3600000) / 60000);
  const segundos = Math.floor((diffMs % 60000) / 1000);

  return `${horas}h ${minutos}m ${segundos}s`;
}

    // Construcción dinámica de la tabla
    let tablaHTML = `
      <table id="tablaBitacora" border="1" cellpadding="5" cellspacing="0">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Hora Entrada</th>
            <th>Hora Salida</th>
            <th>Fecha</th>
            <th>Duración</th>
          </tr>
        </thead>
        <tbody>
          ${json.data
            .map((b) => {
              const duracion = calcularDuracion(b.horaEntrada, b.horaSalida);
              return `
            <tr>
              <td>${b.nombreUsuario}</td>
              <td>${b.horaEntrada}</td>
              <td>${b.horaSalida}</td>
              <td>${b.fecha}</td>
              <td>${duracion}</td>
            </tr>
            `;
            })
            .join("")}
        </tbody>
      </table>
    `;

    contenedor.innerHTML = tablaHTML;
  } catch (e) {
    console.error("❌ Error al cargar bitácora:", e);
    const contenedor = document.getElementById("contenedorTabla");
    if (contenedor) contenedor.innerHTML = "<p>Error al cargar los datos.</p>";
  }
}

/**
 * Exporta la tabla de bitácora a PDF usando jsPDF y autoTable.
 */
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
    head: [[...Array.from(table.rows[0].cells).map((th) => th.innerText)]],
    body: rows,
    startY: 20,
  });

  doc.save("bitacora.pdf");
}

/**
 * Exporta la tabla de bitácora a Excel (formato .xls).
 */
function exportToExcel() {
  const table = document.getElementById("tablaBitacora");
  if (!table) return;

  const html = table.outerHTML.replace(/ /g, "%20");
  const a = document.createElement("a");
  a.href = "data:application/vnd.ms-excel," + html;
  a.download = "bitacora.xls";
  a.click();
}
