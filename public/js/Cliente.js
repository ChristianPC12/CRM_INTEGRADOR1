// Estado de edición
let editandoId = null;

let clientesOriginales = []; // Nueva variable para guardar TODOS los clientes
let clientesActuales = []; // Guardará los clientes filtrados para la tabla

// Referencias a elementos del formulario y botones
const form = document.getElementById("clienteForm");
const lista = document.getElementById("clienteLista");
const submitBtn = document.getElementById("submitBtn");
const cancelBtn = document.getElementById("cancelBtn");
const campoId = document.getElementById("id");

/**
 * Validaciones del formulario
 */
const validaciones = {
  esCedulaValida: (cedula) => {
    // Validar formato de cédula costarricense (9 dígitos)
    return /^\d{9}$/.test(cedula);
  },

  esEmailValido: (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  },

  esTelefonoValido: (telefono) => {
    // Validar teléfono costarricense (8 dígitos)
    return /^\d{8}$/.test(telefono);
  },

  esNombreValido: (nombre) => {
    // Solo letras, espacios y acentos, mínimo 2 caracteres
    return /^[a-zA-ZÀ-ÿ\s]{2,}$/.test(nombre);
  },

  esFechaValida: (fecha) => {
    if (!fecha) return false;
    const fechaNacimiento = new Date(fecha);
    const hoy = new Date();
    const edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
    return edad >= 0 && edad <= 120;
  },
};

/**
 * Carga todos los clientes desde el servidor y los muestra en la tabla.
 */
const cargarClientes = async () => {
  try {
    lista.innerHTML = `<div class="text-center p-3">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2">Cargando clientes...</p>
    </div>`;

    const res = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=readAll",
    });
    const response = await res.json();
    if (response.success && response.data) {
      mostrarClientes(response.data);
    } else {
      lista.innerHTML =
        '<div class="alert alert-warning">No se pudieron cargar los clientes</div>';
    }
  } catch {
    lista.innerHTML = '<div class="alert alert-danger">Error de conexión</div>';
  }
};

const mostrarClientes = (clientes) => {
  clientesActuales = clientes; // Guardar para eventos de la tabla
  
  // Si es la primera vez o estamos cargando todos los clientes, actualizar originales
  if (clientes.length > 0 && clientesOriginales.length === 0) {
    clientesOriginales = [...clientes];
  }

  if (clientes.length === 0) {
    lista.innerHTML =
      '<div class="alert alert-info">No hay clientes registrados</div>';
    return;
  }

  lista.innerHTML = `
    <table class="table table-striped table-hover mt-4" id="tablaClientes">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Cédula</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Lugar</th><th>Cumpleaños</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            ${clientes
              .map(
                (cliente) => `
                <tr class="fila-cliente" data-id="${cliente.id}">
                    <td>${cliente.id}</td>
                    <td>${cliente.cedula}</td>
                    <td>${cliente.nombre.toUpperCase()}</td>
                    <td>${
                      cliente.correo ? cliente.correo.toUpperCase() : ""
                    }</td>
                    <td>${cliente.telefono}</td>
                    <td>${
                      cliente.lugarResidencia
                        ? cliente.lugarResidencia.toUpperCase()
                        : ""
                    }</td>
                    <td>${cliente.fechaCumpleanos}</td>
                    <td>
                        <button class="btn btn-sm btn-warning me-1" onclick="editarCliente('${
                          cliente.id
                        }')">Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarCliente('${
                          cliente.id
                        }')">Eliminar</button>
                    </td>
                </tr>
              `
              )
              .join("")}
        </tbody>
    </table>
  `;

  // ---- EVENTO CLICK EN FILA (autollenar formulario) ----
  document.querySelectorAll(".fila-cliente").forEach((fila) => {
    fila.addEventListener("click", function (e) {
      // Evita que dispare si se da click en un botón dentro de la fila
      if (e.target.tagName === "BUTTON" || e.target.closest("button")) return;

      const id = this.getAttribute("data-id");
      const cliente = clientesActuales.find((c) => c.id == id);
      if (cliente) {
        // Autollenar formulario (mismos campos que editarCliente, pero instantáneo)
        campoId.value = cliente.id;
        form.cedula.value = cliente.cedula;
        form.nombre.value = cliente.nombre;
        form.correo.value = cliente.correo || "";
        form.telefono.value = cliente.telefono;
        form.lugarResidencia.value = cliente.lugarResidencia || "";
        form.fechaCumpleanos.value = cliente.fechaCumpleanos;
        submitBtn.textContent = "Actualizar Cliente";
        submitBtn.className = "btn btn-warning";
        editandoId = cliente.id;
        campoId.disabled = true;
        cancelBtn.style.display = "inline-block";
        form.cedula.focus();
      }
    });
  });
};

