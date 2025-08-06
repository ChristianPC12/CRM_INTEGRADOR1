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
    window.location.href = "/CRM_INT/CRM/index.php?logout=1";
  }

  
}

window.addEventListener("load", function () {
  const toggleBtn = document.getElementById("menuToggle");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const sidebarLinks = document.querySelectorAll(".sidebar ul li a");

  if (!toggleBtn || !sidebar) {
    console.warn("⚠️ No se encontró el botón ☰ o el sidebar.");
    return;
  }

  // 🎯 MANTENER POSICIÓN DEL SCROLL EN SIDEBAR
  // Restaurar posición del scroll al cargar la página
  const savedScrollPosition = localStorage.getItem('sidebarScrollPosition');
  if (savedScrollPosition && sidebar) {
    sidebar.scrollTop = parseInt(savedScrollPosition);
  }

  // Guardar posición del scroll cuando se hace scroll en el sidebar
  if (sidebar) {
    sidebar.addEventListener('scroll', function() {
      localStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
    });
  }

  // 💫 CENTRAR ELEMENTO ACTIVO EN VISTA
  function centrarElementoActivo() {
    const elementoActivo = sidebar.querySelector('.active');
    if (elementoActivo && sidebar) {
      const offsetTop = elementoActivo.offsetTop;
      const sidebarHeight = sidebar.clientHeight;
      const elementHeight = elementoActivo.clientHeight;
      
      // Calcular posición para centrar el elemento
      const scrollPosition = offsetTop - (sidebarHeight / 2) + (elementHeight / 2);
      
      sidebar.scrollTo({
        top: Math.max(0, scrollPosition),
        behavior: 'smooth'
      });
      
      // Guardar la nueva posición
      localStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
    }
  }

  // Centrar elemento activo al cargar la página
  setTimeout(centrarElementoActivo, 100);

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

  // Cerrar al hacer click en un enlace (solo en móvil)
  sidebarLinks.forEach(link => {
    link.addEventListener("click", function () {
      // 🎯 GUARDAR POSICIÓN ANTES DE NAVEGAR
      if (sidebar) {
        localStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
      }
      
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
  //  COMENTADO: Logica del modal de tarjeta
// function mostrarModal() {
//   document.getElementById('modalTarjeta').style.display = 'flex';
//   document.getElementById('modalInputTarjeta').value = '';
//   document.getElementById('modalMensajeError').textContent = '';
//   document.getElementById('modalInputTarjeta').focus();
// }

// function cerrarModal() {
//   document.getElementById('modalTarjeta').style.display = 'none';
//   // Limpiar el campo de texto y el mensaje de error
//   document.getElementById('modalInputTarjeta').value = '';
//   document.getElementById('modalMensajeError').textContent = '';
// }

// function abrirModal() {
//   const modal = document.getElementById('modalTarjeta');
//   if (modal) {
//     modal.style.display = 'flex';
//     // Auto-focus en el campo de texto
//     setTimeout(() => {
//       const input = document.getElementById('modalInputTarjeta');
//       if (input) {
//         input.value = ''; // Limpiar campo
//         input.focus(); // Hacer focus
//       }
//     }, 100);
//   }
// }

// async function redirigirCompra() {
//   const tarjeta = document.getElementById('modalInputTarjeta').value.trim();
//   const mensajeError = document.getElementById('modalMensajeError');
//   mensajeError.textContent = '';

//   if (!tarjeta) {
//     mensajeError.textContent = "Por favor ingrese un número de tarjeta.";
//     return;
//   }

//   try {
//     const response = await fetch('/CRM_INT/CRM/controller/ClienteController.php', {
//       method: 'POST',
//       headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//       body: `action=read&id=${encodeURIComponent(tarjeta)}`
//     });

//     const json = await response.json();
//     if (!json.success || !json.data) {
//       mensajeError.textContent = "El número de tarjeta no existe.";
//       return;
//     }

//     console.log('Modal: Redirigiendo a beneficios con tarjeta:', tarjeta);
//     window.location.href = `/CRM_INT/CRM/index.php?view=compras&idCliente=${encodeURIComponent(tarjeta)}&buscar=auto`;
//   } catch {
//     mensajeError.textContent = "Error de conexión con el servidor.";
//   }
// }

// NUEVO: Lógica del modal de cumpleaños
function abrirModalCumples() {
  console.log('abrirModalCumples() llamada'); // Debug
  const modal = document.getElementById('modalCumples');
  console.log('Modal encontrado:', modal); // Debug
  
  if (modal) {
    modal.style.display = 'flex';
    cargarCumpleanosSemana();
  } else {
    console.error('Modal modalCumples no encontrado en el DOM');
  }
}

function cerrarModalCumples() {
  const modal = document.getElementById('modalCumples');
  if (modal) {
    modal.style.display = 'none';
  }
}

function irACumpleanos() {
  cerrarModalCumples();
  window.location.href = '/CRM_INT/CRM/index.php?view=cumple';
}

async function cargarCumpleanosSemana() {
  console.log('cargarCumpleanosSemana() llamada'); // Debug
  const contenedor = document.getElementById('listaCumpleanos');
  const rangoDiv = document.getElementById('rangoCumplesSemana');
  
  console.log('Contenedor:', contenedor); // Debug
  console.log('RangoDiv:', rangoDiv); // Debug
  
  if (!contenedor || !rangoDiv) {
    console.error('No se encontraron los elementos del modal');
    return;
  }
  
  // Mostrar el rango de la semana
  mostrarRangoSemana(rangoDiv);
  
  // Mostrar loading
  contenedor.innerHTML = `
    <div class="text-center p-3">
      <div class="spinner-border text-warning" role="status"></div>
      <p class="mt-2">Cargando cumpleaños...</p>
    </div>
  `;

  try {
    const response = await fetch('/CRM_INT/CRM/controller/CumpleController.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=readSemana'
    });

    const data = await response.json();

    if (data.success) {
      renderizarCumpleanos(data.data, contenedor);
    } else {
      contenedor.innerHTML = `<div class="alert alert-danger text-center">${data.message}</div>`;
    }
  } catch (error) {
    console.error('Error cargando cumpleaños:', error);
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

  const opciones = { day: '2-digit', month: 'long' };
  const formatoLunes = lunes.toLocaleDateString('es-CR', opciones);
  const formatoDomingo = domingo.toLocaleDateString('es-CR', opciones);

  div.innerHTML = `📆 Semana actual: <strong>${formatoLunes}</strong> al <strong>${formatoDomingo}</strong>`;
}

function renderizarCumpleanos(cumples, contenedor) {
  const pendientes = cumples.filter(c => c.estado === 'Activo');
  
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

  let html = '';
  
  pendientes.forEach(cumple => {
    const tieneCorreo = cumple.correo && cumple.correo.trim() !== '';
    const cardClass = tieneCorreo ? 'cumple-card' : 'cumple-card sin-correo';
    
    html += `
      <div class="${cardClass}">
        <div class="cumple-header">
          <h4 class="cumple-nombre">${cumple.nombre}</h4>
          <span class="cumple-fecha">${formatearFechaCumple(cumple.fechaCumpleanos)}</span>
        </div>
        
        <div class="cumple-info">
          <div class="cumple-detalle">
            <span class="cumple-label">Cédula:</span> ${cumple.cedula}
          </div>
          <div class="cumple-detalle">
            <span class="cumple-label">Teléfono:</span> ${cumple.telefono}
          </div>
          <div class="cumple-detalle">
            <span class="cumple-label">Correo:</span> ${tieneCorreo ? cumple.correo : '<span style="color: #ff6b6b;">Sin correo</span>'}
          </div>
        </div>
      </div>
    `;
  });
  
  contenedor.innerHTML = html;
}

function formatearFechaCumple(fecha) {
  return new Date(fecha).toLocaleDateString('es-CR', { 
    day: '2-digit', 
    month: 'short' 
  });
}

// COMENTADO: Funciones de acciones que ya no se usan en el modal simplificado
// async function enviarCorreoCumple(id, nombre, correo) { ... }
// async function llamarCliente(telefono, nombre) { ... }
// async function marcarComoListo(id, nombre) { ... }
// async function cambiarEstadoCumple(id, estado) { ... }


// COMENTADO: Event listeners del modal de tarjeta
// document.addEventListener("DOMContentLoaded", function () {
//   const inputTarjeta = document.getElementById("modalInputTarjeta");
//   if (inputTarjeta) {
//     inputTarjeta.addEventListener("keydown", function (e) {
//       if (e.key === "Enter") {
//         e.preventDefault();
//         redirigirCompra();
//       }
//     });
//   }
// });

// Badge de cumpleaños pendientes (global)
// (Eliminar las funciones window.mostrarCumpleBadge y window.actualizarCumpleBadgeSidebar)
window.actualizarCumpleBadgeSidebar = function() {
    fetch('/CRM_INT/CRM/controller/CumpleController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=readSemana'
    })
    .then(res => res.json())
    .then(data => {
        const badge = document.getElementById('cumple-badge');
        if (!badge) return;
        if (data.success && data.data && data.data.length > 0) {
            badge.style.display = 'inline-block';
            badge.innerHTML = `<span style="display:inline-block;width:12px;height:12px;background:#f9c41f;border-radius:50%;border:2px solid #000;box-shadow:0 0 2px #000;vertical-align:middle;"></span>`;
        } else {
            badge.style.display = 'none';
            badge.innerHTML = '';
        }
    })
    .catch(() => {
        const badge = document.getElementById('cumple-badge');
        if (badge) {
            badge.style.display = 'none';
            badge.innerHTML = '';
        }
    });
};
document.addEventListener('DOMContentLoaded', function() {
    window.actualizarCumpleBadgeSidebar();
    
    // COMENTADO: Event listeners del modal de tarjeta
    // const modal = document.getElementById('modalTarjeta');
    // if (modal) {
    //     modal.addEventListener('click', function(e) {
    //         if (e.target === modal) {
    //             cerrarModal();
    //         }
    //     });
        
    //     document.addEventListener('keydown', function(e) {
    //         if (e.key === 'Escape' && modal.style.display === 'flex') {
    //             cerrarModal();
    //         }
    //     });
    // }

    // NUEVO: Event listeners para el modal de cumpleaños
    const modalCumples = document.getElementById('modalCumples');
    if (modalCumples) {
        modalCumples.addEventListener('click', function(e) {
            if (e.target === modalCumples) {
                cerrarModalCumples();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalCumples.style.display === 'flex') {
                cerrarModalCumples();
            }
        });
    }
});



