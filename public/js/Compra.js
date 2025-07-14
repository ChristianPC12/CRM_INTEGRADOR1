document.addEventListener("DOMContentLoaded", function () {
  const btns = document.querySelectorAll(".compra-btn-opcion");
  const btnBuscar = document.getElementById("compraBtnBuscar");
  const btnBuscarIcon = document.getElementById("compraBtnBuscarIcon");
  const btnCompra = document.getElementById("compraOpcionCompra");
  const btnDescuento = document.getElementById("compraOpcionDescuento");
  const inputAcumulada = document.getElementById("cantidadAcumulada");

  let idClienteActual = null,
    nombreClienteActual = null,
    datosClienteActual = null;

  // NUEVO: Si viene por URL ?idCliente=65, autocompleta y busca
  const params = new URLSearchParams(window.location.search);
  const idAuto = params.get('idCliente');
  const buscarAuto = params.get('buscar');
  
  console.log('Parámetros URL detectados:', { idAuto, buscarAuto });
  
  if (idAuto) {
    console.log('Configurando búsqueda automática para tarjeta:', idAuto);
    
    // 1. Asegura que "Compra" quede seleccionada visual y funcionalmente
    btns.forEach((b) => b.classList.remove("selected"));
    btnCompra.classList.add("selected");
    // Ejecuta también la lógica asociada en el listener
    btnCompra.dispatchEvent(new Event('click'));
    
    // 2. Rellena el input
    document.getElementById("compraInputId").value = idAuto;
    
    // 3. Si viene con buscar=auto (desde código de barras), ejecuta búsqueda automáticamente
    if (buscarAuto === 'auto') {
      console.log('Ejecutando búsqueda automática...');
      setTimeout(() => {
        btnBuscar.click();
      }, 300); // Aumentamos el timeout para asegurar que todo esté listo
    }
  }

  // Manejo del ENTER (lector de código de barras o manual)
  document.getElementById("compraInputId").addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      btns.forEach((b) => b.classList.remove("selected"));
      btnCompra.classList.add("selected");
      btnCompra.dispatchEvent(new Event('click'));
      e.preventDefault();
      btnBuscar.click();
    }
  });

  btns.forEach((btn) =>
    btn.addEventListener("click", function () {
      btns.forEach((b) => b.classList.remove("selected"));
      this.classList.add("selected");
      if (this === btnCompra) {
        inputAcumulada.removeAttribute("readonly");
        if (idClienteActual) {
          btnBuscar.textContent = "Acumular";
          btnBuscar.style.background = "#39cc6b";
          btnBuscar.style.color = "white";
          btnBuscarIcon.style.display = "block";
        } else {
          btnBuscar.textContent = "Buscar";
          btnBuscar.style.background = "var(--amarillo)";
          btnBuscar.style.color = "var(--negro)";
          btnBuscarIcon.style.display = "none";
        }
      } else {
        inputAcumulada.setAttribute("readonly", true);
        btnBuscar.textContent = "Aplicar";
        btnBuscar.style.background = "#198754";
        btnBuscar.style.color = "white";
        btnBuscarIcon.style.display = "none";
      }
    })
  );

  btnBuscar.addEventListener("click", async () => await manejarAccion());
  btnBuscarIcon.addEventListener("click", async () => await buscarCliente());

  async function manejarAccion() {
    const inputId = document.getElementById("compraInputId").value.trim();
    const opcion = btnCompra.classList.contains("selected")
      ? "compra"
      : btnDescuento.classList.contains("selected")
      ? "descuento"
      : null;

    if (!opcion || !inputId)
      return alert("Seleccione una opción y digite la tarjeta.");

    if (opcion === "descuento" && btnBuscar.textContent === "Aplicar") {
      let saldo = parseFloat(document.getElementById("totalActual").value) || 0;
      if (saldo < 50000)
        return alert(
          "El saldo actual es insuficiente para aplicar el descuento."
        );
      let saldoFinal = saldo - 50000;
      try {
        const r = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: datosClienteActual
            ? `action=update&id=${datosClienteActual.id}&cedula=${datosClienteActual.cedula}&nombre=${datosClienteActual.nombre}&correo=${datosClienteActual.correo}&telefono=${datosClienteActual.telefono}&lugarResidencia=${datosClienteActual.lugarResidencia}&fechaCumpleanos=${datosClienteActual.fechaCumpleanos}&acumulado=${saldoFinal}`
            : "",
        });
        const json = await r.json();
        if (!json.success)
          return alert(
            "Ocurrió un error al actualizar el saldo en la base de datos."
          );
        document.getElementById("totalActual").value = saldoFinal;
        datosClienteActual.acumulado = saldoFinal;
      } catch {
        return alert("Error de servidor.");
      }
      alert(
        `Descuento exitoso, puedes aplicar el 15% a nombre del cliente VIP: ${nombreClienteActual}.\nEl saldo actual cambió de ₡${saldo.toLocaleString(
          "es-CR"
        )} a ₡${saldoFinal.toLocaleString("es-CR")}.`
      );
      return;
    }

   // === Bloque corregido para registrar una compra ==========================
