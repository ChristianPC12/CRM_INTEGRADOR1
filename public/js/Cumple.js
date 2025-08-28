// ================== HELPERS (para reducir código repetido) ==================
const $ = (id) => document.getElementById(id);
const on = (el, ev, fn) => el && el.addEventListener(ev, fn);

// Sincroniza el estado del botón "Enviar ambos": se activa solo si ambos están activos
// Versión mejorada de syncEnviarAmbos
function syncEnviarAmbos() {
  const btnCorreo = $("btnEnviarCorreo");
  const btnWhats = $("btnEnviarWhats");
  const btnAmbos = $("btnEnviarAmbos");
  
  if (!btnAmbos || !btnCorreo || !btnWhats) return;

  // Se activa "Enviar ambos" solo cuando AMBOS botones están habilitados
  const correoHabilitado = !btnCorreo.disabled;
  const whatsHabilitado = !btnWhats.disabled;
  
  btnAmbos.disabled = !(correoHabilitado && whatsHabilitado);
  
  // Debug opcional (puedes comentar estas líneas)
  console.log('Sincronizando botones:', {
    correoHabilitado,
    whatsHabilitado, 
    ambosHabilitado: !btnAmbos.disabled
  });
}

// Hace submit seguro del formulario (para no duplicar lógica)
function submitFormCorreo() {
  const form = $("formCorreo");
  if (!form) return;
  if (typeof form.requestSubmit === "function") form.requestSubmit();
  else
    form.dispatchEvent(
      new Event("submit", { cancelable: true, bubbles: true })
    );
}

