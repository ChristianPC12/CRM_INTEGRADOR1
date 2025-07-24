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
    // Elimina guiones y espacios antes de validar
    const soloDigitos = telefono.replace(/[^\d]/g, "");
    return /^\d{8}$/.test(soloDigitos);
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
                    <td class="celda-correo">${
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
                        <div class="acciones-cliente">
                            <div class="columna-izquierda">
                                <button class="btn btn-sm btn-warning" onclick="editarCliente('${
                                  cliente.id
                                }')">Editar</button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarCliente('${
                                  cliente.id
                                }')">Eliminar</button>
                            </div>
                            <div class="columna-derecha">
                                <button class="btn btn-sm" style="background-color: #111; color: #FFD600; border: none;" onclick="abrirModalReasignar(${cliente.id}, \`${cliente.nombre}\`, '${cliente.cedula}')">Reasignar</button>
                            </div>
                        </div>
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
  const telefonoLimpio = telefono.replace("-", "");
  const lugarResidencia = form.lugarResidencia.value.trim();
  const fechaCumpleanos = form.fechaCumpleanos.value;

  // Validaciones
  if (!cedula || !nombre || !telefono || !lugarResidencia || !fechaCumpleanos) {
    alert("Todos los campos son obligatorios (excepto el correo).");
    return;
  }

  if (!/^\d{9}$|^\d{14}$/.test(cedula)) {
    alert("La cédula debe tener exactamente 9 o 14 dígitos numéricos.");
    form.cedula.focus();
    return;
  }

  if (telefonoLimpio.length !== 8) {
    alert("El teléfono debe tener exactamente 8 dígitos.");
    form.telefono.focus();
    return;
  }




  if (!validaciones.esNombreValido(nombre)) {
    alert("El nombre solo puede contener letras y espacios.");
    form.nombre.focus();
    return;
  }
  
  if (nombre.length > 45) {
   alert("El nombre no puede tener más de 45 caracteres.");
    form.nombre.focus();
    return;
  }

  if (correo && !validaciones.esEmailValido(correo)) {
    alert("Por favor ingrese un correo electrónico válido.");
    form.correo.focus();
    return;
  }


  if (!validaciones.esFechaValida(fechaCumpleanos)) {
    alert("Por favor ingrese una fecha de cumpleaños válida.");
    form.fechaCumpleanos.focus();
    return;
  }

  const formData = new FormData(form);
  formData.set("telefono", telefonoLimpio);
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

});

/**
 * Funciones para reasignación de códigos
 */

// Variables globales para el modal de reasignación
let clienteParaReasignar = null;

/**
 * Abre el modal de reasignación de código
 */
const abrirModalReasignar = (idCliente, nombreCliente, cedulaCliente) => {
  console.log('Abriendo modal para cliente:', idCliente);
  
  clienteParaReasignar = {
    id: idCliente,
    nombre: nombreCliente,
    cedula: cedulaCliente
  };

  // Actualizar información del cliente en el modal
  const modalClienteNombre = document.getElementById('modalClienteNombre');
  const modalClienteCedula = document.getElementById('modalClienteCedula');
  const modalClienteId = document.getElementById('modalClienteId');
  const motivoSelect = document.getElementById('motivoSelect');
  const motivoReasignacion = document.getElementById('motivoReasignacion');
  
  if (modalClienteNombre) modalClienteNombre.textContent = nombreCliente;
  if (modalClienteCedula) modalClienteCedula.textContent = cedulaCliente;
  if (modalClienteId) modalClienteId.textContent = idCliente;
  
  // Limpiar y resetear los campos de motivo
  if (motivoSelect) motivoSelect.selectedIndex = 0;
  if (motivoReasignacion) {
    motivoReasignacion.value = '';
    motivoReasignacion.style.display = 'none';
  }
  
  // Verificar si el modal existe
  const modalElement = document.getElementById('modalReasignar');
  if (!modalElement) {
    console.error('Modal no encontrado');
    alert('Error: No se pudo encontrar el modal');
    return;
  }
  
  // Mostrar el modal
  try {
    const modal = new bootstrap.Modal(modalElement, {
      backdrop: false,   // Sin fondo oscuro
      keyboard: true,    // Permite cerrar con ESC
      focus: true        // Enfoca el modal al abrirse
    });
    modal.show();
  } catch (error) {
    console.error('Error al abrir modal:', error);
    // Fallback: usar jQuery si está disponible
    if (typeof $ !== 'undefined') {
      $('#modalReasignar').modal('show');
    } else {
      alert('Error al abrir el modal. Por favor recarga la página e intenta de nuevo.');
    }
  }
};

