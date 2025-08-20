// Archivo: public/js/Layout.js
// Detectar cuando se cierra la ventana/pestaña y cerrar sesión

// Flag para saber si es navegación interna (dentro del CRM)
window.__navInterna = false;

document.addEventListener("DOMContentLoaded", function () {
  const sendClose = () =>
    navigator.sendBeacon(
      "/CRM_INT/CRM/controller/SessionController.php",
      JSON.stringify({ action: "close_session" })
    );
  window.addEventListener("beforeunload", function () {
    if (window.__navInterna) return;
    sendClose();
    if ("scrollRestoration" in history) history.scrollRestoration = "manual";
  });
  window.addEventListener("unload", function () {
    if (window.__navInterna) return;
    sendClose();
  });

  // Asegura que los modales cuelguen de <body> para evitar problemas con transform/filter en vistas específicas
  (function ensureModalsAtBody() {
    ["modalTarjeta", "modalCumples"].forEach((id) => {
      const el = document.getElementById(id);
      if (el && el.parentElement !== document.body) {
        document.body.appendChild(el);
      }
    });
  })();
});

// Función para confirmar cierre de sesión manual
function confirmarCerrarSesion() {
  if (confirm("¿Cerrar sesión?")) {
    window.location.href = "/CRM_INT/CRM/index.php?logout=1";
  }
}

window.addEventListener("load", function () {
  const toggleBtn = document.getElementById("menuToggle");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const sidebarLinks = document.querySelectorAll(".sidebar ul li a");

  // ⚠️ El scroll real está en el <ul> dentro del sidebar
  const sc = sidebar ? sidebar.querySelector("ul") || sidebar : null;

  if (!toggleBtn || !sidebar) {
    console.warn("⚠️ No se encontró el botón ☰ o el sidebar.");
    return;
  }

  // 🎯 MANTENER POSICIÓN DEL SCROLL EN SIDEBAR
  const savedScrollPosition = localStorage.getItem("sidebarScrollPosition");
  if (savedScrollPosition && sc) {
    sc.scrollTop = parseInt(savedScrollPosition, 10);
  }

  if (sc) {
    sc.addEventListener("scroll", function () {
      localStorage.setItem("sidebarScrollPosition", sc.scrollTop);
    });
  }

  // 💫 CENTRAR ELEMENTO ACTIVO EN VISTA
  function centrarElementoActivo() {
    const elementoActivo = sidebar.querySelector(".active");
    if (!elementoActivo || !sc) return;


    const elRect = elementoActivo.getBoundingClientRect();
    const contRect = sc.getBoundingClientRect();
    const elTopWithin = elRect.top - contRect.top + sc.scrollTop;

    const target =
      elTopWithin - sc.clientHeight / 2 + elementoActivo.clientHeight / 2;

    sc.scrollTo({
      top: Math.max(0, target),
      behavior: "instant" in HTMLElement.prototype ? "instant" : "auto",
    });

    localStorage.setItem("sidebarScrollPosition", sc.scrollTop);
  }

  setTimeout(() => {
    const lastId = localStorage.getItem("lastSidebarFocusId");
    const activo = sidebar.querySelector(".active");

    const objetivo = (lastId && document.getElementById(lastId)) || activo;
    if (!objetivo) return;

    const prev = sc.scrollTop; // sc es el <ul> que scrollea
    try {
      objetivo.focus({ preventScroll: true });
    } catch {
      objetivo.focus();
    }
    if (isGuia) sc.scrollTop = prev; // solo en “guia” forzamos la posición
  }, 180);

  // Forzar sidebar cerrado en móvil al cargar
  if (window.innerWidth <= 992) {
    sidebar.classList.remove("show");
    sidebar.classList.remove("activa");
    if (overlay) overlay.classList.remove("show");
  }

  // Toggle del menú
  toggleBtn.addEventListener("click", function () {
    sidebar.classList.toggle("show");
    sidebar.classList.toggle("activa");
    if (overlay) {
      overlay.classList.toggle("show");
    }
  });

  // Cerrar al hacer click en overlay
  if (overlay) {
    overlay.addEventListener("click", function () {
      sidebar.classList.remove("show");
      sidebar.classList.remove("activa");
      overlay.classList.remove("show");
    });
  }

  sidebarLinks.forEach((link) => {
    link.addEventListener("click", function () {
      window.__navInterna = true;
      if (this.id) localStorage.setItem("lastSidebarFocusId", this.id);
      if (sc) localStorage.setItem("sidebarScrollPosition", sc.scrollTop);

      if (window.innerWidth <= 992) {
        sidebar.classList.remove("show");
        sidebar.classList.remove("activa");
        if (overlay) overlay.classList.remove("show");
      }
    });
  });

  // Cerrar al redimensionar ventana
  window.addEventListener("resize", function () {
    if (window.innerWidth > 992) {
      sidebar.classList.remove("show");
      sidebar.classList.remove("activa");
      if (overlay) overlay.classList.remove("show");
    }
  });
});

