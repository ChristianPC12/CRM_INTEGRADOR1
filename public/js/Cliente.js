// Estado de edición
let editandoId = null;

// Referencias a elementos del formulario y botones
const form = document.getElementById("clienteForm");
const lista = document.getElementById("clienteLista");
const submitBtn = document.getElementById("submitBtn");
const cancelBtn = document.getElementById("cancelBtn");
const campoId = document.getElementById("id");

/**
 * Carga todos los clientes desde el servidor y los muestra en la tabla.
 */
const cargarClientes = async () => {
  try {
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
                    <td>${cliente.nombre}</td>
                    <td>${cliente.correo}</td>
                    <td>${cliente.telefono}</td>
                    <td>${cliente.lugarResidencia}</td>
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
};

// Manejador del envío del formulario
form.onsubmit = async (e) => {
  e.preventDefault();
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
  cargarClientes();
});
