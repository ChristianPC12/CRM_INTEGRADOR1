let usuariosOriginales = []; // Nueva variable para guardar TODOS los usuarios
let usuariosActuales = []; // Guardará los usuarios filtrados para la tabla
let ultimoUsuarioGuardado = null; // Para hacer focus al usuario recién guardado

document.addEventListener("DOMContentLoaded", function () {
  let editandoId = null;

  // Validación de contraseña robusta y detallada
  function validarContrasena(contrasena) {
    let errores = [];
    if (contrasena.length < 6 || contrasena.length > 16) {
      errores.push("A la contraseña le falta entre 6 y 16 caracteres.");
    }
    if (!/[a-z]/.test(contrasena)) {
      errores.push("A la contraseña le falta al menos una letra minúscula.");
    }
    if (!/[A-Z]/.test(contrasena)) {
      errores.push("A la contraseña le falta al menos una letra mayúscula.");
    }
    if (!/\d/.test(contrasena)) {
      errores.push("A la contraseña le falta al menos un número.");
    }
    if (!/[^a-zA-Z0-9]/.test(contrasena)) {
      errores.push("A la contraseña le falta al menos un carácter especial.");
    }
    return errores;
  }

  // Validación de usuario completamente en mayúsculas
  function esUsuarioMayusculas(usuario) {
    return /^[A-Z]+$/.test(usuario);
  }

  const form = document.getElementById("usuarioForm");
  const formTitulo = document.getElementById("usuarioFormTitulo");
  const usuarioIdInput = document.getElementById("usuarioId");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const usuario = document.getElementById("usuario").value.trim();
      const contrasena = document.getElementById("contrasena").value.trim();
      const rol = document.getElementById("rol").value.trim();
      const privilegios = document.getElementById("privilegios").value.trim();

      if (!usuario || (!editandoId && !contrasena) || !rol) {
        alert("Todos los campos obligatorios deben completarse.");
        return;
      }

      // Nueva validación robusta de contraseña
      const errores = validarContrasena(contrasena);
      if (!editandoId && errores.length > 0) {
        alert("La contraseña no es válida:\n- " + errores.join("\n- "));
        return;
      }
      if (editandoId && contrasena && errores.length > 0) {
        alert("La nueva contraseña no es válida:\n- " + errores.join("\n- "));
        return;
      }

      const datos = new FormData();
      datos.append("usuario", usuario);
      datos.append("rol", rol);
      datos.append("privilegios", privilegios);

      if (editandoId) {
        datos.append("action", "update");
        datos.append("id", editandoId);
        if (contrasena) datos.append("contrasena", contrasena);
        // Guardar el ID del usuario que se está editando para hacer focus
        ultimoUsuarioGuardado = editandoId;
      } else {
        datos.append("action", "create");
        datos.append("contrasena", contrasena);
        // Para usuarios nuevos, guardaremos el nombre para buscarlo después
        ultimoUsuarioGuardado = usuario;
      }

      fetch("/CRM_INT/CRM/controller/UsuarioController.php", {
        method: "POST",
        body: datos,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            alert(editandoId ? "Usuario editado correctamente." : "Usuario guardado correctamente.");
            form.reset();
            const esEdicion = editandoId !== null;
            editandoId = null;
            if (formTitulo) formTitulo.textContent = "Registrar Usuario";
            
            // Recargar usuarios y hacer focus en el usuario guardado/editado
            cargarUsuarios().then(() => {
              if (esEdicion) {
                // Si fue edición, hacer focus por ID
                hacerFocusEnUsuario(ultimoUsuarioGuardado, 'id');
              } else {
                // Si fue creación, hacer focus por nombre de usuario
                hacerFocusEnUsuario(ultimoUsuarioGuardado, 'usuario');
              }
            });
          } else {
            alert("No se pudo guardar: " + (data.message || "Error desconocido."));
          }
        })
        .catch((err) => {
          console.error("Error al guardar usuario:", err);
          alert("Error al guardar usuario.");
        });
    });
  }

  // Función para hacer focus en un usuario específico de la tabla
  function hacerFocusEnUsuario(valor, tipo = 'id') {
    setTimeout(() => {
      let selector;
      if (tipo === 'id') {
        selector = `.fila-usuario[data-id="${valor}"]`;
      } else if (tipo === 'usuario') {
        // Buscar por nombre de usuario en los datos
        const usuarioEncontrado = usuariosOriginales.find(u => 
          u.usuario.toLowerCase() === valor.toLowerCase()
        );
        if (usuarioEncontrado) {
          selector = `.fila-usuario[data-id="${usuarioEncontrado.id}"]`;
        }
      }

      if (selector) {
        const filaUsuario = document.querySelector(selector);
        if (filaUsuario) {
          // Hacer scroll hacia el elemento
          filaUsuario.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
          });
          
          // Agregar clase de highlight temporal
          filaUsuario.classList.add('usuario-destacado');
          
          // Remover la clase después de 3 segundos
          setTimeout(() => {
            filaUsuario.classList.remove('usuario-destacado');
          }, 3000);
        }
      }
    }, 500); // Pequeño delay para asegurar que la tabla se haya renderizado
  }

  // Función para llenar el formulario automáticamente
  function llenarFormularioAutomatico(usuario) {
    editandoId = usuario.id;

    if (formTitulo) formTitulo.textContent = "Editar Usuario";
    if (usuarioIdInput) usuarioIdInput.value = usuario.id;

    const usuarioInput = document.getElementById("usuario");
    const rolInput = document.getElementById("rol");
    const contrasenaInput = document.getElementById("contrasena");
    const privilegiosInput = document.getElementById("privilegios");

    if (usuarioInput) usuarioInput.value = usuario.usuario;
    if (rolInput) rolInput.value = usuario.rol;
    if (contrasenaInput) contrasenaInput.value = "";

    // Llenar privilegios basado en el rol
    if (privilegiosInput) {
      const mensajesPrivilegios = {
        "Administrador": "Como administrador, tiene acceso completo al Dashboard. En la sección de Clientes VIP, puede ver, agregar y editar clientes, pero no puede eliminarlos ni reasignarles tarjetas. En la sección de Beneficios, puede utilizar todas las funciones, excepto eliminar beneficios ya registrados. No tiene acceso al módulo de Usuarios. En Código de Barras, puede utilizar todas las funcionalidades, incluyendo la exportación a PDF, pero no puede imprimir directamente desde impresora. Tiene acceso completo al módulo de Análisis y a la sección de Cumpleaños, con todas sus funcionalidades. No tiene acceso al módulo de Bitácora.",
        "Salonero": "Como salonero, puede ver solo la sección de Tareas en el Dashboard. En la sección de Clientes VIP, solo puede visualizar la información; no puede agregar, editar, eliminar clientes ni reasignar tarjetas. En la sección de Beneficios, puede realizar búsquedas, pero no puede acumular puntos, aplicar descuentos ni eliminar beneficios. No tiene permitido ingresar al módulo de Usuarios. En Código de Barras, tiene permitido realizar búsquedas, actualizar y utilizar la funcionalidad del código de barra, pero no puede exportar a PDF ni imprimir. No tiene permiso de acceso al módulo de Análisis. Tiene acceso completo a Cumpleaños, con todas sus funcionalidades. No tiene acceso permitido a Bitácora.",
        "Propietario": "Como propietario, tiene acceso total a todas las funciones y secciones del sistema. No tiene ninguna restricción."
      };
      privilegiosInput.value = mensajesPrivilegios[usuario.rol] || "";
    }

    // Solo hacer focus en el primer campo sin scroll
    if (usuarioInput) {
      setTimeout(() => {
        usuarioInput.focus();
        usuarioInput.select(); // Seleccionar todo el texto para facilitar edición
      }, 300);
    }
  }

  document.addEventListener("click", function (e) {
    const editarBtn = e.target.closest(".editar-usuario");
    const eliminarBtn = e.target.closest(".eliminar-usuario");

    // Mensajes de privilegios unificados
    const mensajesPrivilegios = {
      "Administrador": "Como administrador, tiene acceso completo al Dashboard. En la sección de Clientes VIP, puede ver, agregar y editar clientes, pero no puede eliminarlos ni reasignarles tarjetas. En la sección de Beneficios, puede utilizar todas las funciones, excepto eliminar beneficios ya registrados. No tiene acceso al módulo de Usuarios. En Código de Barras, puede utilizar todas las funcionalidades, incluyendo la exportación a PDF, pero no puede imprimir directamente desde impresora. Tiene acceso completo al módulo de Análisis y a la sección de Cumpleaños, con todas sus funcionalidades. No tiene acceso al módulo de Bitácora.",
      "Salonero": "Como salonero, puede ver solo la sección de Tareas en el Dashboard. En la sección de Clientes VIP, solo puede visualizar la información; no puede agregar, editar, eliminar clientes ni reasignar tarjetas. En la sección de Beneficios, puede realizar búsquedas, pero no puede acumular puntos, aplicar descuentos ni eliminar beneficios. No tiene permitido ingresar al módulo de Usuarios. En Código de Barras, tiene permitido realizar búsquedas, actualizar y utilizar la funcionalidad del código de barra, pero no puede exportar a PDF ni imprimir. No tiene permiso de acceso al módulo de Análisis. Tiene acceso completo a Cumpleaños, con todas sus funcionalidades. No tiene acceso permitido a Bitácora.",
      "Propietario": "Como propietario, tiene acceso total a todas las funciones y secciones del sistema. No tiene ninguna restricción."
    };

    if (editarBtn) {
      const id = editarBtn.getAttribute("data-id");
      const usuario = editarBtn.getAttribute("data-usuario");
      const rol = editarBtn.getAttribute("data-rol");
      editandoId = id;
      if (formTitulo) formTitulo.textContent = "Editar Usuario";
      if (usuarioIdInput) usuarioIdInput.value = id;
      document.getElementById("usuario").value = usuario;
      document.getElementById("rol").value = rol;
      document.getElementById("contrasena").value = "";
      if (rolInput && privilegiosInput) {
        privilegiosInput.value = mensajesPrivilegios[rol] || "";
      }
      document.getElementById("usuario").focus();
      return;
    }

    if (eliminarBtn) {
      const id = eliminarBtn.getAttribute("data-id");
      if (confirm("¿Estás seguro de eliminar este usuario?")) {
        const datos = new FormData();
        datos.append("action", "delete");
        datos.append("id", id);

        fetch("/CRM_INT/CRM/controller/UsuarioController.php", {
          method: "POST",
          body: datos,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              alert("Usuario eliminado.");
              cargarUsuarios();
            } else {
              alert("No se pudo eliminar: " + (data.message || "Error desconocido."));
            }
          })
          .catch((err) => {
            console.error("Error:", err);
            alert("Error al eliminar usuario.");
          });
      }
    }
  });

  const cancelBtn = document.getElementById("cancelBtn");
  if (cancelBtn) {
    cancelBtn.addEventListener("click", function () {
      editandoId = null;
      if (formTitulo) formTitulo.textContent = "Registrar Usuario";
      if (form) form.reset();
    });
  }

  cargarUsuarios();

  const usuarioInput = document.getElementById("usuario");
  if (usuarioInput) {
    usuarioInput.oninvalid = function (e) {
      e.target.setCustomValidity("Por favor introduzca un nombre");
    };
    usuarioInput.oninput = function (e) {
      e.target.setCustomValidity("");
    };
    usuarioInput.addEventListener("input", function () {
      this.value = this.value.toUpperCase();
    });
  }

  const passwordInput = document.getElementById("contrasena");
  if (passwordInput) {
    passwordInput.setAttribute("maxlength", "16");
    passwordInput.oninvalid = function (e) {
      e.target.setCustomValidity("Por favor introduzca una contraseña");
    };
    passwordInput.oninput = function (e) {
      e.target.setCustomValidity("");
    };
  }

  const rolInput = document.getElementById("rol");
  const privilegiosInput = document.getElementById("privilegios");

  if (rolInput) {
    rolInput.oninvalid = function (e) {
      e.target.setCustomValidity("Por favor seleccione un rol");
    };
    rolInput.oninput = function (e) {
      e.target.setCustomValidity("");
    };
  }

  if (rolInput && privilegiosInput) {
    rolInput.addEventListener("change", function () {
      const mensajesPrivilegios = {
        "Administrador": "Como administrador, tiene acceso completo al Dashboard. En la sección de Clientes VIP, puede ver, agregar y editar clientes, pero no puede eliminarlos ni reasignarles tarjetas. En la sección de Beneficios, puede utilizar todas las funciones, excepto eliminar beneficios ya registrados. No tiene acceso al módulo de Usuarios. En Código de Barras, puede utilizar todas las funcionalidades, incluyendo la exportación a PDF, pero no puede imprimir directamente desde impresora. Tiene acceso completo al módulo de Análisis y a la sección de Cumpleaños, con todas sus funcionalidades. No tiene acceso al módulo de Bitácora.",
        "Salonero": "Como salonero, puede ver solo la sección de Tareas en el Dashboard. En la sección de Clientes VIP, solo puede visualizar la información; no puede agregar, editar, eliminar clientes ni reasignar tarjetas. En la sección de Beneficios, puede realizar búsquedas, pero no puede acumular puntos, aplicar descuentos ni eliminar beneficios. No tiene permitido ingresar al módulo de Usuarios. En Código de Barras, tiene permitido realizar búsquedas, actualizar y utilizar la funcionalidad del código de barra, pero no puede exportar a PDF ni imprimir. No tiene permiso de acceso al módulo de Análisis. Tiene acceso completo a Cumpleaños, con todas sus funcionalidades. No tiene acceso permitido a Bitácora.",
        "Propietario": "Como propietario, tiene acceso total a todas las funciones y secciones del sistema. No tiene ninguna restricción."
      };
      privilegiosInput.value = mensajesPrivilegios[rolInput.value] || "";
    });
  }

  if (privilegiosInput) {
    privilegiosInput.value = "";
  }
});

