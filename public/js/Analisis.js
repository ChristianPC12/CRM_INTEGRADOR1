// public/js/Analisis.js

/**
 * Script de análisis de clientes y ventas.
 *
 * Funcionalidades principales:
 * - Ranking de clientes frecuentes, inactivos, antiguos y con mayor historial.
 * - Ranking de residencias frecuentes.
 * - Estadísticas de ventas por mes y año.
 * - Gráficos dinámicos con Chart.js.
 * - Buscador en vivo adaptado a cada tipo de análisis.
 */

// Guarda el ranking actual para filtrar en vivo
let datosAnalisisActual = [];
// Si estamos en modo residencias frecuentes (busca por residencia)
let modoResidencia = false;
// Guarda la función del análisis actual para el botón "Actualizar"
let lastAnalisisFuncion = mostrarClientesFrecuentes;

document.addEventListener("DOMContentLoaded", () => {
  /**
   * === Asignación de eventos a los botones de análisis ===
   */
  document
    .getElementById("btnClientesFrecuentes")
    .addEventListener("click", () => {
      lastAnalisisFuncion = mostrarClientesFrecuentes;
      mostrarClientesFrecuentes();
    });
  document
    .getElementById("btnClientesMayorHistorial")
    .addEventListener("click", () => {
      lastAnalisisFuncion = mostrarClientesMayorHistorial;
      mostrarClientesMayorHistorial();
    });
  document
    .getElementById("btnClientesInactivos")
    .addEventListener("click", () => {
      lastAnalisisFuncion = mostrarClientesInactivos;
      mostrarClientesInactivos();
    });
  document
    .getElementById("btnResidenciasFrecuentes")
    .addEventListener("click", () => {
      lastAnalisisFuncion = mostrarResidenciasFrecuentes;
      mostrarResidenciasFrecuentes();
    });
  document
    .getElementById("btnClientesAntiguos")
    .addEventListener("click", () => {
      lastAnalisisFuncion = mostrarClientesAntiguos;
      mostrarClientesAntiguos();
    });

  document.getElementById("btnVentasPorMes").addEventListener("click", () => {
    lastAnalisisFuncion = mostrarVentasPorMes;
    mostrarVentasPorMes();
  });

  document.getElementById("btnVentasPorAnio").addEventListener("click", () => {
    lastAnalisisFuncion = mostrarVentasPorAnio;
    mostrarVentasPorAnio();
  });

  // Botón de actualizar recarga el análisis actual
  document
    .getElementById("btnActualizarAnalisis")
    .addEventListener("click", () => {
      if (lastAnalisisFuncion) lastAnalisisFuncion();
    });

  /**
   * === Buscador general dinámico ===
   * Filtra la tabla dependiendo del análisis en curso
   */
  document
    .getElementById("analisisBuscadorGeneral")
    .addEventListener("input", function () {
      const value = this.value.trim().toLowerCase();
      let filtrados = [];

      // --- Residencias frecuentes (busca por lugar de residencia) ---
      if (modoResidencia) {
        filtrados = !value
          ? datosAnalisisActual
          : datosAnalisisActual.filter((r) =>
              (r.residencia ?? "").toLowerCase().includes(value)
            );
        renderResidenciasFrecuentesTabla(filtrados);

        // --- Ventas por mes ---
      } else if (lastAnalisisFuncion === mostrarVentasPorMes) {
        filtrados = !value
          ? datosAnalisisActual
          : datosAnalisisActual.filter(
              (v) =>
                (v.mes ?? "").toLowerCase().includes(value) ||
                (v.año ?? "").toString().includes(value)
            );
        renderTablaVentasPorMes(filtrados);

        // --- Ventas por año ---
      } else if (lastAnalisisFuncion === mostrarVentasPorAnio) {
        filtrados = !value
          ? datosAnalisisActual
          : datosAnalisisActual.filter((v) =>
              (v.año ?? "").toString().includes(value)
            );
        renderTablaVentasPorAnio(filtrados);

        // --- Otros análisis (buscan por nombre) ---
      } else {
        filtrados = !value
          ? datosAnalisisActual
          : datosAnalisisActual.filter((c) =>
              (c.nombre ?? "").toLowerCase().includes(value)
            );
        renderTablaAnalisisActual(filtrados);
      }
    });

  /**
   * === Detectar parámetro URL para auto-seleccionar sección ===
   */
  const urlParams = new URLSearchParams(window.location.search);
  const seccion = urlParams.get("seccion");

  // Cargar sección específica o la por defecto
  switch (seccion) {
    case "ventasPorMes":
      console.log("Auto-cargando: Ventas por mes");
      lastAnalisisFuncion = mostrarVentasPorMes;
      mostrarVentasPorMes();
      break;
    case "ventasPorAnio":
      console.log("Auto-cargando: Ventas por año");
      lastAnalisisFuncion = mostrarVentasPorAnio;
      mostrarVentasPorAnio();
      break;
    case "clientesFrecuentes":
      console.log("Auto-cargando: Clientes frecuentes");
      lastAnalisisFuncion = mostrarClientesFrecuentes;
      mostrarClientesFrecuentes();
      break;
    case "clientesMayorHistorial":
      console.log("Auto-cargando: Clientes con mayor historial");
      lastAnalisisFuncion = mostrarClientesMayorHistorial;
      mostrarClientesMayorHistorial();
      break;
    case "clientesInactivos":
      console.log("Auto-cargando: Clientes inactivos");
      lastAnalisisFuncion = mostrarClientesInactivos;
      mostrarClientesInactivos();
      break;
    case "residenciasFrecuentes":
      console.log("Auto-cargando: Residencias frecuentes");
      lastAnalisisFuncion = mostrarResidenciasFrecuentes;
      mostrarResidenciasFrecuentes();
      break;
    case "clientesAntiguos":
      console.log("Auto-cargando: Clientes antiguos");
      lastAnalisisFuncion = mostrarClientesAntiguos;
      mostrarClientesAntiguos();
      break;
    default:
      // Por defecto, carga el primer análisis al entrar
      mostrarClientesFrecuentes();
      break;
  }
});

