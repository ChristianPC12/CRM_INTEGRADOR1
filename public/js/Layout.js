// Archivo: public/js/Layout.js
// Detectar cuando se cierra la ventana/pestaña y cerrar sesión

document.addEventListener("DOMContentLoaded", function () {
  // Detectar cierre de ventana/pestaña
  window.addEventListener("beforeunload", function (e) {
    // Enviar petición síncrona para cerrar sesión
    navigator.sendBeacon(
      "/CRM_INT/CRM/controller/SessionController.php",
      JSON.stringify({ action: "close_session" })
    );
  });

  // También detectar cuando se abandona la página
  window.addEventListener("unload", function (e) {
    // Backup por si beforeunload no funciona
    navigator.sendBeacon(
      "/CRM_INT/CRM/controller/SessionController.php",
      JSON.stringify({ action: "close_session" })
    );
  });

  // Detectar cambio de visibilidad de la página (opcional, más agresivo)
  document.addEventListener("visibilitychange", function () {
    if (document.visibilityState === "hidden") {
      // Opcional: cerrar sesión cuando se oculta la página
      // Descomenta la siguiente línea si quieres que sea más estricto
      // navigator.sendBeacon('/CRM_INT/CRM/controller/SessionController.php', JSON.stringify({action: 'close_session'}));
    }
  });
});

// Función para confirmar cierre de sesión manual
function confirmarCerrarSesion() {
  if (confirm("¿Cerrar sesión?")) {
    window.location.href = "index.php?logout=1";
  }

  
}

window.addEventListener("load", function () {
  const toggleBtn = document.getElementById("menuToggle");
  const sidebar = document.querySelector(".sidebar");
  const sidebarLinks = document.querySelectorAll(".sidebar ul li a");

  if (!toggleBtn || !sidebar) {
    console.warn("⚠️ No se encontró el botón ☰ o el sidebar.");
    return;
  }

  //  Forzar sidebar cerrado en móvil al cargar (clave para evitar bug al cambiar de vista)
  if (window.innerWidth <= 768) {
    sidebar.classList.remove("activa");
  }

  toggleBtn.addEventListener("click", function () {
    sidebar.classList.toggle("activa");
  });

  sidebarLinks.forEach(link => {
    link.addEventListener("click", function () {
      if (window.innerWidth <= 768) {
        sidebar.classList.remove("activa");
      }
    });
  });
});
  //  Logica del modal de tarjeta
function mostrarModal() {
  document.getElementById('modalTarjeta').style.display = 'flex';
  document.getElementById('modalInputTarjeta').value = '';
  document.getElementById('modalMensajeError').textContent = '';
  document.getElementById('modalInputTarjeta').focus();
}

function cerrarModal() {
  document.getElementById('modalTarjeta').style.display = 'none';
}


async function redirigirCompra() {
  const tarjeta = document.getElementById('modalInputTarjeta').value.trim();
  const mensajeError = document.getElementById('modalMensajeError');
  mensajeError.textContent = '';

  if (!tarjeta) {
    mensajeError.textContent = "Por favor ingrese un número de tarjeta.";
    return;
  }

  try {
    const response = await fetch('/CRM_INT/CRM/controller/ClienteController.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=read&id=${encodeURIComponent(tarjeta)}`
    });

    const json = await response.json();
    if (!json.success || !json.data) {
      mensajeError.textContent = "El número de tarjeta no existe.";
      return;
    }

    window.location.href = `index.php?view=compras&idCliente=${encodeURIComponent(tarjeta)}`;
  } catch {
    mensajeError.textContent = "Error de conexión con el servidor.";
  }
}


document.addEventListener("DOMContentLoaded", function () {
  const inputTarjeta = document.getElementById("modalInputTarjeta");
  if (inputTarjeta) {
    inputTarjeta.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        redirigirCompra();
      }
    });
  }
});