//LÓGICA PARA EL BUSCADOR Y LA FORMA EN QUE SE MUESTRAN LO USUARIOS
//ADEMÁS DEL AUTOCOMPLETADO DEL FORMULARIO
function mostrarUsuarios(usuarios) {
  usuariosActuales = usuarios; // Guardar para eventos de la tabla

  const contenedor = document.getElementById("usuarioLista");
  if (!usuarios || usuarios.length === 0) {
    contenedor.innerHTML = "<p class='text-center mt-3'>No hay usuarios registrados.</p>";
    return;
  }

  let html = `<table class='table table-striped table-hover mt-4' id='tablaUsuarios'>
    <thead class='table-dark'>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>`;

  usuarios.forEach(usuario => {
    html += `<tr class="fila-usuario" data-id="${usuario.id}" style="cursor: pointer;">
        <td>${usuario.id}</td>
        <td>${usuario.usuario}</td>
        <td>${usuario.rol.toUpperCase()}</td>
        <td>
          <button class='btn btn-sm btn-warning editar-usuario' data-id='${usuario.id}' data-usuario='${usuario.usuario}' data-rol='${usuario.rol}'>Editar</button>
          <button class='btn btn-sm btn-danger eliminar-usuario' data-id='${usuario.id}'>Eliminar</button>
        </td>
      </tr>`;
  });

  html += "</tbody></table>";
  contenedor.innerHTML = html;

  // --- Auto-llenado de formulario al dar click en la fila (NO en los botones)
  document.querySelectorAll(".fila-usuario").forEach(fila => {
    fila.addEventListener("click", function(e) {
      // Prevenir si se hace clic en botones
      if (e.target.tagName === "BUTTON" || e.target.closest("button")) return;
      
      const id = this.getAttribute("data-id");

      // Buscar en usuariosOriginales el usuario correspondiente
      const usuario = usuariosOriginales.find(u => u.id == id);

      if (usuario) {
        llenarFormularioAutomatico(usuario);
      }
    });

    // Agregar efecto hover visual para indicar que es clickeable
    fila.addEventListener("mouseenter", function(e) {
      if (!e.target.closest("button")) {
        this.style.backgroundColor = "#f8f9fa";
      }
    });

    fila.addEventListener("mouseleave", function() {
      this.style.backgroundColor = "";
    });
  });
}