/**
 * Maneja la selección del motivo de reasignación
 */
const manejarSeleccionMotivo = () => {
  const motivoSelect = document.getElementById('motivoSelect');
  const motivoTextarea = document.getElementById('motivoReasignacion');
  
  const valorSeleccionado = motivoSelect.value;
  
  if (valorSeleccionado === 'otro') {
    // Mostrar textarea para motivo personalizado
    motivoTextarea.style.display = 'block';
    motivoTextarea.value = '';
    motivoTextarea.placeholder = 'Especifique el motivo...';
    motivoTextarea.focus();
  } else if (valorSeleccionado !== '') {
    // Ocultar textarea y usar el valor seleccionado
    motivoTextarea.style.display = 'none';
    motivoTextarea.value = valorSeleccionado;
  } else {
    // No hay selección, ocultar textarea
    motivoTextarea.style.display = 'none';
    motivoTextarea.value = '';
  }
};

/**
 * Procesa la reasignación del código
 */
const procesarReasignacion = async () => {
  try {
    if (!clienteParaReasignar || !clienteParaReasignar.id) {
      alert('Error: No se ha seleccionado un cliente válido');
      return;
    }

    // Obtener el motivo correcto
    const motivoSelect = document.getElementById('motivoSelect');
    let motivo = '';
    
    if (motivoSelect && motivoSelect.value) {
      if (motivoSelect.value === 'otro') {
        const motivoReasignacion = document.getElementById('motivoReasignacion');
        motivo = motivoReasignacion ? motivoReasignacion.value.trim() : '';
        
        if (!motivo) {
          alert('Por favor especifica el motivo de reasignación');
          return;
        }
      } else {
        motivo = motivoSelect.value;
      }
    } else {
      alert('Por favor selecciona un motivo para la reasignación');
      return;
    }

    const btnConfirmar = document.getElementById('btnConfirmarReasignacion');
    const textoOriginal = btnConfirmar.textContent;
    btnConfirmar.disabled = true;
    btnConfirmar.textContent = 'Procesando...';

    // El procedimiento ahora se encarga de encontrar el código activo internamente
    const res = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=reassignCode&idCliente=${clienteParaReasignar.id}&motivo=${encodeURIComponent(motivo)}`,
    });

    const response = await res.json();

    btnConfirmar.disabled = false;
    btnConfirmar.textContent = textoOriginal;

    if (response.success) {
      alert(`Código reasignado exitosamente.\nNuevo código: ${response.nuevoCodigo}`);
      
      // Cerrar el modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalReasignar'));
      modal.hide();
      
      // Recargar la lista de clientes Y el historial de reasignaciones
      cargarClientes();
      cargarHistorialReasignaciones(true); // Manual después de reasignación
    } else {
      alert(`Error al reasignar código: ${response.message}`);
    }

  } catch (error) {
    console.error('Error:', error);
    alert('Error de conexión al procesar la reasignación');
    
    const btnConfirmar = document.getElementById('btnConfirmarReasignacion');
    btnConfirmar.disabled = false;
    btnConfirmar.textContent = 'Confirmar';
  }
};

// Inicialización cuando se carga la vista
document.addEventListener("DOMContentLoaded", () => {
  cargarClientes();
  
  // Verificar que el modal existe al cargar la página
  const modal = document.getElementById('modalReasignar');
  if (modal) {
    console.log('Modal de reasignación encontrado correctamente');
    
    // Agregar evento para cerrar modal al hacer clic en el fondo
    modal.addEventListener('click', function(event) {
      if (event.target === modal) {
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
          bootstrapModal.hide();
        }
      }
    });
    
    // Agregar evento para cerrar con ESC
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal && modal.classList.contains('show')) {
          bootstrapModal.hide();
        }
      }
    });
    
  } else {
    console.error('Modal de reasignación NO encontrado en el DOM');
  }
});

/**
 * Actualiza el estado visual de la actualización automática
 */
const actualizarEstadoVisualizacion = (estado) => {
  const estadoElement = document.getElementById('estadoActualizacion');
  if (estadoElement) {
    switch (estado) {
      case 'cargando':
        estadoElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
        break;
      case 'activo':
        estadoElement.innerHTML = 'Actualización automática activa';
        break;
      case 'error':
        estadoElement.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error en actualización';
        break;
      default:
        estadoElement.innerHTML = 'Actualización automática activa';
    }
  }
};

/**
 * Carga el historial de reasignaciones desde el servidor
 */
const cargarHistorialReasignaciones = async (esManual = false) => {
  const contenedor = document.getElementById("historialReasignaciones");
  
  // Solo mostrar spinner en carga manual, no en automática
  const esActualizacionManual = esManual || !intervaloActualizacion;
  
  if (esActualizacionManual) {
    contenedor.innerHTML = `<div class="text-center p-3">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2">Cargando historial...</p>
    </div>`;
  } else {
    // Para actualización automática, solo cambiar el estado
    actualizarEstadoVisualizacion('cargando');
  }
  
  try {
    // Agregar timestamp para evitar cache del navegador
    const timestamp = new Date().getTime();
    const res = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
      method: "POST",
      headers: { 
        "Content-Type": "application/x-www-form-urlencoded",
        "Cache-Control": "no-cache"
      },
      body: `action=getHistorialReasignaciones&timestamp=${timestamp}`
    });
    
    const response = await res.json();
    
    if (response.success && response.data) {
      // Procesar los datos para obtener el historial de reasignaciones
      const historialProcesado = procesarHistorialReasignaciones(response.data.codigos, response.data.clientes);
      mostrarHistorialReasignaciones(historialProcesado);
      
      // Actualizar estado visual
      actualizarEstadoVisualizacion('activo');
    } else {
      contenedor.innerHTML = '<div class="alert alert-warning">No se pudieron cargar los registros de reasignaciones</div>';
      actualizarEstadoVisualizacion('error');
    }
  } catch (error) {
    console.error('Error cargando historial:', error);
    contenedor.innerHTML = '<div class="alert alert-danger">Error de conexión al cargar el historial</div>';
    actualizarEstadoVisualizacion('error');
  }
};

/**
 * Procesa los datos de códigos para extraer el historial de reasignaciones
 */
const procesarHistorialReasignaciones = (codigos, clientes) => {
  const ahora = new Date().toLocaleTimeString();
  console.log(`[${ahora}] Procesando historial de reasignaciones...`);
  console.log('Total códigos recibidos:', codigos.length);
  console.log('Total clientes recibidos:', clientes.length);
  
  // Crear un mapa de clientes para búsqueda rápida
  const clientesMap = {};
  clientes.forEach(cliente => {
    clientesMap[cliente.id] = cliente;
  });
  
  // Agrupar por cliente y separar códigos activos/inactivos
  const agrupadoPorCliente = {};
  codigos.forEach(codigo => {
    const idCliente = codigo.idCliente;
    const cliente = clientesMap[idCliente];
    if (!agrupadoPorCliente[idCliente]) {
      agrupadoPorCliente[idCliente] = {
        cliente: {
          id: idCliente,
          nombre: cliente ? cliente.nombre : 'Cliente no encontrado',
          cedula: cliente ? cliente.cedula : '-'
        },
        codigoActivo: null,
        reasignaciones: [],
        totalReasignaciones: 0
      };
    }
    if (codigo.estado === 'Activo') {
      agrupadoPorCliente[idCliente].codigoActivo = {
        codigoBarra: codigo.codigoBarra,
        cantImpresiones: codigo.cantImpresiones || 0,
        fechaRegistro: codigo.fechaRegistro
      };
    } else if (codigo.estado === 'Inactivo' && codigo.motivoCambio && codigo.motivoCambio.trim() !== '') {
      agrupadoPorCliente[idCliente].reasignaciones.push({
        codigoBarra: codigo.codigoBarra,
        cantImpresiones: codigo.cantImpresiones || 0,
        motivoCambio: codigo.motivoCambio,
        fechaCambio: codigo.fechaCambio,
        fechaRegistro: codigo.fechaRegistro
      });
      agrupadoPorCliente[idCliente].totalReasignaciones++;
    }
  });
  // Ordenar las reasignaciones por fecha más reciente primero
  Object.values(agrupadoPorCliente).forEach(cliente => {
    cliente.reasignaciones.sort((a, b) => {
      const fechaA = new Date(a.fechaCambio || a.fechaRegistro || '1970-01-01');
      const fechaB = new Date(b.fechaCambio || b.fechaRegistro || '1970-01-01');
      if (fechaA.getTime() === fechaB.getTime()) {
        return (b.cantImpresiones || 0) - (a.cantImpresiones || 0);
      }
      return fechaB - fechaA;
    });
    // Para ordenar la lista principal por la fecha más reciente (activo o inactivo)
    let fechaMasReciente = null;
    if (cliente.codigoActivo) {
      fechaMasReciente = new Date(cliente.codigoActivo.fechaRegistro || '1970-01-01');
    } else if (cliente.reasignaciones.length > 0) {
      fechaMasReciente = new Date(cliente.reasignaciones[0].fechaCambio || cliente.reasignaciones[0].fechaRegistro || '1970-01-01');
    } else {
      fechaMasReciente = new Date('1970-01-01');
    }
    cliente.fechaMasReciente = fechaMasReciente;
  });
  // Ordenar clientes por fecha más reciente
  const historialArray = Object.values(agrupadoPorCliente).sort((a, b) => b.fechaMasReciente - a.fechaMasReciente);
  const ahora2 = new Date().toLocaleTimeString();
  console.log(`[${ahora2}] Historial procesado:`, historialArray.length, 'clientes con reasignaciones');
  historialArray.forEach(cliente => {
    console.log(`Cliente ${cliente.cliente.nombre}: ${cliente.totalReasignaciones} reasignaciones`);
  });
  return historialArray;
};

/**
 * Muestra el historial de reasignaciones en una tabla
 */
const mostrarHistorialReasignaciones = (historialPorCliente) => {
  const contenedor = document.getElementById("historialReasignaciones");
  
  if (!historialPorCliente || historialPorCliente.length === 0) {
    contenedor.innerHTML = '<div class="alert alert-info">No hay reasignaciones registradas</div>';
    return;
  }

  const formatearFecha = (fecha) => {
    if (!fecha) return '-';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-CR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });
  };

  // Mostrar solo la fila del código activo por cliente
  const filasClientes = historialPorCliente.map((clienteData) => {
    const cliente = clienteData.cliente;
    const codigoActivo = clienteData.codigoActivo;
    if (!codigoActivo) return '';
    // Buscar la última reasignación (inactiva más reciente)
    let ultimoMotivo = '-';
    let ultimaFecha = '-';
    if (clienteData.reasignaciones && clienteData.reasignaciones.length > 0) {
      const ultimaReasignacion = clienteData.reasignaciones[0];
      ultimoMotivo = ultimaReasignacion.motivoCambio || '-';
      ultimaFecha = formatearFecha(ultimaReasignacion.fechaCambio || ultimaReasignacion.fechaRegistro);
    }
    return `
      <tr class="fila-cliente">
        <td class="text-center">
          <strong>${cliente.id}</strong>
        </td>
        <td>
          <strong>${cliente.cedula || '-'}</strong>
        </td>
        <td>
          <strong>${cliente.nombre || '-'}</strong>
        </td>
        <td class="text-center">
          <code class="codigo-barra">${codigoActivo.codigoBarra}</code>
          <br>
          <small class="text-success">(activo)</small>
        </td>
        <td class="text-center">
          <span class="badge bg-warning text-dark">${clienteData.totalReasignaciones}</span>
        </td>
        <td class="text-center">
          <span class="badge bg-success">Activo</span>
        </td>
        <td class="motivo-cell">${ultimoMotivo}</td>
        <td class="text-center">
          <small class="text-muted">${ultimaFecha}</small>
        </td>
      </tr>
    `;
  }).join('');

  contenedor.innerHTML = `
    <table class="table table-striped table-hover mt-2">
      <thead class="table-dark">
        <tr>
          <th class="text-center">Tarjeta</th>
          <th>Cédula</th>
          <th>Cliente</th>
          <th class="text-center">Código</th>
          <th class="text-center">Total Reasignaciones</th>
          <th class="text-center">Estado</th>
          <th>Último Motivo</th>
          <th class="text-center">Fecha</th>
        </tr>
      </thead>
      <tbody>
        ${filasClientes}
      </tbody>
    </table>
  `;
};

/**
 * Función para mostrar/ocultar detalles de reasignaciones de un cliente
 */
window.toggleDetalleReasignaciones = function(clienteIndex) {
  const detalles = document.querySelectorAll(`#detalle-${clienteIndex}`);
  const isVisible = detalles[0]?.style.display !== 'none';
  
  detalles.forEach(detalle => {
    detalle.style.display = isVisible ? 'none' : '';
    if (!isVisible) {
      detalle.style.backgroundColor = '#f8f9fa';
    }
  });
};

/**
 * Actualización automática del historial cada 30 segundos
 */
let intervaloActualizacion = null;

const iniciarActualizacionAutomatica = () => {
  // Limpiar cualquier intervalo existente
  if (intervaloActualizacion) {
    clearInterval(intervaloActualizacion);
  }
  
  // Actualizar cada 30 segundos
  intervaloActualizacion = setInterval(() => {
    const ahora = new Date().toLocaleTimeString();
    console.log(`[${ahora}] Actualizando historial automáticamente...`);
    cargarHistorialReasignaciones();
  }, 30000); // 30 segundos
  
  console.log('Auto-refresh iniciado: cada 30 segundos');
};

const detenerActualizacionAutomatica = () => {
  if (intervaloActualizacion) {
    clearInterval(intervaloActualizacion);
    intervaloActualizacion = null;
  }
};

// Cargar historial al inicializar la página
document.addEventListener("DOMContentLoaded", function() {
  cargarHistorialReasignaciones(true); // Manual al cargar la página
  iniciarActualizacionAutomatica();
  
  // Detener actualización automática cuando se cambia de página
  window.addEventListener('beforeunload', detenerActualizacionAutomatica);
});
document.addEventListener("DOMContentLoaded", () => {
  const cedulaInput = document.getElementById("clienteCedula");

  cedulaInput.addEventListener("input", function () {
    this.value = this.value.replace(/\D/g, "");
    if (this.value.length > 14) {
      this.value = this.value.slice(0, 14);
    }
  });

  const nombreInput = document.getElementById("clienteNombre");

  nombreInput.addEventListener("input", function () {
    // Solo letras (mayúsculas, minúsculas) y espacios
    this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, "");

    // Limita a 45 caracteres
    if (this.value.length > 45) {
      this.value = this.value.slice(0, 45);
    }
  });
   const cantones = [
    "LIBERIA, GUANACASTE",
  "NICOYA, GUANACASTE",
  "SANTA CRUZ, GUANACASTE",
  "BAGACES, GUANACASTE",
  "CARRILLO, GUANACASTE",
  "CAÑAS, GUANACASTE",
  "ABANGARES, GUANACASTE",
  "TILARÁN, GUANACASTE",
  "NANDAYURE, GUANACASTE",
  "LA CRUZ, GUANACASTE",
  "HOJANCHA, GUANACASTE",
  "PUNTARENAS, PUNTARENAS",
  "ESPARZA, PUNTARENAS",
  "BUENOS AIRES, PUNTARENAS",
  "MONTES DE ORO, PUNTARENAS",
  "OSA, PUNTARENAS",
  "AGUIRRE, PUNTARENAS",
  "GOLFITO, PUNTARENAS",
  "COTO BRUS, PUNTARENAS",
  "PARAÍSO, PUNTARENAS",
  "CORREDORES, PUNTARENAS",
  "GARABITO, PUNTARENAS",
  "SAN JOSÉ, SAN JOSÉ",
  "ESCAZÚ, SAN JOSÉ",
  "DESAMPARADOS, SAN JOSÉ",
  "PURISCAL, SAN JOSÉ",
  "TARAZU, SAN JOSÉ",
  "ASERRÍ, SAN JOSÉ",
  "MORA, SAN JOSÉ",
  "GOICOECHEA, SAN JOSÉ",
  "SANTA ANA, SAN JOSÉ",
  "ALAJUELITA, SAN JOSÉ",
  "VÁSQUEZ DE CORONADO, SAN JOSÉ",
  "ACOSTA, SAN JOSÉ",
  "TIBÁS, SAN JOSÉ",
  "MORAVIA, SAN JOSÉ",
  "MONTES DE OCA, SAN JOSÉ",
  "TURRUBARES, SAN JOSÉ",
  "DOTA, SAN JOSÉ",
  "CURRIDABAT, SAN JOSÉ",
  "PÉREZ ZELEDÓN, SAN JOSÉ",
  "LEÓN CORTÉS, SAN JOSÉ",
  "ALAJUELA, ALAJUELA",
  "SAN RAMÓN, ALAJUELA",
  "GRECIA, ALAJUELA",
  "SAN MATEO, ALAJUELA",
  "ATENAS, ALAJUELA",
  "NARANJO, ALAJUELA",
  "PALMARES, ALAJUELA",
  "POÁS, ALAJUELA",
  "OROTINA, ALAJUELA",
  "SAN CARLOS, ALAJUELA",
  "ZARCERO, ALAJUELA",
  "VALVERDE VEGA, ALAJUELA",
  "UPALA, ALAJUELA",
  "LOS CHILES, ALAJUELA",
  "GUATUSO, ALAJUELA",
  "RIO CUARTO, ALAJUELA",
  "HEREDIA, HEREDIA",
  "BARVA, HEREDIA",
  "SANTO DOMINGO, HEREDIA",
  "SANTA BÁRBARA, HEREDIA",
  "SAN RAFAEL, HEREDIA",
  "SAN ISIDRO, HEREDIA",
  "BELÉN, HEREDIA",
  "FLORES, HEREDIA",
  "SARAPIQUÍ, HEREDIA",
  "CARTAGO, CARTAGO",
  "PARAÍSO, CARTAGO",
  "LA UNIÓN, CARTAGO",
  "JIMÉNEZ, CARTAGO",
  "TURRIALBA, CARTAGO",
  "ALVARADO, CARTAGO",
  "OREAMUNO, CARTAGO",
  "EL GUARCO, CARTAGO",
  "LIMÓN, LIMÓN",
  "POCOCÍ, LIMÓN",
  "SIQUIRRES, LIMÓN",
  "TALAMANCA, LIMÓN",
  "MATINA, LIMÓN",
  "GUÁCIMO, LIMÓN"
  ];
   const datalist = document.getElementById("listaCantones");
  cantones.forEach((canton) => {
    const option = document.createElement("option");
    option.value = canton;
    datalist.appendChild(option);
  });
});
