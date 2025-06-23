// Archivo: Compra.js
// Lógica principal para la vista de compra/canje de puntos

document.addEventListener("DOMContentLoaded", function () {
  // 1. Gestión de selección de botones (Compra/Descuento)
  const btns = document.querySelectorAll(".compra-btn-opcion");
  btns.forEach((btn) => {
    btn.addEventListener("click", function () {
      btns.forEach((b) => b.classList.remove("selected"));
      this.classList.add("selected");
    });
  });

  // Variables de referencia
  let idClienteActual = null;
  let nombreClienteActual = null; // Guarda el nombre para la tabla

  // 2. Lógica del botón Buscar / Acumular
  const btnBuscar = document.getElementById("compraBtnBuscar");
  const btnBuscarIcon = document.getElementById("compraBtnBuscarIcon");

  // Event listener para el botón principal
  btnBuscar.addEventListener("click", async function () {
    await manejarBusquedaOAcumulacion();
  });

  // Event listener para el ícono de búsqueda
  btnBuscarIcon.addEventListener("click", async function () {
    await manejarBusquedaNueva();
  });

  // Función para manejar la búsqueda nueva (cuando se presiona el ícono)
  async function manejarBusquedaNueva() {
    const inputId = document.getElementById("compraInputId").value.trim();
    const errorDiv = document.getElementById("compraMensaje");
    const btnCompra = document.getElementById("compraOpcionCompra");

    // Validar que hay texto en el input
    if (!inputId) {
      alert("Ingrese el número de tarjeta para buscar.");
      return;
    }

    // Resetear el estado del botón a modo búsqueda
    btnBuscar.style.background = "var(--amarillo)";
    btnBuscar.style.color = "var(--negro)";
    btnBuscar.textContent = "Buscar";
    btnBuscarIcon.style.display = "none";

    // Realizar la búsqueda
    await buscarCliente(inputId);
  }

  // Función principal para manejar búsqueda o acumulación
  async function manejarBusquedaOAcumulacion() {
    const inputId = document.getElementById("compraInputId").value.trim();
    const errorDiv = document.getElementById("compraMensaje");
    const cardForm = document.getElementById("compraCardForm");
    const indicacion = document.getElementById("compraIndicacion");
    const btnCompra = document.getElementById("compraOpcionCompra");
    const btnDescuento = document.getElementById("compraOpcionDescuento");

    let opcion = null;
    if (btnCompra.classList.contains("selected")) opcion = "compra";
    else if (btnDescuento.classList.contains("selected")) opcion = "descuento";

    // Si estamos en modo "Acumular", hacer la operación de compra
    if (
      btnBuscar.textContent === "Acumular" &&
      idClienteActual &&
      opcion === "compra"
    ) {
      const monto = document.getElementById("cantidadAcumulada").value.trim();
      if (!monto || isNaN(monto) || parseFloat(monto) <= 0) {
        alert("Ingrese un monto válido para acumular.");
        return;
      }
      try {
        const res = await fetch(
          "/CRM_INT/CRM/controller/CompraController.php",
          {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=create&total=${encodeURIComponent(
              monto
            )}&idCliente=${encodeURIComponent(idClienteActual)}`,
          }
        );
        const json = await res.json();
        alert(json.message);
        if (json.success) {
          document.getElementById("cantidadAcumulada").value = "";
          cargarHistorialCompras(idClienteActual);
        }
      } catch {
        alert("Error al acumular la compra.");
      }
      return;
    }

    // ---- FLUJO NORMAL: BÚSQUEDA Y MUESTRA DE CLIENTE ----
    errorDiv.style.display = "none";
    btnBuscar.style.background = "var(--amarillo)";
    btnBuscar.style.color = "var(--negro)";
    btnBuscar.textContent = "Buscar";
    btnBuscarIcon.style.display = "none";

    // Validar opción seleccionada
    if (!opcion) {
      alert("Seleccione una opción.");
      cardForm.style.display = "none";
      indicacion.style.display = "block";
      idClienteActual = null;
      nombreClienteActual = null;
      document.getElementById("historialCompras").innerHTML = "";
      return;
    }

    // Validar inputId
    if (!inputId) {
      alert("Ingrese el número de tarjeta.");
      cardForm.style.display = "none";
      indicacion.style.display = "block";
      idClienteActual = null;
      nombreClienteActual = null;
      document.getElementById("historialCompras").innerHTML = "";
      return;
    }

    // Realizar la búsqueda
    await buscarCliente(inputId);

    // Si seleccionó "Compra" y se encontró el cliente, cambiar a modo Acumular
    if (opcion === "compra" && idClienteActual) {
      btnBuscar.style.background = "#39cc6b"; // Verde
      btnBuscar.style.color = "white";
      btnBuscar.textContent = "Acumular";
      btnBuscarIcon.style.display = "block"; // Mostrar el ícono de búsqueda
    }
  }

  // Función para buscar cliente
  async function buscarCliente(inputId) {
    const errorDiv = document.getElementById("compraMensaje");
    const cardForm = document.getElementById("compraCardForm");
    const indicacion = document.getElementById("compraIndicacion");

    try {
      const res = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=read&id=${encodeURIComponent(inputId)}`,
      });
      const json = await res.json();

      if (json.success && json.data) {
        const c = json.data;
        document.getElementById("id").value = c.id || "";
        document.getElementById("clienteCedula").value = c.cedula || "";
        document.getElementById("clienteNombre").value = c.nombre || "";
        document.getElementById("clienteCorreo").value = c.correo || "";
        document.getElementById("clienteTelefono").value = c.telefono || "";
        document.getElementById("clienteLugar").value = c.lugarResidencia || "";
        document.getElementById("clienteFecha").value = c.fechaCumpleanos || "";
        document.getElementById("cantidadAcumulada").value = "";
        document.getElementById("totalActual").value = "";

        cardForm.style.display = "block";
        indicacion.style.display = "none";
        errorDiv.style.display = "none";
        idClienteActual = c.id;
        nombreClienteActual = c.nombre; // Guarda el nombre para la tabla

        cargarHistorialCompras(idClienteActual);

        // Si estamos en modo compra, activar el botón acumular y mostrar ícono
        const btnCompra = document.getElementById("compraOpcionCompra");
        if (btnCompra.classList.contains("selected")) {
          btnBuscar.style.background = "#39cc6b"; // Verde
          btnBuscar.style.color = "white";
          btnBuscar.textContent = "Acumular";
          btnBuscarIcon.style.display = "block"; // Mostrar el ícono de búsqueda
        }
      } else {
        errorDiv.textContent =
          "No se encontró un cliente con ese número de tarjeta.";
        errorDiv.style.display = "block";
        cardForm.style.display = "none";
        indicacion.style.display = "block";
        idClienteActual = null;
        nombreClienteActual = null;
        document.getElementById("historialCompras").innerHTML = "";
        btnBuscarIcon.style.display = "none";
      }
    } catch (e) {
      errorDiv.textContent = "Error de conexión con el servidor.";
      errorDiv.style.display = "block";
      cardForm.style.display = "none";
      indicacion.style.display = "block";
      idClienteActual = null;
      nombreClienteActual = null;
      document.getElementById("historialCompras").innerHTML = "";
      btnBuscarIcon.style.display = "none";
    }
  }

  // ----------- HISTORIAL DE COMPRAS Y ELIMINACIÓN --------------

  // Muestra el historial de compras para un cliente
  // Función corregida para cargar historial de compras
  window.cargarHistorialCompras = async function (idCliente) {
    const contenedor = document.getElementById("historialCompras");
    contenedor.innerHTML =
      '<div style="color:var(--gris)">Cargando historial...</div>';

    try {
      const res = await fetch("/CRM_INT/CRM/controller/CompraController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=readByCliente&idCliente=${encodeURIComponent(idCliente)}`,
      });
      const json = await res.json();

      if (!json.success || !json.data || json.data.length === 0) {
        contenedor.innerHTML = `<div class="alert alert-info mt-4">No hay compras registradas para este cliente.</div>`;
        actualizarTotalActual(0);
        return;
      }

      // Primero calculamos el total (con el array original)
      let total = 0;
      json.data.forEach((compra) => {
        total += parseFloat(compra.total);
      });

      // Luego creamos las filas con el array invertido
      // Crea una copia invertida del array para mostrar primero el más reciente
      const comprasInvertidas = json.data.slice().reverse();
      const totalFilas = comprasInvertidas.length;

      const filas = comprasInvertidas
        .map((compra, i) => {
          return `
      <tr>
        <td>${totalFilas - i}</td>
        <td>${compra.fechaCompra}</td>
        <td>₡${parseFloat(compra.total).toLocaleString("es-CR")}</td>
        <td>${nombreClienteActual || ""}</td>
        <td>
          <button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarCompra(${
            compra.idCompra
          })">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
        })
        .join("");

      contenedor.innerHTML = `
      <div class="tabla-scroll">
          <table class="table table-striped table-hover">
              <thead class="table-dark">
                  <tr>
                      <th>#</th>
                      <th>Fecha</th>
                      <th>Total</th>
                      <th>Nombre Cliente</th>
                      <th>Acciones</th>
                  </tr>
              </thead>
              <tbody>${filas}</tbody>
          </table>
      </div>
    `;

      actualizarTotalActual(total);
    } catch (e) {
      contenedor.innerHTML = `<div style="color:#d43b3b;">Error al cargar historial</div>`;
    }
  };

  // Elimina una compra (registro) individual
  window.eliminarCompra = async function (idCompra) {
    if (!confirm("¿Está seguro de eliminar esta compra?")) return;
    try {
      const res = await fetch("/CRM_INT/CRM/controller/CompraController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=delete&idCompra=${encodeURIComponent(idCompra)}`,
      });
      const json = await res.json();
      alert(json.message);
      if (json.success && idClienteActual) {
        cargarHistorialCompras(idClienteActual);
      }
    } catch {
      alert("Error al eliminar la compra.");
    }
  };

  // Actualiza el campo Total Actual
  function actualizarTotalActual(valor) {
    const campoTotal = document.getElementById("totalActual");
    if (campoTotal) campoTotal.value = valor ? valor : 0;
  }
});