// Función para llenar el formulario automáticamente
function llenarFormularioAutomatico(usuario) {
  editandoId = usuario.id;
  
  const usuarioIdInput = document.getElementById("usuarioId");
  if (usuarioIdInput) usuarioIdInput.value = usuario.id;

  const usuarioInput = document.getElementById("usuario");
  const rolInput = document.getElementById("rol");
  const contrasenaInput = document.getElementById("contrasena");
  const privilegiosInput = document.getElementById("privilegios");

  if (usuarioInput) usuarioInput.value = usuario.usuario;
  if (rolInput) rolInput.value = usuario.rol;
  if (contrasenaInput) contrasenaInput.value = "";

  // Llenar privilegios basado en el rol
  if (privilegiosInput) {
    const mensajesPrivilegios = {
      "Administrador": "...",
      "Salonero": "...",
      "Propietario": "..."
    };
    privilegiosInput.value = mensajesPrivilegios[usuario.rol] || "";
  }

  // Solo hacer focus en el primer campo sin scroll
  if (usuarioInput) {
    setTimeout(() => {
      usuarioInput.focus();
      usuarioInput.select(); // Seleccionar todo el texto para facilitar edición
    }, 300);
  }
}


// Función para inicializar o actualizar la lista completa de usuarios
const cargarUsuariosCompletos = (usuarios) => {
  usuariosOriginales = [...usuarios].sort((a, b) => b.id - a.id); // Más reciente primero
  mostrarUsuarios(usuariosOriginales);
};