// === Modal de TARJETA (buscar por número) ===
function mostrarModal() {
  const modal = document.getElementById("modalTarjeta");
  if (!modal) return;
  document.body.classList.add("modal-open"); // bloquea scroll
  modal.style.display = "flex";
  const input = document.getElementById("modalInputTarjeta");
  if (input) {
    input.value = "";
    const err = document.getElementById("modalMensajeError");
    if (err) err.textContent = "";
    setTimeout(() => input.focus(), 100);
  }
}

function cerrarModal() {
  const modal = document.getElementById("modalTarjeta");
  if (!modal) return;
  modal.style.display = "none";
  const input = document.getElementById("modalInputTarjeta");
  if (input) input.value = "";
  const err = document.getElementById("modalMensajeError");
  if (err) err.textContent = "";

  // Quita modal-open si no queda otro modal abierto
  const cumplesAbierto = document.querySelector(
    "#modalCumples[style*='display: flex']"
  );
  if (!cumplesAbierto) document.body.classList.remove("modal-open");
}

// Mantengo este nombre porque así lo llama el HTML original del modal
function abrirModal() {
  mostrarModal();
}

async function redirigirCompra() {
  const tarjeta = (document.getElementById("modalInputTarjeta")?.value || "")
    .trim();
  const mensajeError = document.getElementById("modalMensajeError");
  if (mensajeError) mensajeError.textContent = "";

  if (!tarjeta) {
    if (mensajeError)
      mensajeError.textContent = "Por favor ingrese un número de tarjeta.";
    return;
  }

  try {
    const response = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=read&id=${encodeURIComponent(tarjeta)}`,
    });
    const json = await response.json();

    if (!json.success || !json.data) {
      if (mensajeError) mensajeError.textContent = "El número de tarjeta no existe.";
      return;
    }

    // Guarda foco correcto antes de redirigir
    localStorage.setItem("lastSidebarFocusId", "link-compras");

    // OK: redirigir a Beneficios con el id del cliente encontrado
    window.location.href = `/CRM_INT/CRM/index.php?view=compras&idCliente=${encodeURIComponent(
      tarjeta
    )}&buscar=auto`;
  } catch (e) {
    if (mensajeError) mensajeError.textContent = "Error de conexión con el servidor.";
  }
}

// Cierre por click fuera y por ESC + Enter para buscar
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("modalTarjeta");
  const inputTarjeta = document.getElementById("modalInputTarjeta");

  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) cerrarModal();
    });

    document.addEventListener("keydown", function (e) {
      if (modal.style.display === "flex") {
        if (e.key === "Escape") cerrarModal();
        if (e.key === "Enter") {
          e.preventDefault();
          redirigirCompra();
        }
      }
    });
  }

  if (inputTarjeta) {
    inputTarjeta.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        redirigirCompra();
      }
    });
  }
});

// === Modal de CUMPLEAÑOS ===
function abrirModalCumples() {
  const modal = document.getElementById("modalCumples");
  if (modal) {
    document.body.classList.add("modal-open"); // bloquea scroll
    modal.style.display = "flex";
    cargarCumpleanosSemana();
  } else {
    console.error("Modal modalCumples no encontrado en el DOM");
  }
}

function cerrarModalCumples() {
  const modal = document.getElementById("modalCumples");
  if (modal) {
    modal.style.display = "none";
    // Quita modal-open si no queda otro modal abierto
    const tarjetaAbierto = document.querySelector(
      "#modalTarjeta[style*='display: flex']"
    );
    if (!tarjetaAbierto) document.body.classList.remove("modal-open");
  }
}

function irACumpleanos() {
  cerrarModalCumples();
  window.location.href = "/CRM_INT/CRM/index.php?view=cumple";
}

async function cargarCumpleanosSemana() {
  const contenedor = document.getElementById("listaCumpleanos");
  const rangoDiv = document.getElementById("rangoCumplesSemana");

  if (!contenedor || !rangoDiv) {
    console.error("No se encontraron los elementos del modal");
    return;
  }

  mostrarRangoSemana(rangoDiv);

  contenedor.innerHTML = `
    <div class="text-center p-3">
      <div class="spinner-border text-warning" role="status"></div>
      <p class="mt-2">Cargando cumpleaños...</p>
    </div>
  `;

  try {
    const response = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=readSemana",
    });

    const data = await response.json();

    if (data.success) {
      renderizarCumpleanos(data.data, contenedor);
    } else {
      contenedor.innerHTML = `<div class="alert alert-danger text-center">${data.message}</div>`;
    }
  } catch (error) {
    console.error("Error cargando cumpleaños:", error);
    contenedor.innerHTML = `<div class="alert alert-danger text-center">Error al cargar los cumpleaños.</div>`;
  }
}

function mostrarRangoSemana(div) {
  const hoy = new Date();
  const diaActual = hoy.getDay();
  const diffInicio = hoy.getDate() - diaActual + (diaActual === 0 ? -6 : 1);
  const lunes = new Date(hoy.setDate(diffInicio));
  const domingo = new Date(lunes);
  domingo.setDate(lunes.getDate() + 6);

  const opciones = { day: "2-digit", month: "long" };
  const formatoLunes = lunes.toLocaleDateString("es-CR", opciones);
  const formatoDomingo = domingo.toLocaleDateString("es-CR", opciones);

  div.innerHTML = `📆 Semana actual: <strong>${formatoLunes}</strong> al <strong>${formatoDomingo}</strong>`;
}

function renderizarCumpleanos(cumples, contenedor) {
  const pendientes = cumples.filter((c) => c.estado === "Activo");

  if (pendientes.length === 0) {
    contenedor.innerHTML = `
      <div class="alerta-sin-cumples">
        <i class="bi bi-calendar-x"></i>
        <h4>¡No hay cumpleaños esta semana!</h4>
        <p>Todos los cumpleañeros han sido atendidos o no hay cumpleaños programados.</p>
      </div>
    `;
    return;
  }

  let html = "";

  pendientes.forEach((cumple) => {
    const tieneCorreo = cumple.correo && cumple.correo.trim() !== "";
    const cardClass = tieneCorreo ? "cumple-card" : "cumple-card sin-correo";

    html += `
      <div class="${cardClass}">
        <div class="cumple-header">
          <h4 class="cumple-nombre">${cumple.nombre}</h4>
          <span class="cumple-fecha">${formatearFechaCumple(
            cumple.fechaCumpleanos
          )}</span>
        </div>
        
        <div class="cumple-info">
          <div class="cumple-detalle">
            <span class="cumple-label">Cédula:</span> ${cumple.cedula}
          </div>
          <div class="cumple-detalle">
            <span class="cumple-label">Teléfono:</span> ${cumple.telefono}
          </div>
          <div class="cumple-detalle">
            <span class="cumple-label">Correo:</span> ${
              tieneCorreo
                ? cumple.correo
                : '<span style="color: #ff6b6b;">Sin correo</span>'
            }
          </div>
        </div>
      </div>
    `;
  });

  contenedor.innerHTML = html;
}

function formatearFechaCumple(fecha) {
  return new Date(fecha).toLocaleDateString("es-CR", {
    day: "2-digit",
    month: "short",
  });
}

// Badge de cumpleaños pendientes (global)
window.actualizarCumpleBadgeSidebar = function () {
  fetch("/CRM_INT/CRM/controller/CumpleController.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "action=readSemana",
  })
    .then((res) => res.json())
    .then((data) => {
      const badge = document.getElementById("cumple-badge");
      if (!badge) return;
      if (data.success && data.data && data.data.length > 0) {
        badge.style.display = "inline-block";
        badge.innerHTML = `<span style="display:inline-block;width:12px;height:12px;background:#f9c41f;border-radius:50%;border:2px solid #000;box-shadow:0 0 2px #000;vertical-align:middle;"></span>`;
      } else {
        badge.style.display = "none";
        badge.innerHTML = "";
      }
    })
    .catch(() => {
      const badge = document.getElementById("cumple-badge");
      if (badge) {
        badge.style.display = "none";
        badge.innerHTML = "";
      }
    });
};

document.addEventListener("DOMContentLoaded", function () {
  window.actualizarCumpleBadgeSidebar();

  // Cerrar modal de Cumples por click fuera y ESC
  const modalCumples = document.getElementById("modalCumples");
  if (modalCumples) {
    modalCumples.addEventListener("click", function (e) {
      if (e.target === modalCumples) {
        cerrarModalCumples();
      }
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modalCumples.style.display === "flex") {
        cerrarModalCumples();
      }
    });
  }
});
