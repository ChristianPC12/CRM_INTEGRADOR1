document.addEventListener("DOMContentLoaded", () => {
  mostrarSemanaActual(); // ⬅️ NUEVO: Mostrar el rango apenas cargue
  cargarCumples();
  cargarHistorial();
  document.getElementById("btnEnviarCorreo").disabled = true;

  // Función para actualizar el badge de cumpleaños pendientes en el sidebar
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

  document
    .getElementById("formCorreo")
    .addEventListener("submit", async (e) => {
      e.preventDefault();

      const id = document.getElementById("idCumple").value;
      const nombre = document.getElementById("nombreCorreo").value;
      const correo = document.getElementById("correoCorreo").value;

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
      // NUEVO: enviar cedula, telefono y cumpleaños
      // NUEVO: enviar solo el ID del cliente
      const idCliente = document.getElementById("idCumple").value;
      formData.append("idCliente", idCliente);

      try {
        const res = await fetch(
          "/CRM_INT/CRM/controller/CumpleController.php",
          {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString(),
          }
        );

        const data = await res.json();

        if (data.success) {
          await cambiarEstado(id, "LISTA");
          Swal.fire("¡Éxito!", data.message, "success");
          document.getElementById("formCorreo").reset();
          document.getElementById("btnEnviarCorreo").disabled = true;
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
      }
    });
});

const mostrarSemanaActual = () => {
  const hoy = new Date();
  const diaActual = hoy.getDay(); // 0 (Domingo) a 6 (Sábado)
  const diffInicio = hoy.getDate() - diaActual + (diaActual === 0 ? -6 : 1);
  const lunes = new Date(hoy.setDate(diffInicio));
  const domingo = new Date(lunes);
  domingo.setDate(lunes.getDate() + 6);

  const opciones = { day: "2-digit", month: "long" };

  const formatoLunes = lunes.toLocaleDateString("es-CR", opciones);
  const formatoDomingo = domingo.toLocaleDateString("es-CR", opciones);

  const div = document.getElementById("rangoSemana");
  if (div) {
    div.innerHTML = `📆 Semana actual: <strong>${formatoLunes}</strong> al <strong>${formatoDomingo}</strong>`;
  }
};

const cargarCumples = async () => {
  const contenedor = document.getElementById("cumpleLista");
  contenedor.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="mt-2">Cargando cumpleaños...</p>
        </div>
    `;

  try {
    const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=readSemana",
    });

    const data = await res.json();

    if (data.success) {
      renderizarTabla(data.data);
    } else {
      contenedor.innerHTML = `<div class="alert alert-danger text-center">${data.message}</div>`;
    }
  } catch (error) {
    console.error("Error cargando cumpleaños:", error);
    contenedor.innerHTML = `<div class="alert alert-danger text-center">Error al cargar los cumpleaños.</div>`;
  }
};

const renderizarTabla = (cumples) => {
  const contenedor = document.getElementById("cumpleLista");

  const pendientes = cumples.filter((c) => c.estado === "Activo");
  pendientes.sort((a, b) => {
    const fechaA = new Date(a.fechaCumpleanos);
    const fechaB = new Date(b.fechaCumpleanos);
    return fechaB - fechaA;
  });

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
    if (c.estado === "LISTA") {
      badge = `<span class="badge bg-success">LISTO</span>`;
    }

    const botonCorreo = `
            <button class="btn btn-sm btn-primary"
            onclick="seleccionarCumple('${c.id}', '${c.nombre}', '${c.cedula}', '${c.correo}', '${c.telefono}', '${c.fechaCumpleanos}')"
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
            <button 
                 class="btn btn-sm btn-warning text-black ms-2 btnRegistrarLlamada"
                 data-id="${c.id}" 
                 data-nombre="${c.nombre}"
                 data-telefono="${c.telefono}">
                 Registrar llamada
            </button>

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

  const divRecordatorio = document.getElementById("recordatorioLlamadas");
  const ulRecordatorio = document.getElementById("listaRecordatorios");

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
                // Limpia el recordatorio por si acaso
                document.getElementById("listaRecordatorios").innerHTML = "";
                document
                  .getElementById("recordatorioLlamadas")
                  .classList.add("d-none");
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
    }, 300); // Espera un poco para asegurar que los botones ya existen en el DOM
  } else {
    divRecordatorio.classList.add("d-none");
    ulRecordatorio.innerHTML = "";
  }
};