/**
 * === ANÁLISIS 1: CLIENTES MÁS FRECUENTES ===
 * Llama al backend y muestra clientes con más visitas
 */
async function mostrarClientesFrecuentes() {
  setBotonActivo("btnClientesFrecuentes");
  modoResidencia = false;

  // Limpiar y mostrar loader
  setBuscador("Buscar por nombre...");
  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = loaderHTML();
  graficoCont.innerHTML = "";

  try {
    const res = await fetch("/CRM_INT/CRM/controller/AnalisisController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=clientesFrecuentes",
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const ranking = json.data.ranking;
    datosAnalisisActual = ranking; // Actualiza ranking para filtro
    const top = json.data.top;

    renderTablaAnalisisActual(ranking);

    // Gráfico Top 5
    renderGraficoBarras({
      cont: graficoCont,
      idCanvas: "graficoFrecuentes",
      labels: top.map((c) =>
        c.nombre.length > 15 ? c.nombre.slice(0, 15) + "…" : c.nombre
      ),
      data: top.map((c) => c.visitas),
      label: "Visitas",
      color: "var(--amarillo)",
      tooltipTitle: (i) => top[i].nombre,
      tooltipLabel: (i) => [
        `Visitas: ${top[i].visitas}`,
        `Tarjeta: ${top[i].id}`,
      ],
    });
  } catch (e) {
    tablaCont.innerHTML = errorHTML(e.message);
    graficoCont.innerHTML = "";
  }
}

/**
 * === ANÁLISIS 2: CLIENTES CON MAYOR HISTORIAL DE COMPRAS ===
 */
async function mostrarClientesMayorHistorial() {
  setBotonActivo("btnClientesMayorHistorial");
  modoResidencia = false;
  setBuscador("Buscar por nombre...");
  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = loaderHTML();
  graficoCont.innerHTML = "";

  try {
    const res = await fetch("/CRM_INT/CRM/controller/AnalisisController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=clientesMayorHistorial",
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const ranking = json.data.ranking;
    datosAnalisisActual = ranking;
    const top = json.data.top;

    renderTablaAnalisisActual(ranking);

    renderGraficoBarras({
      cont: graficoCont,
      idCanvas: "graficoMayorHistorial",
      labels: top.map((c) =>
        c.nombre.length > 15 ? c.nombre.slice(0, 15) + "…" : c.nombre
      ),
      data: top.map((c) => c.totalHistorico),
      label: "Total histórico",
      color: "var(--amarillo)",
      tooltipTitle: (i) => top[i].nombre,
      tooltipLabel: (i) => [
        `Total histórico: ₡${parseFloat(top[i].totalHistorico).toLocaleString(
          "es-CR"
        )}`,

        `Tarjeta: ${top[i].id}`,
      ],
    });
  } catch (e) {
    tablaCont.innerHTML = errorHTML(e.message);
    graficoCont.innerHTML = "";
  }
}

/**
 * === ANÁLISIS 3: CLIENTES INACTIVOS ===
 * Muestra clientes que no han comprado en X días
 */
async function mostrarClientesInactivos() {
  setBotonActivo("btnClientesInactivos");
  modoResidencia = false;
  setBuscador("Buscar por nombre...");
  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = loaderHTML();
  graficoCont.innerHTML = "";

  try {
    const res = await fetch("/CRM_INT/CRM/controller/AnalisisController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=clientesInactivos",
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const ranking = json.data.ranking;
    const top = json.data.top;
    const usarSegundos = json.usarSegundos;
    const limite = json.limite;

    // 👇 Guarda el modo y el límite para el renderer de tabla
    window.__modoInactivosSegundos = usarSegundos;
    window.__limiteInactivos = limite;

    datosAnalisisActual = ranking;

    if (!ranking.length) {
      const unidad = usarSegundos ? "segundos" : "días";
      tablaCont.innerHTML = `<div class="alert alert-success mt-4">Aún no hay clientes que lleven más de ${limite} ${unidad} sin comprar.</div>`;
      graficoCont.innerHTML = "";
      return;
    }

    // Render tabla (tu función existente)
    renderTablaAnalisisActual(ranking);

    // 📊 Gráfico: si es "Nunca", pinta 0 para no romper el eje, pero el tooltip dirá "Nunca"
    const labels = top.map((c) =>
      c.nombre.length > 15 ? c.nombre.slice(0, 15) + "…" : c.nombre
    );
    const datos = top.map((c) => {
      if (usarSegundos) return c.nuncaCompro ? 0 : c.segundosSinComprar ?? 0;
      return c.nuncaCompro ? 0 : c.diasSinComprar ?? 0;
    });

    renderGraficoBarras({
      cont: graficoCont,
      idCanvas: "graficoInactivos",
      labels,
      data: datos,
      label: usarSegundos
        ? "Tiempo sin comprar (segundos)"
        : "Tiempo sin comprar (días)",
      color: "var(--amarillo)",
      tooltipTitle: (i) => top[i].nombre,
      tooltipLabel: (i) => {
        const c = top[i];
        const txtTiempo = usarSegundos
          ? c.nuncaCompro
            ? "Nunca"
            : formatearSegundos(c.segundosSinComprar)
          : c.nuncaCompro
          ? "Nunca"
          : formatearAntiguedad(c.diasSinComprar);
        return [`Tiempo sin comprar: ${txtTiempo}`, `Tarjeta: ${c.id}`];
      },
    });
  } catch (e) {
    tablaCont.innerHTML = errorHTML(e.message);
    graficoCont.innerHTML = "";
  }
}

function formatearSegundos(segundos) {
  if (segundos === null || segundos === undefined) return "Nunca";
  const m = Math.floor(segundos / 60);
  const s = segundos % 60;
  if (m === 0) return `${segundos} segundos`;
  if (s === 0) return `${m} ${m === 1 ? "minuto" : "minutos"}`;
  return `${m} ${m === 1 ? "minuto" : "minutos"}, ${s} segundos`;
}


/**
 * === ANÁLISIS 4: RESIDENCIAS MÁS FRECUENTES ===
 */
async function mostrarResidenciasFrecuentes() {
  setBotonActivo("btnResidenciasFrecuentes");
  modoResidencia = true;
  setBuscador("Buscar por lugar de residencia...");
  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = loaderHTML();
  graficoCont.innerHTML = "";

  try {
    const res = await fetch("/CRM_INT/CRM/controller/AnalisisController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=residenciasFrecuentes",
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const ranking = json.data.ranking;
    datosAnalisisActual = ranking;
    const top = json.data.top;

    renderResidenciasFrecuentesTabla(ranking);

    renderGraficoBarras({
      cont: graficoCont,
      idCanvas: "graficoResidencias",
      labels: top.map((r) =>
        r.residencia.length > 15
          ? r.residencia.slice(0, 15) + "…"
          : r.residencia
      ),
      data: top.map((r) => r.cantidad),
      label: "Clientes",
      color: "var(--amarillo)",
      tooltipTitle: (i) => top[i].residencia,
      tooltipLabel: (i) => [`Clientes: ${top[i].cantidad}`],
    });
  } catch (e) {
    tablaCont.innerHTML = errorHTML(e.message);
    graficoCont.innerHTML = "";
  }
}

/**
 * === ANÁLISIS 5: CLIENTES MÁS ANTIGUOS ===
 */
async function mostrarClientesAntiguos() {
  setBotonActivo("btnClientesAntiguos");
  modoResidencia = false;
  setBuscador("Buscar por nombre...");
  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = loaderHTML();
  graficoCont.innerHTML = "";

  try {
    const res = await fetch("/CRM_INT/CRM/controller/AnalisisController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=clientesAntiguos",
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const ranking = json.data.ranking;
    datosAnalisisActual = ranking;
    const top = json.data.top;

    renderTablaAnalisisActual(ranking);

    renderGraficoBarras({
      cont: graficoCont,
      idCanvas: "graficoAntiguos",
      labels: top.map((c) =>
        c.nombre.length > 15 ? c.nombre.slice(0, 15) + "…" : c.nombre
      ),
      data: top.map((c) => c.antiguedadDias),
      label: "Antigüedad (días)",
      color: "var(--amarillo)",
      indexAxis: "y",
      tooltipTitle: (i) => top[i].nombre,
      tooltipLabel: (i) => [
        `Antigüedad: ${formatearAntiguedad(top[i].antiguedadDias)}`,
        `Tarjeta: ${top[i].id}`,
      ],
    });
  } catch (e) {
    tablaCont.innerHTML = errorHTML(e.message);
    graficoCont.innerHTML = "";
  }
}

/**
 * ========= FUNCIONES DE AYUDA / UI =========
 */

// Pone el botón de análisis activo con color
function setBotonActivo(idBtn) {
  document
    .querySelectorAll(".btn-analisis")
    .forEach((b) => b.classList.remove("active"));
  document.getElementById(idBtn).classList.add("active");
}

// Cambia el placeholder y limpia el input de búsqueda
function setBuscador(texto) {
  const buscador = document.getElementById("analisisBuscadorGeneral");
  if (buscador) {
    buscador.value = "";
    buscador.placeholder = texto;
  }
}

// Loader estándar
function loaderHTML() {
  return `<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>`;
}

// Mensaje de error estándar
function errorHTML(msg) {
  return `<div class="alert alert-danger">Error: ${msg}</div>`;
}

// Utilidad para convertir días a formato legible (años, meses, semanas, días)
function formatearAntiguedad(dias) {
  const años = Math.floor(dias / 365);
  const meses = Math.floor((dias % 365) / 30.44);
  const semanas = Math.floor(((dias % 365) % 30.44) / 7);
  const diasRestantes = Math.round(((dias % 365) % 30.44) % 7);
  let partes = [];
  if (años) partes.push(años === 1 ? "1 año" : `${años} años`);
  if (meses) partes.push(meses === 1 ? "1 mes" : `${meses} meses`);
  if (semanas) partes.push(semanas === 1 ? "1 semana" : `${semanas} semanas`);
  if (diasRestantes)
    partes.push(diasRestantes === 1 ? "1 día" : `${diasRestantes} días`);
  return partes.length ? partes.join(", ") : "Hoy";
}

/**
 * Redibuja la tabla según el análisis activo y el array filtrado
 */
function renderTablaAnalisisActual(arr) {
  // Detecta el análisis activo por el botón con .active
  const idActivo = document.querySelector(".btn-analisis.active")?.id;
  const tablaCont = document.getElementById("analisisTablaCont");
  if (!idActivo || !tablaCont) return;

  switch (idActivo) {
    case "btnClientesFrecuentes":
      tablaCont.innerHTML = `
        <table class="table table-striped table-hover align-middle mb-0 rounded">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Cédula</th>
              <th>Teléfono</th>
              <th>Visitas</th>
              <th>Última visita</th>
            </tr>
          </thead>
          <tbody>
            ${arr
              .map(
                (cliente) => `
                  <tr>
                    <td>${cliente.posicion}</td>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.cedula}</td>
                    <td>${cliente.telefono ?? "-"}</td>
                    <td>${cliente.visitas}</td>
                    <td>${cliente.ultimaVisita ?? "-"}</td>
                  </tr>
                `
              )
              .join("")}
          </tbody>
        </table>
      `;
      break;
    case "btnClientesMayorHistorial":
      tablaCont.innerHTML = `
        <table class="table table-striped table-hover align-middle mb-0 rounded">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Cédula</th>
              <th>Teléfono</th>
              <th>Total Histórico</th>
              <th>Última compra</th>
            </tr>
          </thead>
          <tbody>
            ${arr
              .map(
                (cliente) => `
                  <tr>
                    <td>${cliente.posicion}</td>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.cedula}</td>
                    <td>${cliente.telefono ?? "-"}</td>
                    <td>₡${parseFloat(cliente.totalHistorico).toLocaleString(
                      "es-CR"
                    )}</td>
                    <td>${cliente.ultimaCompra ?? "-"}</td>
                  </tr>
                `
              )
              .join("")}
          </tbody>
        </table>
      `;
      break;
    case "btnClientesInactivos": {
      const usarSegundos = window.__modoInactivosSegundos === true;

      tablaCont.innerHTML = `
    <div style="max-height:370px;overflow:auto">
      <table class="table table-striped table-hover align-middle mb-0 rounded">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Teléfono</th>
            <th>Última compra</th>
            <th>Tiempo sin comprar</th>
            <th>Total histórico</th>
          </tr>
        </thead>
        <tbody>
          ${arr
            .map(
              (c, i) => `
            <tr>
              <td>${i + 1}</td>
              <td>${c.nombre}</td>
              <td>${c.cedula}</td>
              <td>${c.telefono ?? "-"}</td>
              <td>${c.ultimaCompra ?? "-"}</td>
              <td>${
                c.nuncaCompro
                  ? "Nunca"
                  : usarSegundos
                  ? formatearSegundos(c.segundosSinComprar)
                  : formatearAntiguedad(c.diasSinComprar)
              }</td>
              <td>₡${parseFloat(c.totalHistorico).toLocaleString("es-CR")}</td>
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    </div>
  `;
      break;
    }

    case "btnClientesAntiguos":
      tablaCont.innerHTML = `
        <table class="table table-striped table-hover align-middle mb-0 rounded">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Cédula</th>
              <th>Antigüedad</th>
              <th>Días sin comprar</th>
              <th>Total histórico</th>
            </tr>
          </thead>
          <tbody>
            ${arr
              .map(
                (cliente) => `
                  <tr>
                    <td>${cliente.posicion}</td>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.cedula}</td>
                    <td>${formatearAntiguedad(cliente.antiguedadDias)}</td>
                    <td>${formatearAntiguedad(cliente.diasSinComprar)}</td>
                    <td>₡${parseFloat(cliente.totalHistorico).toLocaleString(
                      "es-CR"
                    )}</td>
                  </tr>
                `
              )
              .join("")}
          </tbody>
        </table>
      `;
      break;
  }
}

/**
 * Dibuja la tabla de residencias frecuentes (con collapses)
 */
function renderResidenciasFrecuentesTabla(arr) {
  const tablaCont = document.getElementById("analisisTablaCont");
  if (!tablaCont) return;
  if (!arr.length) {
    tablaCont.innerHTML = `<div class="alert alert-info mt-4">No hay datos de residencia disponibles.</div>`;
    return;
  }
  let filas = arr
    .map((r, i) => {
      const collapseId = "collapseRes" + i;
      return `
        <tr>
          <td>${i + 1}</td>
          <td>${r.residencia}</td>
          <td>${r.cantidad}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#${collapseId}"
                    aria-expanded="false" aria-controls="${collapseId}">
              Ver nombres
            </button>
          </td>
        </tr>
        <tr>
          <td colspan="4" class="bg-light p-0">
            <div class="collapse" id="${collapseId}">
              <div style="max-height:150px;overflow:auto; padding: 1rem;">
                <ul class="mb-0 small">
                  ${r.clientes
                    .map(
                      (c) => `
                        <li><strong>${c.nombre}</strong> <span class="text-muted">— Tarjeta: ${c.id}</span></li>
                      `
                    )
                    .join("")}
                </ul>
              </div>
            </div>
          </td>
        </tr>
      `;
    })
    .join("");

  tablaCont.innerHTML = `
    <div style="max-height:370px;overflow:auto">
      <table class="table table-striped table-hover align-middle mb-0 rounded">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Lugar de residencia</th>
            <th>Clientes</th>
            <th></th>
          </tr>
        </thead>
        <tbody>${filas}</tbody>
      </table>
    </div>
  `;
}

/**
 * Gráfico de barras usando Chart.js
 */
function renderGraficoBarras({
  cont,
  idCanvas,
  labels,
  data,
  label,
  color,
  tooltipTitle,
  tooltipLabel,
  indexAxis,
}) {
  cont.innerHTML = `<canvas id="${idCanvas}"></canvas>`;
  const ctx = document.getElementById(idCanvas).getContext("2d");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels,
      datasets: [
        {
          label,
          data,
          backgroundColor: color,
          borderRadius: 10,
        },
      ],
    },
    options: {
      indexAxis: indexAxis || "x",
      plugins: {
        tooltip: {
          callbacks: {
            title: function (context) {
              return tooltipTitle(context[0].dataIndex);
            },
            label: function (context) {
              return tooltipLabel(context.dataIndex);
            },
          },
        },
      },
    },
  });
}

/**
 * Redibuja la tabla de ventas por mes
 */
function renderTablaVentasPorMes(arr) {
  const tablaCont = document.getElementById("analisisTablaCont");
  let filas = arr
    .map((v, i) => {
      let varHtml = "-";
      if (typeof v.variacion === "number") {
        varHtml =
          v.variacion > 0
            ? `<span class="text-success fw-bold">+${v.variacion}% <i class="fa fa-arrow-up"></i></span>`
            : `<span class="text-danger fw-bold">${v.variacion}% <i class="fa fa-arrow-down"></i></span>`;
      }
      return `
      <tr>
        <td>${v.posicion}</td>
        <td>${v.mes}</td>
        <td>${v.año}</td>
        <td>₡${parseFloat(v.total).toLocaleString("es-CR")}</td>
        <td>${varHtml}</td>
      </tr>
    `;
    })
    .join("");

  tablaCont.innerHTML = `
    <table class="table table-striped table-hover align-middle mb-0 rounded">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Mes</th>
          <th>Año</th>
          <th>Ventas Totales</th>
          <th>Var. Mensual (%)</th>
        </tr>
      </thead>
      <tbody>${filas}</tbody>
    </table>
  `;
}

/**
 * Muestra análisis de ventas por mes
 */
async function mostrarVentasPorMes() {
  setBotonActivo("btnVentasPorMes");
  modoResidencia = false;
  setBuscador("Buscar por mes o año...");
  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = loaderHTML();
  graficoCont.innerHTML = "";

  try {
    const res = await fetch("/CRM_INT/CRM/controller/AnalisisController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=ventasPorMes",
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const ventas = json.data.ventas;
    datosAnalisisActual = ventas;

    renderTablaVentasPorMes(ventas);

    // Gráfico
    if (ventas.length) {
      const labels = ventas.map((v) => `${v.mes} ${v.año}`).reverse();
      const data = ventas.map((v) => v.total).reverse();
      graficoCont.innerHTML = `<canvas id="graficoVentasMes"></canvas>`;
      const ctx = document.getElementById("graficoVentasMes").getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: "Ventas Totales",
              data,
              backgroundColor: "var(--amarillo)",
              borderRadius: 10,
            },
          ],
        },
        options: {
          plugins: {
            tooltip: {
              callbacks: {
                title: function (context) {
                  return labels[context[0].dataIndex];
                },
                label: function (context) {
                  return `₡${parseFloat(data[context.dataIndex]).toLocaleString(
                    "es-CR"
                  )}`;
                },
              },
            },
          },
        },
      });
    }
  } catch (e) {
    tablaCont.innerHTML = errorHTML(e.message);
    graficoCont.innerHTML = "";
  }
}

/**
 * Muestra análisis de ventas por año
 */
async function mostrarVentasPorAnio() {
  setBotonActivo("btnVentasPorAnio");
  modoResidencia = false;
  setBuscador("Buscar por año...");
  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = loaderHTML();
  graficoCont.innerHTML = "";

  try {
    const res = await fetch("/CRM_INT/CRM/controller/AnalisisController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=ventasPorAnio",
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const ventas = json.data.ventas;
    datosAnalisisActual = ventas;

    renderTablaVentasPorAnio(ventas);

    // Gráfico de barras (años en orden ascendente)
    if (ventas.length) {
      const labels = ventas.map((v) => v.año).reverse();
      const data = ventas.map((v) => v.total).reverse();
      graficoCont.innerHTML = `<canvas id="graficoVentasAnio"></canvas>`;
      const ctx = document.getElementById("graficoVentasAnio").getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: "Ventas Totales",
              data,
              backgroundColor: "var(--amarillo)",
              borderRadius: 10,
            },
          ],
        },
        options: {
          plugins: {
            tooltip: {
              callbacks: {
                title: function (context) {
                  return `Año ${labels[context[0].dataIndex]}`;
                },
                label: function (context) {
                  return `₡${parseFloat(data[context.dataIndex]).toLocaleString(
                    "es-CR"
                  )}`;
                },
              },
            },
          },
        },
      });
    }
  } catch (e) {
    tablaCont.innerHTML = errorHTML(e.message);
    graficoCont.innerHTML = "";
  }
}

/**
 * Redibuja la tabla de ventas por año
 */
function renderTablaVentasPorAnio(arr) {
  const tablaCont = document.getElementById("analisisTablaCont");
  let filas = arr
    .map((v, i) => {
      let varHtml = "-";
      if (typeof v.variacion === "number") {
        varHtml =
          v.variacion > 0
            ? `<span class="text-success fw-bold">+${v.variacion}% <i class="fa fa-arrow-up"></i></span>`
            : `<span class="text-danger fw-bold">${v.variacion}% <i class="fa fa-arrow-down"></i></span>`;
      }
      return `
      <tr>
        <td>${v.posicion}</td>
        <td>${v.año}</td>
        <td>₡${parseFloat(v.total).toLocaleString("es-CR")}</td>
        <td>${varHtml}</td>
      </tr>
    `;
    })
    .join("");

  tablaCont.innerHTML = `
    <table class="table table-striped table-hover align-middle mb-0 rounded">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Año</th>
          <th>Ventas Totales</th>
          <th>Var. Anual (%)</th>
        </tr>
      </thead>
      <tbody>${filas}</tbody>
    </table>
  `;
}