if (opcion === "compra" && btnBuscar.textContent === "Acumular") {
  const monto =
    parseFloat(document.getElementById("cantidadAcumulada").value.trim()) || 0;
  if (monto <= 0) return alert("Ingrese un monto válido para acumular.");

  /* 1) Registrar la compra (SP SumarCompra hace INSERT + UPDATE) */
  const rCompra = await fetch(
    "/CRM_INT/CRM/controller/CompraController.php",
    {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=create&total=${monto}&idCliente=${idClienteActual}`,
    }
  );
  const resCompra = await rCompra.json();
  if (resCompra.success) {
  // Limpia el input de monto
  document.getElementById("cantidadAcumulada").value = "";

  // Refresca los datos del cliente (ya traen Acumulado y TotalHistorico actualizados)
  await buscarCliente();

  cargarHistorialCompras(idClienteActual);
  return alert(resCompra.message);
}}
// ========================================================================


    await buscarCliente();
    if (opcion === "compra" && idClienteActual) {
      btnBuscar.textContent = "Acumular";
      btnBuscar.style.background = "#39cc6b";
      btnBuscar.style.color = "white";
      btnBuscarIcon.style.display = "block";
    }
  }

  async function buscarCliente() {
    const inputId = document.getElementById("compraInputId").value.trim();
    try {
      const r = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=read&id=${encodeURIComponent(inputId)}`,
      });
      const { success, data } = await r.json();
      if (!success || !data)
        return alert("No se encontró un cliente con ese número de tarjeta.");
      datosClienteActual = { ...data, acumulado: data.acumulado || 0 };
      idClienteActual = data.id;
      nombreClienteActual = data.nombre;
      document.getElementById("id").value = data.id || "";
      document.getElementById("clienteCedula").value = data.cedula || "";
      document.getElementById("clienteNombre").value = data.nombre || "";
      document.getElementById("clienteCorreo").value = data.correo || "";
      document.getElementById("clienteTelefono").value = data.telefono || "";
      document.getElementById("clienteLugar").value =
        data.lugarResidencia || "";
      document.getElementById("clienteFecha").value =
        data.fechaCumpleanos || "";
      document.getElementById("cantidadAcumulada").value = "";
      document.getElementById("totalActual").value =
        datosClienteActual.acumulado;
      document.getElementById("compraCardForm").style.display = "block";
      document.getElementById("compraIndicacion").style.display = "none";
      cargarHistorialCompras(idClienteActual);
    } catch {
      alert("Error de conexión con el servidor.");
    }
  }

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
      if (!json.success || !json.data || !json.data.length) {
        contenedor.innerHTML = `<div class="alert alert-info mt-4">No hay compras registradas para este cliente.</div>`;
        return;
      }
      const filas = json.data
        .slice()
        .reverse()
        .map(
          (compra, i, arr) => `
        <tr>
          <td>${arr.length - i}</td>
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
      `
        )
        .join("");
      contenedor.innerHTML = `
        <div class="tabla-scroll">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr><th>#</th><th>Fecha</th><th>Total</th><th>Nombre Cliente</th><th>Acciones</th></tr>
            </thead>
            <tbody>${filas}</tbody>
          </table>
        </div>
      `;
    } catch {
      contenedor.innerHTML = `<div style="color:#d43b3b;">Error al cargar historial</div>`;
    }
  };

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
      if (json.success && idClienteActual)
        cargarHistorialCompras(idClienteActual);
    } catch {
      alert("Error al eliminar la compra.");
    }
  };
});