const seleccionarCumple = (id, nombre, cedula, correo, telefono, fecha) => {
  document.getElementById("idCumple").value = id;
  document.getElementById("nombreCorreo").value = nombre;
  document.getElementById("cedulaCorreo").value = cedula;
  document.getElementById("correoCorreo").value = correo;
  document.getElementById("telefonoCorreo").value = telefono;
  document.getElementById("fechaCumple").value = fecha;

  const btn = document.getElementById("btnEnviarCorreo");
  if (!correo) {
    Swal.fire({
      icon: "warning",
      title: "¡Este cliente no tiene correo!",
      text: "Recordá llamarlo o escribirle un mensaje.",
      confirmButtonText: "Entendido",
    });
    btn.disabled = true;
  } else {
    btn.disabled = false;
    
    // NUEVO: Focus inmediato al botón
    setTimeout(() => {
      // Scroll suave al botón
      btn.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest'
      });
      
      // Focus y efecto visual
      setTimeout(() => {
        btn.focus();
        btn.style.transition = "all 0.3s ease";
        btn.style.transform = "scale(1.02)";
        btn.style.boxShadow = "0 0 20px rgba(249, 196, 31, 0.7)";
        
        // Restaurar estilo después de 1.5 segundos
        setTimeout(() => {
          btn.style.transform = "scale(1)";
          btn.style.boxShadow = "";
        }, 1500);
      }, 600);
    }, 200);
  }
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
                            <th>Fecha de Llamada</th>
                            <th>Vence</th>
                            <th>Vencido</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

      data.forEach((c) => {
        let datoContacto =
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

      document.getElementById("historialCumples").innerHTML = html;
    });
}