// Función para inicializar o actualizar la lista completa de clientes
const cargarClientesCompletos = (clientes) => {
  clientesOriginales = [...clientes]; // Guardar copia completa
  mostrarClientes(clientes);
};

// Buscador en tiempo real por número de tarjeta (ID) o nombre
document.addEventListener("DOMContentLoaded", () => {
  const buscador = document.getElementById("buscadorClientes");
  if (buscador) {
    buscador.addEventListener("input", function () {
      const valor = this.value.trim().toLowerCase();
      
      // CLAVE: Siempre filtra sobre la lista ORIGINAL completa
      if (!valor) {
        mostrarClientes(clientesOriginales); // Mostrar TODOS los clientes
        return;
      }
      
      const filtrados = clientesOriginales.filter(c =>
        (c.nombre && c.nombre.toLowerCase().includes(valor)) ||
        (c.id && c.id.toString().includes(valor))
      );
      
      mostrarClientes(filtrados);
    });
  }
});



/**
 * Carga los datos del cliente seleccionado para edición.
 */
const editarCliente = async (id) => {
  try {
    const res = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=read&id=${id}`,
    });
    const response = await res.json();
    if (response.success && response.data) {
      const cliente = response.data;
      campoId.value = cliente.id;
      form.cedula.value = cliente.cedula;
      form.nombre.value = cliente.nombre;
      form.correo.value = cliente.correo;
      form.telefono.value = cliente.telefono;
      form.lugarResidencia.value = cliente.lugarResidencia;
      form.fechaCumpleanos.value = cliente.fechaCumpleanos;
      submitBtn.textContent = "Actualizar Cliente";
      submitBtn.className = "btn btn-warning";
      editandoId = cliente.id;
      campoId.disabled = true;
      cancelBtn.style.display = "inline-block";

      // Llevar el focus a cédula
      form.cedula.focus();
    }
  } catch {
    alert("Error al cargar los datos del cliente");
  }
};

/**
 * Elimina un cliente mediante confirmación.
 */
const eliminarCliente = async (id) => {
  if (!confirm("¿Estás seguro de que quieres eliminar este cliente?")) return;
  try {
    const res = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=delete&id=${id}`,
    });
    const response = await res.json();
    alert(response.message);
    if (response.success) {
      cargarClientes();
      
      // ✨ ACTUALIZAR BADGE DE CUMPLEAÑOS SI EXISTE LA FUNCIÓN
      if (window.actualizarCumpleBadgeSidebar) {
        window.actualizarCumpleBadgeSidebar();
      }
    }
  } catch {
    alert("Error al eliminar el cliente");
  }
};

/**
 * Restaura el formulario a su estado inicial.
 */
const cancelarEdicion = () => {
  editandoId = null;
  form.reset();
  campoId.disabled = true;
  campoId.value = "";
  submitBtn.textContent = "Guardar Cliente";
  submitBtn.className = "btn btn-primary";
  cancelBtn.style.display = "none";

  // Limpiar mensajes de validación personalizados
  const inputs = form.querySelectorAll("input");
  inputs.forEach((input) => input.setCustomValidity(""));
};

// Manejador del envío del formulario
form.onsubmit = async (e) => {
  e.preventDefault();

  // Obtener valores del formulario
  const cedula = form.cedula.value.trim();
  const nombre = form.nombre.value.trim();
  const correo = form.correo.value.trim();
  const telefono = form.telefono.value.trim();
  const lugarResidencia = form.lugarResidencia.value.trim();
  const fechaCumpleanos = form.fechaCumpleanos.value;

  // Validaciones
  if (!cedula || !nombre || !telefono || !lugarResidencia || !fechaCumpleanos) {
    alert("Todos los campos son obligatorios (excepto el correo).");
    return;
  }

  if (!(cedula.length === 9 || cedula.length === 14)) {
    alert("La cédula debe tener 9 o 14 dígitos.");
    form.cedula.focus();
    return;
  }


  if (!validaciones.esNombreValido(nombre)) {
    alert("El nombre solo puede contener letras y espacios, mínimo 2 caracteres.");
    form.nombre.focus();
    return;
  }


  if (correo && !validaciones.esEmailValido(correo)) {
    alert("Por favor ingrese un correo electrónico válido.");
    form.correo.focus();
    return;
  }

  if (!validaciones.esTelefonoValido(telefono.replace("-", ""))) {
    alert("El teléfono debe tener exactamente 8 dígitos.");
    form.telefono.focus();
    return;
  }

  if (!validaciones.esFechaValida(fechaCumpleanos)) {
    alert("Por favor ingrese una fecha de cumpleaños válida.");
    form.fechaCumpleanos.focus();
    return;
  }

  const formData = new FormData(form);
  const action = editandoId ? "update" : "create";
  formData.append("action", action);
  if (editandoId) formData.append("id", editandoId);

  try {
    const res = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
    method: "POST",
    body: formData,
  });
    const response = await res.json();

  if (!response.success) {
    alert(response.message || "Error al guardar el cliente.");
    return;
  }

    let msg = response.message || "Cliente guardado correctamente";
  if (response.debug) {
    msg += "\n\nDEBUG:\n" + response.debug;
  }
    alert(msg);
    cancelarEdicion();
    cargarClientes();
    
    // ✨ ACTUALIZAR BADGE DE CUMPLEAÑOS SI EXISTE LA FUNCIÓN
    if (window.actualizarCumpleBadgeSidebar) {
      window.actualizarCumpleBadgeSidebar();
    }
  } catch (error) {
   console.error("Error real al guardar cliente:", error);
    alert("Error de conexión al guardar");
}

};