// ================== TU LÓGICA ORIGINAL (con mínimos ajustes) ==================
document.addEventListener("DOMContentLoaded", () => {
  mostrarSemanaActual();
  cargarCumples();
  cargarHistorial();

  // --- Navegación de semana (flechas del header)
  const btnPrev = $("btnPrevSemana");
  const btnNext = $("btnNextSemana");

  if (btnPrev && btnNext) {
    on(btnPrev, "click", () => {
      semanaOffset = 0;
      mostrarSemanaActual();
      cargarCumples();
      actualizarNavSemanaUI();
    });
    on(btnNext, "click", () => {
      semanaOffset = 1;
      mostrarSemanaActual();
      cargarCumples();
      actualizarNavSemanaUI();
    });
    actualizarNavSemanaUI();
  }

  // Estado inicial de botones
  const btnCorreo = $("btnEnviarCorreo");
  const btnWhats = $("btnEnviarWhats");
  const btnAmbos = $("btnEnviarAmbos");

  if (btnCorreo) btnCorreo.disabled = true;
  if (btnWhats) btnWhats.disabled = true;
  if (btnAmbos) btnAmbos.disabled = true;

  // Habilitar/deshabilitar "Enviar ambos" si cambia el teléfono manualmente
  on($("telefonoCorreo"), "input", () => {
    const telefono = $("telefonoCorreo").value?.trim();
    if (btnWhats) btnWhats.disabled = !telefono;
    syncEnviarAmbos();
  });

  // ====== Enviar Ambos: hace click al WhatsApp y luego envía el formulario de correo ======
  if (btnAmbos) {
    on(btnAmbos, "click", async () => {
      const btnCorreo = $("btnEnviarCorreo");
      const btnWhats = $("btnEnviarWhats");

      if (!btnCorreo || !btnWhats) return;
      if (btnAmbos.disabled) return;

      // 1) Disparar WhatsApp (usa tu handler existente)
      btnWhats.click();

      // 2) Pequeño delay para no pelear con el "estado LISTA" (evita condiciones de carrera)
      setTimeout(() => {
        // Solo si sigue habilitado (tiene correo)
        if (!btnCorreo.disabled) submitFormCorreo();
      }, 250);
    });
  }

  // ========== Sidebar badge (sin cambios funcionales) ==========
  function actualizarCumpleBadgeSidebar() {
    fetch("/CRM_INT/CRM/controller/CumpleController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=hayPendientes",
    })
      .then((res) => res.json())
      .then((data) => {
        if (window.mostrarCumpleBadge) {
          window.mostrarCumpleBadge(data.success && data.hayPendientes);
        }
      });
  }

  // ====== Submit de correo (igual que el tuyo, con llamada a syncEnviarAmbos()) ======
  on($("formCorreo"), "submit", async (e) => {
    e.preventDefault();

    const id = $("idCumple").value;
    const nombre = $("nombreCorreo").value;
    const correo = $("correoCorreo").value;

    if (!correo || correo.trim() === "") {
      Swal.fire({
        icon: "info",
        title: "¡No tiene correo!",
        text: `${nombre} no tiene correo registrado. Contactalo por teléfono para felicitarlo.`,
      });
      return;
    }

    if (!id || !nombre || !correo) return;

    const mensaje = `¡Feliz cumpleaños ${nombre}! 🎉 En nuestro restaurante queremos celebrarte con una comida, bebida o postre totalmente gratis para vos. Te esperamos hoy mismo para consentirte como te merecés.`;

    const formData = new URLSearchParams();
    formData.append("action", "enviarCorreoCumple");
    formData.append("nombre", nombre);
    formData.append("correo", correo);
    formData.append("mensaje", mensaje);
    formData.append("idCliente", id);

    try {
      const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData.toString(),
      });
      const data = await res.json();

      if (data.success) {
        await cambiarEstado(id, "LISTA");
        Swal.fire("¡Éxito!", data.message, "success");
        $("formCorreo").reset();
        if ($("btnEnviarCorreo")) $("btnEnviarCorreo").disabled = true;
        if ($("btnEnviarWhats")) $("btnEnviarWhats").disabled = true; // después de atenderlo, deshabilitamos ambos
        if ($("btnEnviarAmbos")) $("btnEnviarAmbos").disabled = true;

        cargarCumples();
        cargarHistorial();
        if (window.actualizarCumpleBadgeSidebar)
          window.actualizarCumpleBadgeSidebar();
      } else {
        Swal.fire("Error", data.message, "error");
      }
    } catch (err) {
      console.error("Error al enviar correo:", err);
      Swal.fire("Error", "No se pudo conectar con el servidor", "error");
    } finally {
      syncEnviarAmbos();
    }
  });

  // ====== Handler Enviar WhatsApp (igual que el tuyo, con syncEnviarAmbos()) ======
  if (btnWhats) {
    on(btnWhats, "click", async () => {
      const id = $("idCumple").value;
      const nombre = $("nombreCorreo").value;
      let telefono = $("telefonoCorreo").value;

      if (!telefono || telefono.trim() === "") {
        Swal.fire({
          icon: "info",
          title: "Sin teléfono",
          text: `${nombre} no tiene teléfono registrado.`,
        });
        return;
      }
      if (!id || !nombre || !telefono) return;

      const soloDigitos = (telefono || "").replace(/\D+/g, "");
      if (soloDigitos.length === 8) {
        telefono = "506" + soloDigitos;
      } else if (soloDigitos.length > 8) {
        telefono = soloDigitos;
      } else {
        telefono = soloDigitos;
      }

      const mensaje = `¡Hola ${nombre}! En Bastos sabemos que estás de cumpleaños. Visítanos para celebrarlo juntos y reclamar tu regalía 🎉🎁`;

      const formData = new URLSearchParams();
      formData.append("action", "enviarWhatsCumple");
      formData.append("nombre", nombre);
      formData.append("telefono", telefono);
      formData.append("mensaje", mensaje);
      formData.append("idCliente", id);

      try {
        btnWhats.disabled = true;
        btnWhats.innerText = "Enviando...";
        const res = await fetch(
          "/CRM_INT/CRM/controller/CumpleController.php",
          {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString(),
          }
        );

        const text = await res.text();
        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error("Respuesta no JSON:", text);
          throw new Error("Respuesta no válida del servidor");
        }

        if (data.success) {
          await cambiarEstado(id, "LISTA");
          Swal.fire("¡Éxito!", data.message, "success");
          $("formCorreo").reset();
          if ($("btnEnviarCorreo")) $("btnEnviarCorreo").disabled = true;
          if ($("btnEnviarWhats")) $("btnEnviarWhats").disabled = true;
          if ($("btnEnviarAmbos")) $("btnEnviarAmbos").disabled = true;

          cargarCumples();
          cargarHistorial();
          if (window.actualizarCumpleBadgeSidebar)
            window.actualizarCumpleBadgeSidebar();
        } else {
          Swal.fire("Error", data.message, "error");
        }
      } catch (err) {
        console.error("Error al enviar WhatsApp:", err);
        Swal.fire(
          "Error",
          err && err.message
            ? err.message
            : "No se pudo conectar con el servidor",
          "error"
        );
      } finally {
        btnWhats.innerHTML = '<i class="bi bi-whatsapp"></i> Enviar WhatsApp';
        btnWhats.disabled = false;
        syncEnviarAmbos();
      }
    });
  }

  // Sincroniza al cargar la vista
  syncEnviarAmbos();
});

