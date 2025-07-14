let usuariosOriginales = []; // Nueva variable para guardar TODOS los usuarios
let usuariosActuales = []; // Guardará los usuarios filtrados para la tabla

document.addEventListener("DOMContentLoaded", function () {
  let editandoId = null;

  // Validación de contraseña: 6 a 16 caracteres, al menos una letra, un número y un carácter especial
  function esContrasenaValida(contrasena) {
    const regex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{6,16}$/;
    return regex.test(contrasena);
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

      if (contrasena.length > 16) {
        alert("La contraseña debe tener entre 6 y 16 caracteres, al menos una letra, un número y un carácter especial.");
        return;
      }

      if (!editandoId && !esContrasenaValida(contrasena)) {
        alert("La contraseña debe tener entre 6 y 16 caracteres, con al menos una letra, un número y un carácter especial.");
        return;
      }

      if (editandoId && contrasena && !esContrasenaValida(contrasena)) {
        alert("La nueva contraseña debe tener entre 6 y 16 caracteres, con al menos una letra, un número y un carácter especial.");
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
      } else {
        datos.append("action", "create");
        datos.append("contrasena", contrasena);
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
            editandoId = null;
            if (formTitulo) formTitulo.textContent = "Registrar Usuario";
            cargarUsuarios();
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

  

  document.addEventListener("click", function (e) {
    const editarBtn = e.target.closest(".editar-usuario");
    const eliminarBtn = e.target.closest(".eliminar-usuario");

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
        let mensaje = "";
        switch (rol) {
          case "Administrador":
            mensaje = "Como administrador, puede acceder completamente al Dashboard. En la sección de Clientes VIP puede ver, agregar y editar clientes, pero no eliminarlos. En la sección de Beneficios puede usar todas las funciones, excepto eliminar beneficios ya registrados. No tiene acceso al módulo de Usuarios.";
            break;
          case "Salonero":
            mensaje = "Como salonero, puede ver todo el contenido del Dashboard. En la sección de Clientes VIP solo puede ver la información, pero no puede agregar, editar ni eliminar clientes. En la sección de Beneficios puede hacer búsquedas, pero no puede acumular puntos, aplicar descuentos ni eliminar beneficios. No tiene permitido ingresar al módulo de Usuarios.";
            break;
          case "Propietario":
            mensaje = "Como propietario, tiene acceso total a todas las funciones y secciones del sistema. No tiene ninguna restricción.";
            break;
          default:
            mensaje = "";
        }
        privilegiosInput.value = mensaje;
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
      let mensaje = "";
      switch (rolInput.value) {
        case "Administrador":
          mensaje = "Como administrador, puede acceder completamente al Dashboard. En la sección de Clientes VIP puede ver, agregar y editar clientes, pero no eliminarlos. En la sección de Beneficios puede usar todas las funciones, excepto eliminar beneficios ya registrados. No tiene acceso al módulo de Usuarios.";
          break;
        case "Salonero":
          mensaje = "Como salonero, puede ver todo el contenido del Dashboard. En la sección de Clientes VIP solo puede ver la información, pero no puede agregar, editar ni eliminar clientes. En la sección de Beneficios puede hacer búsquedas, pero no puede acumular puntos, aplicar descuentos ni eliminar beneficios. No tiene permitido ingresar al módulo de Usuarios.";
          break;
        case "Propietario":
          mensaje = "Como propietario, tiene acceso total a todas las funciones y secciones del sistema. No tiene ninguna restricción.";
          break;
        default:
          mensaje = "";
      }
      privilegiosInput.value = mensaje;
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
        html += `<tr class="fila-usuario" data-id="${usuario.id}">
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
        if (e.target.tagName === "BUTTON" || e.target.closest("button")) return;
        const id = this.getAttribute("data-id");
        
        // Buscar en usuariosOriginales en lugar de usuariosActuales
        const usuario = usuariosOriginales.find(u => u.id == id);
        
        if (usuario) {
            editandoId = usuario.id;
            
            // Verificar si formTitulo existe antes de usarlo
            const formTitulo = document.getElementById("formTitulo") || document.querySelector("h5") || document.querySelector(".form-title");
            if (formTitulo) {
                formTitulo.textContent = "Editar Usuario";
            }
            
            // Verificar si usuarioIdInput existe antes de usarlo
            const usuarioIdInput = document.getElementById("usuarioId") || document.getElementById("id");
            if (usuarioIdInput) {
                usuarioIdInput.value = usuario.id;
            }
            
            // Elementos del formulario con validación
            const usuarioInput = document.getElementById("usuario");
            const rolInput = document.getElementById("rol");
            const contrasenaInput = document.getElementById("contrasena");
            
            if (usuarioInput) usuarioInput.value = usuario.usuario;
            if (rolInput) rolInput.value = usuario.rol;
            if (contrasenaInput) contrasenaInput.value = "";
            if (usuarioInput) usuarioInput.focus();
            
            // Privilegios - también con validación
            const privilegiosInput = document.getElementById("privilegios");
            if (rolInput && privilegiosInput) {
              let mensaje = "";
              switch (usuario.rol) {
                case "Administrador":
                  mensaje = "Como administrador, puede acceder completamente al Dashboard...";
                  break;
                case "Salonero":
                  mensaje = "Como salonero, puede ver todo el contenido del Dashboard...";
                  break;
                case "Propietario":
                  mensaje = "Como propietario, tiene acceso total a todas las funciones y secciones...";
                  break;
                default:
                  mensaje = "";
              }
              privilegiosInput.value = mensaje;
            }
        }
    });
});
}

// Función para inicializar o actualizar la lista completa de usuarios
const cargarUsuariosCompletos = (usuarios) => {
  usuariosOriginales = [...usuarios]; // Guardar copia completa
  mostrarUsuarios(usuarios);
};

window.cargarUsuarios = function () {
    const contenedor = document.getElementById("usuarioLista");
    if (!contenedor) return;
    
    contenedor.innerHTML = `<div class="text-center p-3">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2">Cargando usuarios...</p>
    </div>`;
    
    fetch("/CRM_INT/CRM/controller/UsuarioController.php?action=readAll")
      .then((res) => res.json())
      .then((data) => {
        if (data.success && Array.isArray(data.data)) {
            // CAMBIO IMPORTANTE: Usar la nueva función en lugar de mostrarUsuarios directamente
            cargarUsuariosCompletos(data.data);
        } else {
            contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
        }
      })
      .catch((err) => {
        console.error("Error al cargar usuarios:", err);
        contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
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