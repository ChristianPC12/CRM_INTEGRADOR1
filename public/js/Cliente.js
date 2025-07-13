// Estado de edición
let editandoId = null;

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
  }
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

/**
 * Muestra la lista de clientes en formato tabla.
 */
const mostrarClientes = (clientes) => {
  if (clientes.length === 0) {
    lista.innerHTML =
      '<div class="alert alert-info">No hay clientes registrados</div>';
    return;
  }

  lista.innerHTML = `
    <table class="table table-striped table-hover mt-4">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Cédula</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Lugar</th><th>Cumpleaños</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            ${clientes
              .map(
                (cliente) => `
                <tr>
                    <td>${cliente.id}</td>
                    <td>${cliente.cedula}</td>
                    <td>${cliente.nombre.toUpperCase()}</td>
                    <td>${cliente.correo.toUpperCase()}</td>
                    <td>${cliente.telefono}</td>
                    <td>${cliente.lugarResidencia.toUpperCase()}</td>
                    <td>${cliente.fechaCumpleanos}</td>
                    <td>
                        <button class="btn btn-sm btn-warning me-1" onclick="editarCliente('${cliente.id}')">Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarCliente('${cliente.id}')">Eliminar</button>
                    </td>
                </tr>
              `
              )
              .join("")}
        </tbody>
    </table>
  `;
};

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
    if (response.success) cargarClientes();
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
  const inputs = form.querySelectorAll('input');
  inputs.forEach(input => input.setCustomValidity(''));
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
  
  if (!validaciones.esCedulaValida(cedula)) {
    alert("La cédula debe tener exactamente 9 dígitos.");
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

  
  if (!validaciones.esTelefonoValido(telefono)) {
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
    let msg = response.message || "Cliente guardado correctamente";
    if (response.debug) {
      msg += "\n\nDEBUG:\n" + response.debug;
    }
    alert(msg);
    if (response.success) {
      cancelarEdicion();
      cargarClientes();
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
    cedulaInput.addEventListener('input', function() {
      // Solo permitir números
      this.value = this.value.replace(/\D/g, '');
      // Limitar a 9 dígitos
      if (this.value.length > 9) {
        this.value = this.value.slice(0, 9);
      }
      this.setCustomValidity('');
    });
    
    cedulaInput.addEventListener('invalid', function() {
      this.setCustomValidity('Por favor ingrese una cédula válida de 9 dígitos');
    });
  }
  
  // Conversión automática a mayúsculas para nombre
  if (nombreInput) {
    nombreInput.addEventListener('input', function() {
      this.value = this.value.toUpperCase();
      this.setCustomValidity('');
    });
    
    nombreInput.addEventListener('invalid', function() {
      this.setCustomValidity('Por favor ingrese un nombre válido');
    });
  }
  
  // Conversión automática a mayúsculas para correo
  if (correoInput) {
    correoInput.addEventListener('input', function() {
      this.value = this.value.toUpperCase();
      this.setCustomValidity('');
    });
    
    correoInput.addEventListener('invalid', function() {
      this.setCustomValidity('Por favor ingrese un correo electrónico válido');
    });
  }
  
  // Validaciones para teléfono
  if (telefonoInput) {
    telefonoInput.addEventListener('input', function() {
      // Solo permitir números
      this.value = this.value.replace(/\D/g, '');
      // Limitar a 8 dígitos
      if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
      }
      this.setCustomValidity('');
    });
    
    telefonoInput.addEventListener('invalid', function() {
      this.setCustomValidity('Por favor ingrese un teléfono válido de 8 dígitos');
    });
  }
  
  // Conversión automática a mayúsculas para lugar de residencia
  if (lugarInput) {
    lugarInput.addEventListener('input', function() {
      this.value = this.value.toUpperCase();
      this.setCustomValidity('');
    });
    
    lugarInput.addEventListener('invalid', function() {
      this.setCustomValidity('Por favor ingrese el lugar de residencia');
    });
  }
  
  // Validación para fecha
  if (fechaInput) {
    fechaInput.addEventListener('invalid', function() {
      this.setCustomValidity('Por favor seleccione una fecha de cumpleaños válida');
    });
    
    fechaInput.addEventListener('input', function() {
      this.setCustomValidity('');
    });
  }
  
  cargarClientes();
});