let semanaOffset = 0; // 0 = actual, 1 = siguiente

const mostrarSemanaActual = () => {
  const hoy = new Date();
  const diaActual = hoy.getDay(); // 0 (Dom) a 6 (Sáb)
  const diffInicio = diaActual === 0 ? -6 : 1 - diaActual;
  const lunes = new Date(
    hoy.getFullYear(),
    hoy.getMonth(),
    hoy.getDate() + diffInicio + semanaOffset * 7
  );
  const domingo = new Date(
    lunes.getFullYear(),
    lunes.getMonth(),
    lunes.getDate() + 6
  );

  const opciones = { day: "2-digit", month: "long" };
  const formatoLunes = lunes.toLocaleDateString("es-CR", opciones);
  const formatoDomingo = domingo.toLocaleDateString("es-CR", opciones);

  const div = $("rangoSemana");
  if (div) {
    const etiqueta = semanaOffset === 0 ? "Semana actual" : "Semana siguiente";
    div.innerHTML = `📆 ${etiqueta}: <strong>${formatoLunes}</strong> al <strong>${formatoDomingo}</strong>`;
  }

  const lbl = $("lblSemana");
  if (lbl)
    lbl.textContent = semanaOffset === 0 ? "SEMANA ACTUAL" : "SEMANA SIGUIENTE";
};

function actualizarNavSemanaUI() {
  const btnPrev = $("btnPrevSemana");
  const btnNext = $("btnNextSemana");
  const lblSemana = $("lblSemana");

  if (btnPrev) btnPrev.disabled = semanaOffset === 0;
  if (btnNext) btnNext.disabled = semanaOffset === 1;
  if (lblSemana)
    lblSemana.textContent =
      semanaOffset === 0 ? "SEMANA ACTUAL" : "SEMANA SIGUIENTE";
}

const cargarCumples = async () => {
  const contenedor = $("cumpleLista");
  contenedor.innerHTML = `
    <div class="text-center p-3">
      <div class="spinner-border text-warning" role="status"></div>
      <p class="mt-2">Cargando cumpleaños...</p>
    </div>
  `;

  try {
    const body = `action=readSemana&offset=${encodeURIComponent(semanaOffset)}`;
    const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body,
    });

    const data = await res.json();

    if (data.success) {
      renderizarTabla(data.data);
      mostrarSemanaActual();
      if (typeof actualizarNavSemanaUI === "function") actualizarNavSemanaUI();
    } else {
      contenedor.innerHTML = `<div class="alert alert-danger text-center">${
        data.message || "No se pudo cargar."
      }</div>`;
    }
  } catch (error) {
    console.error("Error cargando cumpleaños:", error);
    contenedor.innerHTML = `<div class="alert alert-danger text-center">Error al cargar los cumpleaños.</div>`;
  }
};