window.cargarUsuarios = function () {
  const contenedor = document.getElementById("usuarioLista");
  if (!contenedor) return Promise.reject("Contenedor no encontrado");

  contenedor.innerHTML = `<div class="text-center p-3">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2">Cargando usuarios...</p>
  </div>`;

  return fetch("/CRM_INT/CRM/controller/UsuarioController.php?action=readAll")
    .then((res) => res.json())
    .then((data) => {
      if (data.success && Array.isArray(data.data)) {
        // CAMBIO IMPORTANTE: Usar la nueva función en lugar de mostrarUsuarios directamente
        cargarUsuariosCompletos(data.data);
        return data.data; // Retornar los datos para poder usar en el promise
      } else {
        contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
        throw new Error("Error al cargar usuarios");
      }
    })
    .catch((err) => {
      console.error("Error al cargar usuarios:", err);
      contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
      throw err;
    });
};

const buscadorUsuarios = document.getElementById("buscadorUsuarios");
if (buscadorUsuarios) {
  buscadorUsuarios.addEventListener("input", function () {
    const valor = this.value.trim().toLowerCase();

    if (!valor) {
      // Si usuariosOriginales está vacío, usar usuariosActuales como fallback
      const usuariosParaMostrar = usuariosOriginales.length > 0 ? usuariosOriginales : usuariosActuales;
      mostrarUsuarios(usuariosParaMostrar);
      return;
    }

    // Usar el mismo fallback para filtrar
    const usuariosParaFiltrar = usuariosOriginales.length > 0 ? usuariosOriginales : usuariosActuales;
    const filtrados = usuariosParaFiltrar.filter(u =>
      (u.usuario && u.usuario.toLowerCase().includes(valor)) ||
      (u.id && u.id.toString().includes(valor))
    );

    mostrarUsuarios(filtrados);
  });
}