document.getElementById("btnImprimir").addEventListener("click", () => {
  const tabla = document.getElementById("tablaHistorial");

  if (!tabla || tabla.rows.length <= 1) {
    Swal.fire(
      "Tabla vacía",
      "No hay datos en el historial para imprimir.",
      "warning"
    );
    return;
  }

  const contenido = document.getElementById("historialCumples").innerHTML;
  const ventana = window.open("", "", "height=800,width=1000");

  ventana.document.write("<html><head><title>Historial de Cumpleaños</title>");
  ventana.document.write(`
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 20px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
      }
      th, td {
        border: 1px solid black;
        padding: 6px;
        text-align: center;
      }
      th {
        background-color: #f9c41f;
        color: black;
      }
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

document.getElementById("btnExportPDF").addEventListener("click", function () {
  const tabla = document.getElementById("tablaHistorial");

  if (!tabla || tabla.rows.length <= 1) {
    Swal.fire(
      "Tabla vacía",
      "No hay datos en el historial para exportar.",
      "warning"
    );
    return;
  }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({
    orientation: "landscape",
    unit: "pt",
    format: "a4",
  });

  // Título
  doc.setFont("helvetica", "bold");
  doc.setFontSize(16);
  doc.text(
    "Historial de Cumpleaños Atendidos",
    doc.internal.pageSize.getWidth() / 2,
    40,
    {
      align: "center",
    }
  );

  // Extraer la tabla directamente
  doc.autoTable({
    html: "#tablaHistorial",
    startY: 60,
    styles: {
      font: "helvetica",
      fontSize: 10,
      cellPadding: 4,
    },
    headStyles: {
      fillColor: [249, 196, 31], // amarillo institucional
      textColor: [0, 0, 0],
      halign: "center",
    },
    bodyStyles: {
      halign: "center",
    },
    didDrawPage: function (data) {
      // Footer con fecha
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

// === MEJORAS PARA EL SISTEMA DE CUMPLEAÑOS ===

// 1. BUSCADOR POR CÉDULA PARA EL HISTORIAL
function agregarBuscadorHistorial() {
  // Buscar el contenedor donde vamos a agregar el input
  const historialContainer = document.getElementById("historialCumples");
  if (!historialContainer) return;

  // Verificar si ya existe el buscador para evitar duplicados
  if (document.getElementById("buscadorCedula")) return;

  // Crear el input de búsqueda
  const buscadorHTML = `
    <div class="mb-3">
      <div class="input-group input-group-sm">
        <input 
          type="text" 
          id="buscadorCedula" 
          class="form-control" 
          placeholder="Buscar por cédula (ej: 504590528)..."
          autocomplete="off"
        >
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          id="limpiarBusqueda"
          title="Limpiar búsqueda">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <small class="text-muted">
        <i class="fas fa-info-circle"></i> 
        Escribí la cédula para filtrar los resultados
      </small>
    </div>
  `;

  // Insertar el buscador antes del contenido de la tabla
  historialContainer.insertAdjacentHTML('afterbegin', buscadorHTML);

  // Event listeners para el buscador
  const inputBuscador = document.getElementById("buscadorCedula");
  const btnLimpiar = document.getElementById("limpiarBusqueda");

  // Función de filtrado
  function filtrarTabla() {
    const termino = inputBuscador.value.toLowerCase().trim();
    const tabla = document.getElementById("tablaHistorial");
    
    if (!tabla) return;

    const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let filasVisibles = 0;

    for (let i = 0; i < filas.length; i++) {
      const celdaCedula = filas[i].getElementsByTagName('td')[0]; // Primera columna (Cédula)
      
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

    // Mostrar mensaje si no hay resultados
    mostrarMensajeResultados(filasVisibles, termino);
  }

  // Función para mostrar mensaje de resultados
  function mostrarMensajeResultados(cantidad, termino) {
    // Remover mensaje anterior si existe
    const mensajeAnterior = document.getElementById("mensajeResultados");
    if (mensajeAnterior) {
      mensajeAnterior.remove();
    }

    // Si hay término de búsqueda, mostrar contador
    if (termino !== "") {
      const mensaje = document.createElement("div");
      mensaje.id = "mensajeResultados";
      mensaje.className = cantidad > 0 ? "alert alert-info alert-sm py-1 px-2 mb-2" : "alert alert-warning alert-sm py-1 px-2 mb-2";
      mensaje.innerHTML = cantidad > 0 
        ? `<small><i class="fas fa-filter"></i> Se encontraron <strong>${cantidad}</strong> resultados para "<strong>${termino}</strong>"</small>`
        : `<small><i class="fas fa-exclamation-triangle"></i> No se encontraron resultados para "<strong>${termino}</strong>"</small>`;
      
      inputBuscador.parentNode.parentNode.insertAdjacentElement('afterend', mensaje);
    }
  }

  // Event listeners
  inputBuscador.addEventListener("input", filtrarTabla);
  inputBuscador.addEventListener("keyup", function(e) {
    if (e.key === "Escape") {
      this.value = "";
      filtrarTabla();
      this.blur();
    }
  });

  btnLimpiar.addEventListener("click", function() {
    inputBuscador.value = "";
    filtrarTabla();
    inputBuscador.focus();
  });
}

// 2. FUNCIÓN PARA HACER SCROLL Y FOCUS AL FORMULARIO
function scrollYFocusFormulario() {
  // Primero hacer scroll al formulario
  const formularioCard = document.querySelector('.card.shadow-sm[style*="border-left: 5px solid #f9c41f"]');
  
  if (formularioCard) {
    // Scroll suave al formulario
    formularioCard.scrollIntoView({
      behavior: 'smooth',
      block: 'center',
      inline: 'nearest'
    });
    
    // Después del scroll, hacer focus al botón
    setTimeout(() => {
      const btnEnviarCorreo = document.getElementById("btnEnviarCorreo");
      if (btnEnviarCorreo) {
        // Añadir efecto visual
        btnEnviarCorreo.style.transition = "all 0.3s ease";
        btnEnviarCorreo.style.transform = "scale(1.05)";
        btnEnviarCorreo.style.boxShadow = "0 0 20px rgba(249, 196, 31, 0.6)";
        
        // Focus al botón
        btnEnviarCorreo.focus();
        
        // Remover efecto después de 2 segundos
        setTimeout(() => {
          btnEnviarCorreo.style.transform = "scale(1)";
          btnEnviarCorreo.style.boxShadow = "0 4px 16px rgba(249, 196, 31, 0.3)";
        }, 2000);
      }
    }, 800); // Esperar a que termine el scroll
  }
}

// === MODIFICACIONES A LAS FUNCIONES EXISTENTES ===

// Modificar la función cargarHistorial para agregar el buscador
const cargarHistorialOriginal = cargarHistorial;
cargarHistorial = function() {
  cargarHistorialOriginal.call(this);
  
  // Agregar el buscador después de que se cargue la tabla
  setTimeout(() => {
    agregarBuscadorHistorial();
  }, 100);
};

// Modificar la función seleccionarCumple para agregar el scroll/focus
const seleccionarCumpleOriginal = seleccionarCumple;
seleccionarCumple = function(id, nombre, cedula, correo, telefono, fecha) {
  // Ejecutar la función original
  seleccionarCumpleOriginal.call(this, id, nombre, cedula, correo, telefono, fecha);
  
  // Agregar el scroll y focus al formulario
  setTimeout(() => {
    scrollYFocusFormulario();
  }, 300); // Pequeña pausa para que se complete la selección
};