const renderizarTabla = (cumples) => {
  const contenedor = $("cumpleLista");
  const pendientes = cumples.filter((c) => c.estado === "Activo");
  pendientes.sort(
    (a, b) => new Date(b.fechaCumpleanos) - new Date(a.fechaCumpleanos)
  );

  if (pendientes.length === 0) {
    contenedor.innerHTML = `<div class="alert alert-info text-center">No hay cumpleaños pendientes esta semana.</div>`;
    return;
  }

  let html = `
    <div class="table-responsive">
      <table class="table table-bordered table-hover tabla-ajustada">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Fecha de Cumpleaños</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
  `;

  let recordatorios = "";
  let tieneSinCorreo = false;

  pendientes.forEach((c) => {
    let badge = `<span class="badge bg-success">Activo</span>`;
    if (c.estado === "LISTA")
      badge = `<span class="badge bg-success">LISTO</span>`;

    const botonCorreo = `
      <button class="btn btn-sm btn-primary"
        onclick="seleccionarCumple('${c.id}', '${c.nombre}', '${c.cedula}', '${c.correo}', '${c.telefono}', '${c.fechaCumpleanos}')">
        <i class="fas fa-paper-plane"></i>
      </button>
    `;

    html += `
      <tr id="fila-${c.id}">
        <td>${c.nombre}</td>
        <td>${c.cedula}</td>
        <td class="correo">${c.correo}</td>
        <td>${c.telefono}</td>
        <td>${formatearFecha(c.fechaCumpleanos)}</td>
        <td>${badge}</td>
        <td class="text-center">${botonCorreo}</td>
      </tr>
    `;

    if (!c.correo || c.correo.trim() === "") {
      tieneSinCorreo = true;
      recordatorios += `
        <li>
          <i class="fa-solid fa-phone text-warning me-1"></i>
          <strong>${c.cedula}</strong> - ${c.nombre} → 
          <span class="text-primary fw-bold">Llamar al ${c.telefono}</span>
        </li>
      `;
    }
  });

  html += `
        </tbody>
      </table>
    </div>
  `;

  contenedor.innerHTML = html;

  const divRecordatorio = $("recordatorioLlamadas");
  const ulRecordatorio = $("listaRecordatorios");

  if (tieneSinCorreo) {
    ulRecordatorio.innerHTML = recordatorios;
    divRecordatorio.classList.remove("d-none");
    setTimeout(() => {
      document.querySelectorAll(".btnRegistrarLlamada").forEach((btn) => {
        btn.addEventListener("click", async function () {
          const idCliente = this.dataset.id;
          const nombre = this.dataset.nombre;
          const telefono = this.dataset.telefono;

          const confirm = await Swal.fire({
            title: "¿Registrar llamada?",
            text: `¿Confirmás que llamaste a ${nombre} al número ${telefono}?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, registrar",
            cancelButtonText: "Cancelar",
          });

          if (confirm.isConfirmed) {
            const formData = new URLSearchParams();
            formData.append("action", "registrarLlamadaCumple");
            formData.append("idCliente", idCliente);

            try {
              const res = await fetch(
                "/CRM_INT/CRM/controller/CumpleController.php",
                {
                  method: "POST",
                  headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                  },
                  body: formData.toString(),
                }
              );
              const data = await res.json();

              if (data.success) {
                Swal.fire("¡Llamada registrada!", data.message, "success");
                cargarCumples();
                cargarHistorial();
                if (window.actualizarCumpleBadgeSidebar)
                  window.actualizarCumpleBadgeSidebar();
                $("listaRecordatorios").innerHTML = "";
                $("recordatorioLlamadas").classList.add("d-none");
              } else {
                Swal.fire("Error", data.message, "error");
              }
            } catch (err) {
              console.error("Error al registrar llamada:", err);
              Swal.fire(
                "Error",
                "No se pudo conectar con el servidor",
                "error"
              );
            }
          }
        });
      });
    }, 300);
  } else {
    divRecordatorio.classList.add("d-none");
    ulRecordatorio.innerHTML = "";
  }
};

const seleccionarCumple = (id, nombre, cedula, correo, telefono, fecha) => {
  $("idCumple").value = id;
  $("nombreCorreo").value = nombre;
  $("cedulaCorreo").value = cedula;
  $("correoCorreo").value = correo;
  $("telefonoCorreo").value = telefono;
  $("fechaCumple").value = fecha;

  const btn = $("btnEnviarCorreo");
  const btnWhats2 = $("btnEnviarWhats");

  if (!correo) {
    Swal.fire({
      icon: "warning",
      title: "¡Este cliente no tiene correo!",
      text: "Recordá llamarlo o escribirle un mensaje.",
      confirmButtonText: "Entendido",
    });
    if (btn) btn.disabled = true;
    if (btnWhats2) btnWhats2.disabled = !telefono;
  } else {
    if (btn) btn.disabled = false;
    if (btnWhats2) btnWhats2.disabled = !telefono;

    setTimeout(() => {
      btn.scrollIntoView({
        behavior: "smooth",
        block: "center",
        inline: "nearest",
      });
      setTimeout(() => {
        btn.focus();
        btn.style.transition = "all 0.3s ease";
        btn.style.transform = "scale(1.02)";
        btn.style.boxShadow = "0 0 20px rgba(249, 196, 31, 0.7)";
        setTimeout(() => {
          btn.style.transform = "scale(1)";
          btn.style.boxShadow = "";
        }, 1500);
      }, 600);
    }, 200);
  }

  // ➜ Sincroniza el botón "Enviar ambos" cada vez que seleccionás un cumple
  syncEnviarAmbos();
};

const cambiarEstado = async (id, nuevoEstado) => {
  const formData = new URLSearchParams();
  formData.append("action", "cambiarEstado");
  formData.append("id", id);
  formData.append("estado", nuevoEstado);

  try {
    const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formData.toString(),
    });

    const data = await res.json();
    return data.success;
  } catch (error) {
    console.error("Error en cambiarEstado:", error);
    return false;
  }
};

const formatearFecha = (fechaStr) => {
  if (!fechaStr) return "Fecha no disponible";
  const partes = fechaStr.split("-");
  if (partes.length !== 3) return "Fecha inválida";
  const [anio, mes, dia] = partes;
  const fecha = new Date(anio, mes - 1, dia);
  if (isNaN(fecha)) return "Fecha inválida";
  return fecha
    .toLocaleDateString("es-CR", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    })
    .toUpperCase();
};

function cargarHistorial() {
  fetch("/CRM_INT/CRM/controller/CumpleController.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "action=readHistorial",
  })
    .then((res) => res.json())
    .then((data) => {
      let html = `
        <div class="table-responsive">
          <table class="table table-striped table-bordered" id="tablaHistorial">
            <thead class="table-dark">
              <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>CORREO / TELÉFONO</th>
                <th>Fecha de Cumpleaños</th>
                <th>Fecha de Llamada/Correo</th>
                <th>Vence</th>
                <th>Vencido</th>
              </tr>
            </thead>
            <tbody>
      `;

      data.forEach((c) => {
        const datoContacto =
          c.correo && c.correo.trim() !== "" ? c.correo : c.telefono;
        html += `
          <tr>
            <td>${c.cedula}</td>
            <td>${c.nombre}</td>
            <td class="contacto">${datoContacto}</td>
            <td>${c.fechaCumpleanos}</td>
            <td>${c.fechaLlamada}</td>
            <td>${c.vence}</td>
            <td class="vencido-cell">${c.vencido === "SI" ? "SÍ" : "NO"}</td>
          </tr>
        `;
      });

      html += `
            </tbody>
          </table>
        </div>
      `;

      $("historialCumples").innerHTML = html;
    });
}

on($("btnImprimir"), "click", () => {
  const tabla = $("tablaHistorial");
  if (!tabla || tabla.rows.length <= 1) {
    Swal.fire(
      "Tabla vacía",
      "No hay datos en el historial para imprimir.",
      "warning"
    );
    return;
  }

  const contenido = $("historialCumples").innerHTML;
  const ventana = window.open("", "", "height=800,width=1000");

  ventana.document.write("<html><head><title>Historial de Cumpleaños</title>");
  ventana.document.write(`
    <style>
      body { font-family: Arial, sans-serif; margin: 20px; }
      table { width: 100%; border-collapse: collapse; font-size: 13px; }
      th, td { border: 1px solid black; padding: 6px; text-align: center; }
      th { background-color: #f9c41f; color: black; }
    </style>
  `);
  ventana.document.write("</head><body>");
  ventana.document.write(
    "<h3 style='text-align:center;'>Historial de Cumpleaños Atendidos</h3>"
  );
  ventana.document.write(contenido);
  ventana.document.write("</body></html>");
  ventana.document.close();

  ventana.focus();
  ventana.print();
  ventana.close();
});

on($("btnExportPDF"), "click", function () {
  const tabla = $("tablaHistorial");
  if (!tabla || tabla.rows.length <= 1) {
    Swal.fire(
      "Tabla vacía",
      "No hay datos en el historial para exportar.",
      "warning"
    );
    return;
  }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });

  doc.setFont("helvetica", "bold");
  doc.setFontSize(16);
  doc.text(
    "Historial de Cumpleaños Atendidos",
    doc.internal.pageSize.getWidth() / 2,
    40,
    { align: "center" }
  );

  doc.autoTable({
    html: "#tablaHistorial",
    startY: 60,
    styles: { font: "helvetica", fontSize: 10, cellPadding: 4 },
    headStyles: {
      fillColor: [249, 196, 31],
      textColor: [0, 0, 0],
      halign: "center",
    },
    bodyStyles: { halign: "center" },
    didDrawPage: function (data) {
      const fecha = new Date().toLocaleString("es-CR");
      doc.setFontSize(8);
      doc.text(
        `CRM Bastos - ${fecha}`,
        data.settings.margin.left,
        doc.internal.pageSize.getHeight() - 10
      );
    },
  });

  doc.save("historial_cumpleanos.pdf");
});

// === MEJORAS PARA EL SISTEMA DE CUMPLEAÑOS (se mantienen) ===
function agregarBuscadorHistorial() {
  const historialContainer = $("historialCumples");
  if (!historialContainer) return;
  if ($("buscadorCedula")) return;

  const buscadorHTML = `
    <div class="mb-3">
      <div class="input-group input-group-sm">
        <input type="text" id="buscadorCedula" class="form-control" placeholder="Buscar por cédula (ej: 504590528)..." autocomplete="off">
        <button type="button" class="btn btn-outline-secondary" id="limpiarBusqueda" title="Limpiar búsqueda">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <small class="text-muted"><i class="fas fa-info-circle"></i> Escribí la cédula para filtrar los resultados</small>
    </div>
  `;

  historialContainer.insertAdjacentHTML("afterbegin", buscadorHTML);

  const inputBuscador = $("buscadorCedula");
  const btnLimpiar = $("limpiarBusqueda");

  function filtrarTabla() {
    const termino = inputBuscador.value.toLowerCase().trim();
    const tabla = $("tablaHistorial");
    if (!tabla) return;

    const filas = tabla
      .getElementsByTagName("tbody")[0]
      .getElementsByTagName("tr");
    let filasVisibles = 0;

    for (let i = 0; i < filas.length; i++) {
      const celdaCedula = filas[i].getElementsByTagName("td")[0];
      if (celdaCedula) {
        const textoCedula = celdaCedula.textContent.toLowerCase();
        if (termino === "" || textoCedula.includes(termino)) {
          filas[i].style.display = "";
          filasVisibles++;
        } else {
          filas[i].style.display = "none";
        }
      }
    }
    mostrarMensajeResultados(filasVisibles, termino);
  }

  function mostrarMensajeResultados(cantidad, termino) {
    const anterior = $("mensajeResultados");
    if (anterior) anterior.remove();

    if (termino !== "") {
      const mensaje = document.createElement("div");
      mensaje.id = "mensajeResultados";
      mensaje.className =
        cantidad > 0
          ? "alert alert-info alert-sm py-1 px-2 mb-2"
          : "alert alert-warning alert-sm py-1 px-2 mb-2";
      mensaje.innerHTML =
        cantidad > 0
          ? `<small><i class="fas fa-filter"></i> Se encontraron <strong>${cantidad}</strong> resultados para "<strong>${termino}</strong>"</small>`
          : `<small><i class="fas fa-exclamation-triangle"></i> No se encontraron resultados para "<strong>${termino}</strong>"</small>`;

      inputBuscador.parentNode.parentNode.insertAdjacentElement(
        "afterend",
        mensaje
      );
    }
  }

  on(inputBuscador, "input", filtrarTabla);
  on(inputBuscador, "keyup", function (e) {
    if (e.key === "Escape") {
      this.value = "";
      filtrarTabla();
      this.blur();
    }
  });

  on(btnLimpiar, "click", function () {
    inputBuscador.value = "";
    filtrarTabla();
    inputBuscador.focus();
  });
}

function scrollYFocusFormulario() {
  const formularioCard = document.querySelector(
    '.card.shadow-sm[style*="border-left: 5px solid #f9c41f"]'
  );
  if (formularioCard) {
    formularioCard.scrollIntoView({
      behavior: "smooth",
      block: "center",
      inline: "nearest",
    });
    setTimeout(() => {
      const btnEnviarCorreo = $("btnEnviarCorreo");
      if (btnEnviarCorreo) {
        btnEnviarCorreo.style.transition = "all 0.3s ease";
        btnEnviarCorreo.style.transform = "scale(1.05)";
        btnEnviarCorreo.style.boxShadow = "0 0 20px rgba(249, 196, 31, 0.6)";
        btnEnviarCorreo.focus();
        setTimeout(() => {
          btnEnviarCorreo.style.transform = "scale(1)";
          btnEnviarCorreo.style.boxShadow =
            "0 4px 16px rgba(249, 196, 31, 0.3)";
        }, 2000);
      }
    }, 800);
  }
}

// Hook: agregar buscador tras cargar historial
const cargarHistorialOriginal = cargarHistorial;
cargarHistorial = function () {
  cargarHistorialOriginal.call(this);
  setTimeout(() => {
    agregarBuscadorHistorial();
  }, 100);
};

const seleccionarCumpleOriginal = seleccionarCumple;
seleccionarCumple = function (id, nombre, cedula, correo, telefono, fecha) {
  seleccionarCumpleOriginal.call(
    this,
    id,
    nombre,
    cedula,
    correo,
    telefono,
    fecha
  );
  setTimeout(() => {
    scrollYFocusFormulario();
    syncEnviarAmbos();
  }, 300);
};