document.getElementById("usuarioForm").addEventListener("keydown", function(e) {
  // Solo para Enter, no estando en textarea ni en botón de cancelar
  if (
    e.key === "Enter" &&
    e.target.tagName !== "TEXTAREA" &&
    !(e.target.id === "cancelBtn")
  ) {
    e.preventDefault(); // Evita el submit por defecto (doble envío)
    this.requestSubmit(); // Lanza el submit (respeta validaciones)
  }
});

// Función para hacer focus en un usuario específico de la tabla
function hacerFocusEnUsuario(valor, tipo = 'id') {
  setTimeout(() => {
    let selector;
    if (tipo === 'id') {
      selector = `.fila-usuario[data-id="${valor}"]`;
    } else if (tipo === 'usuario') {
      // Buscar por nombre de usuario en los datos
      const usuarioEncontrado = usuariosOriginales.find(u => 
        u.usuario.toLowerCase() === valor.toLowerCase()
      );
      if (usuarioEncontrado) {
        selector = `.fila-usuario[data-id="${usuarioEncontrado.id}"]`;
      }
    }

    if (selector) {
      const filaUsuario = document.querySelector(selector);
      if (filaUsuario) {
        // Hacer scroll hacia el elemento
        filaUsuario.scrollIntoView({ 
          behavior: 'smooth', 
          block: 'center' 
        });
        
        // Agregar clase de highlight temporal
        filaUsuario.classList.add('usuario-destacado');
        
        // Remover la clase después de 3 segundos
        setTimeout(() => {
          filaUsuario.classList.remove('usuario-destacado');
        }, 3000);
      }
    }
  }, 500); // Pequeño delay para asegurar que la tabla se haya renderizado
}