// Inicialización cuando se carga la vista
document.addEventListener("DOMContentLoaded", () => {
  campoId.disabled = true;
  cancelBtn.style.display = "none";

  // Configurar validaciones y conversiones en tiempo real
  const cedulaInput = form.cedula;
  const nombreInput = form.nombre;
  const correoInput = form.correo;
  const telefonoInput = form.telefono;
  const lugarInput = form.lugarResidencia;
  const fechaInput = form.fechaCumpleanos;

 // Validaciones en tiempo real para cédula
  if (cedulaInput) {
   cedulaInput.addEventListener("input", function () {
      this.value = this.value.replace(/\D/g, ""); // Solo números

    // Limitar a 14 dígitos (máximo permitido)
     if (this.value.length > 14) {
       this.value = this.value.slice(0, 14);
     }

    this.setCustomValidity("");
    });

    cedulaInput.addEventListener("invalid", function () {
     this.setCustomValidity("Por favor ingrese una cédula válida (9 o 14 dígitos)");
    });
  }


 // Conversión automática a mayúsculas para nombre y límite de caracteres
  if (nombreInput) {
      nombreInput.addEventListener("input", function () {
      this.value = this.value.toUpperCase();

      // Limitar a 50 caracteres
    if (this.value.length > 50) {
      this.value = this.value.slice(0, 50);
    }

      this.setCustomValidity("");
      });

        nombreInput.addEventListener("invalid", function () {
        this.setCustomValidity("Por favor ingrese un nombre válido");
      });
  }


  // Conversión automática a mayúsculas para correo
  if (correoInput) {
    correoInput.addEventListener("input", function () {
      this.value = this.value.toUpperCase();
      this.setCustomValidity("");
    });

    correoInput.addEventListener("invalid", function () {
      this.setCustomValidity("Por favor ingrese un correo electrónico válido");
    });
  }

  // Validaciones para teléfono
 // Validaciones para teléfono con guion automático
  if (telefonoInput) {
    telefonoInput.addEventListener("input", function () {
    let valor = this.value.replace(/\D/g, ""); // Solo números

    // Limitar a 8 dígitos
    if (valor.length > 8) {
      valor = valor.slice(0, 8);
    }

    // Agregar guion en la posición 4 si hay más de 4 dígitos
    if (valor.length > 4) {
      this.value = valor.slice(0, 4) + '-' + valor.slice(4);
    } else {
      this.value = valor;
    }

    this.setCustomValidity("");
  });

   telefonoInput.addEventListener("invalid", function () {
    this.setCustomValidity("Por favor ingrese un teléfono válido de 8 dígitos");
    });
  }

  // Conversión automática a mayúsculas y validación para lugar de residencia
  if (lugarInput) {
    lugarInput.addEventListener("input", function () {
    this.value = this.value.toUpperCase();

    // Limitar a 50 caracteres
    if (this.value.length > 50) {
      this.value = this.value.slice(0, 50);
    }

    this.setCustomValidity("");
   });

    lugarInput.addEventListener("invalid", function () {
    this.setCustomValidity("Por favor ingrese el lugar de residencia");
    });
  }


  // Validación para fecha
  if (fechaInput) {
    fechaInput.addEventListener("invalid", function () {
      this.setCustomValidity(
        "Por favor seleccione una fecha de cumpleaños válida"
      );
    });

    fechaInput.addEventListener("input", function () {
      this.setCustomValidity("");
    });
  }

  cargarClientes();
});
