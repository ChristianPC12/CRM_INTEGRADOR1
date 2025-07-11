// public/js/Analisis.js

document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("btnClientesFrecuentes")
    .addEventListener("click", mostrarClientesFrecuentes);

  // Puedes hacer que por defecto cargue el primer análisis:
  mostrarClientesFrecuentes();
});

async function mostrarClientesFrecuentes() {
  document
    .querySelectorAll(".btn-analisis")
    .forEach((b) => b.classList.remove("active"));
  document.getElementById("btnClientesFrecuentes").classList.add("active");

  const tablaCont = document.getElementById("analisisTablaCont");
  const graficoCont = document.getElementById("analisisGraficoCont");
  tablaCont.innerHTML = `<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>`;
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
    const top = json.data.top;

    // Tabla
    let filas = ranking
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
      .join("");

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
                <tbody>${filas}</tbody>
            </table>
        `;

    // Gráfico
    if (top && top.length) {
      const labels = top.map((c) =>
        c.nombre.length > 15 ? c.nombre.slice(0, 15) + "…" : c.nombre
      );
      const data = top.map((c) => c.visitas);
      graficoCont.innerHTML = `<canvas id="graficoFrecuentes"></canvas>`;
      const ctx = document.getElementById("graficoFrecuentes").getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: "Visitas",
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
                  // Solo el nombre (una vez)
                  return top[context[0].dataIndex].nombre;
                },
                label: function (context) {
                  const cliente = top[context.dataIndex];
                  return [
                    `Visitas: ${cliente.visitas}`,
                    `Tarjeta: ${cliente.id}`, // ID de tabla cliente
                  ];
                },
              },
            },
          },
        },
      });
    } else {
      graficoCont.innerHTML = `<div class="alert alert-info mt-3">No hay datos suficientes para mostrar el gráfico.</div>`;
    }
  } catch (e) {
    tablaCont.innerHTML = `<div class="alert alert-danger">Error: ${e.message}</div>`;
    graficoCont.innerHTML = "";
  }
}
