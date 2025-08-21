$(document).ready(function () {
  // -------------------------------
  // 🔹 Variables principales
  // -------------------------------
  const $inputDescripcion = $("#descripcion");      // Input de descripción de tarea
  const $contador = $("#contador-caracteres");      // Contador de caracteres

  const nombreUsuario = localStorage.getItem("nombreUsuario"); // Nombre guardado en localStorage
  const rolUsuario = localStorage.getItem("rolUsuario");       // Rol guardado en localStorage

  // -------------------------------
  // 🔹 Mostrar nombre y rol en bienvenida
  // -------------------------------
  if (nombreUsuario) {
    $("#bienvenida-nombre").text(nombreUsuario);
  }
  if (rolUsuario) {
    $("#bienvenida-rol").text(rolUsuario);
  }

  // -------------------------------
  // 🔹 Contador de caracteres (máx 220)
  // -------------------------------
  $inputDescripcion.on("input", function () {
    const max = 220;
    const restante = max - $(this).val().length; // Resta caracteres usados
    $contador.text(restante);                    // Actualiza contador
  });

  // -------------------------------
  // 🔹 Obtener clientes vía AJAX
  // -------------------------------
  $.ajax({
    url: "/CRM_INT/CRM/controller/ClienteController.php?action=readAll",
    type: "GET",
    dataType: "json",
    success: function (res) {
      if (res.success && res.data) {
        const clientes = res.data;

        // 👉 Total de clientes
        $("#total-clientes").text(clientes.length.toLocaleString());

        // 👉 Cliente del mes (el que más acumulado tiene)
        const clienteMes = clientes
          .filter(
            (c) =>
              c.acumulado && !isNaN(c.acumulado) && parseFloat(c.acumulado) > 0
          )
          .reduce(
            (max, cliente) =>
              cliente.acumulado > max.acumulado ? cliente : max,
            { acumulado: 0 }
          );

        if (clienteMes && clienteMes.nombre) {
          $("#cliente-mes-nombre").text(clienteMes.nombre);
          $("#cliente-mes-valor").text(formatearColones(clienteMes.acumulado));
        } else {
          $("#cliente-mes-nombre").text("N/A");
          $("#cliente-mes-valor").text(formatearColones(0));
        }

        // 👉 Clientes que cumplen años en el mes actual
        const mesActual = new Date().getMonth() + 1;
        const cumpleaneros = clientes.filter((cliente) => {
          if (!cliente.fechaCumpleanos) return false;
          const mes = new Date(cliente.fechaCumpleanos).getMonth() + 1;
          return mes === mesActual;
        });

        const limite = 3;
        if (cumpleaneros.length > 0) {
          const nombres = cumpleaneros.slice(0, limite).map((c) => c.nombre);
          const textoFinal =
            nombres.join(", ") +
            (cumpleaneros.length > limite ? " y más..." : "");
          $("#cumple-texto")
            .text(textoFinal)
            .attr("title", cumpleaneros.map((c) => c.nombre).join(", "));
        } else {
          $("#cumple-texto").text("No hay cumpleaños este mes");
        }
      }
    },
    error: function (xhr, status, error) {
      console.error("Error al obtener clientes:");
      console.log("Status:", status);
      console.log("Error:", error);
      console.log("Respuesta del servidor:", xhr.responseText);
    },
  });

  // -------------------------------
  // 🔹 Obtener datos de ventas vía AJAX
  // -------------------------------
  $.ajax({
    url: "/CRM_INT/CRM/controller/CompraController.php",
    method: "POST",
    data: { action: "readAll" },
    dataType: "json",
    success: function (response) {
      if (response.success && response.data) {
        const compras = response.data;
        const fechaActual = new Date();
        const mesActual = fechaActual.getMonth() + 1;
        const anioActual = fechaActual.getFullYear();

        // 👉 Filtrar compras del mes actual
        const comprasMesActual = compras.filter((compra) => {
          if (!compra.fechaCompra) return false;
          const fechaCompra = new Date(compra.fechaCompra);
          return (
            fechaCompra.getFullYear() === anioActual &&
            fechaCompra.getMonth() === mesActual - 1
          );
        });

        // 👉 Filtrar compras del año actual
        const comprasAnioActual = compras.filter((compra) => {
          if (!compra.fechaCompra) return false;
          const fechaCompra = new Date(compra.fechaCompra);
          return fechaCompra.getFullYear() === anioActual;
        });

        // 👉 Totales
        const totalVentasMes = comprasMesActual.reduce(
          (total, compra) => total + parseFloat(compra.total || 0),
          0
        );

        const totalVentasAnio = comprasAnioActual.reduce(
          (total, compra) => total + parseFloat(compra.total || 0),
          0
        );

        // 👉 Actualizar en el DOM
        $("#total-ventas").text(formatearColones(totalVentasMes));
        $("#total-ventas-anio").text(formatearColones(totalVentasAnio));

        console.log(
          `Ventas del mes ${mesActual}/${anioActual}:`,
          formatearColones(totalVentasMes)
        );
        console.log(
          `Ventas del año ${anioActual}:`,
          formatearColones(totalVentasAnio)
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error al obtener datos de ventas:");
      console.log("Status:", status);
      console.log("Error:", error);
      console.log("Respuesta del servidor:", xhr.responseText);
    },
  });

  // -------------------------------
  // 🔹 Resetear contador de caracteres
  // -------------------------------
  function resetearContador() {
    $contador.text(220);
  }

  // -------------------------------
  // 🔹 Resetear contador al enviar tarea
  // -------------------------------
  $(document).on("submit", "#formTarea", function () {
    setTimeout(function () {
      if ($inputDescripcion.val() === "") {
        resetearContador(); // Solo si el input se limpió
      }
    }, 100);
  });
});

// -------------------------------
// 🔹 Función auxiliar: formatear a colones (₡)
// -------------------------------
function formatearColones(valor) {
  return new Intl.NumberFormat("es-CR", {
    style: "currency",
    currency: "CRC",
    minimumFractionDigits: 0,
  }).format(valor);
}
