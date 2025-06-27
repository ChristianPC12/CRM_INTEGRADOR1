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
          if (data.data.length === 0) {
            contenedor.innerHTML = "<p class='text-center mt-3'>No hay usuarios registrados.</p>";
            return;
          }
          let html = `<table class='table table-striped table-hover mt-4'>
            <thead class='table-dark'>
              <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>`;
          data.data.forEach((usuario) => {
            html += `<tr>
              <td>${usuario.id}</td>
              <td>${usuario.usuario}</td>
              <td>${usuario.rol.toUpperCase()}</td>
              <td>
                <button class='btn btn-sm btn-amarillo editar-usuario' data-id='${usuario.id}' data-usuario='${usuario.usuario}' data-rol='${usuario.rol}'>Editar</button>
                <button class='btn btn-sm btn-danger eliminar-usuario' data-id='${usuario.id}'>Eliminar</button>
              </td>
            </tr>`;
          });
          html += "</tbody></table>";
          contenedor.innerHTML = html;
        } else {
          contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
        }
      })
      .catch((err) => {
        console.error("Error al cargar usuarios:", err);
        contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
      });
  };

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
