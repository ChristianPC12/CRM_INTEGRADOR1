document.addEventListener("DOMContentLoaded", function () {
  let editandoId = null;

  // Mostrar/ocultar contraseña
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("contrasena");
  const passwordIcon = document.getElementById("passwordIcon");

  if (togglePassword && passwordInput && passwordIcon) {
    // PC: mouse events
    togglePassword.addEventListener("mousedown", function () {
      passwordInput.type = "text";
      passwordIcon.classList.replace("bi-eye", "bi-eye-slash");
      togglePassword.title = "Ocultar contraseña";
    });
    togglePassword.addEventListener("mouseup", function () {
      passwordInput.type = "password";
      passwordIcon.classList.replace("bi-eye-slash", "bi-eye");
      togglePassword.title = "Mostrar contraseña";
    });
    togglePassword.addEventListener("mouseleave", function () {
      passwordInput.type = "password";
      passwordIcon.classList.replace("bi-eye-slash", "bi-eye");
      togglePassword.title = "Mostrar contraseña";
    });
    // Touch
    togglePassword.addEventListener("touchstart", function () {
      passwordInput.type = "text";
      passwordIcon.classList.replace("bi-eye", "bi-eye-slash");
      togglePassword.title = "Ocultar contraseña";
    });
    togglePassword.addEventListener("touchend", function () {
      passwordInput.type = "password";
      passwordIcon.classList.replace("bi-eye-slash", "bi-eye");
      togglePassword.title = "Mostrar contraseña";
    });
  }

  // Validación de contraseña: 6 a 16 caracteres, al menos una letra y un número
  function esContrasenaValida(contrasena) {
    // Al menos una letra, un número y un carácter especial, longitud 6 a 16
    const regex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{6,16}$/;
    return regex.test(contrasena);
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

      // Validación de longitud máxima
      if (contrasena.length > 16) {
        alert(
          "La contraseña debe tener entre 6 y 16 caracteres, al menos una letra, un número y un carácter especial."
        );
        return;
      }

      // Solo validar la complejidad en creación, no al editar si no hay cambio de contraseña
      if (!editandoId && !esContrasenaValida(contrasena)) {
        alert(
          "La contraseña debe tener entre 6 y 16 caracteres, con al menos una letra y un número."
        );
        return;
      }
      // Al editar, si se está cambiando la contraseña, validarla también
      if (editandoId && contrasena && !esContrasenaValida(contrasena)) {
        alert(
          "La nueva contraseña debe tener entre 6 y 16 caracteres, con al menos una letra y un número."
        );
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
            alert(
              editandoId
                ? "Usuario editado correctamente."
                : "Usuario guardado correctamente."
            );
            form.reset();
            editandoId = null;
            if (formTitulo) formTitulo.textContent = "Registrar Usuario";
            cargarUsuarios();
          } else {
            alert(
              "No se pudo guardar: " + (data.message || "Error desconocido.")
            );
          }
        })
        .catch((err) => {
          console.error("Error al guardar usuario:", err);
          alert("Error al guardar usuario.");
        });
    });
  }

  // Cargar usuarios en la tabla
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
            contenedor.innerHTML =
              "<p class='text-center mt-3'>No hay usuarios registrados.</p>";
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
              <td>${usuario.rol}</td>
              <td>
                <button class='btn btn-sm btn-amarillo editar-usuario' data-id='${usuario.id}' data-usuario='${usuario.usuario}' data-rol='${usuario.rol}'>Editar</button>
                <button class='btn btn-sm btn-danger eliminar-usuario' data-id='${usuario.id}'>Eliminar</button>
              </td>
            </tr>`;
          });
          html += "</tbody></table>";
          contenedor.innerHTML = html;
        } else {
          contenedor.innerHTML =
            "<p class='text-danger'>Error al cargar usuarios.</p>";
        }
      })
      .catch((err) => {
        console.error("Error al cargar usuarios:", err);
        contenedor.innerHTML =
          "<p class='text-danger'>Error al cargar usuarios.</p>";
      });
  };

  // Evento para editar y eliminar usuario
  document.addEventListener("click", function (e) {
    const editarBtn = e.target.closest(".editar-usuario");
    const eliminarBtn = e.target.closest(".eliminar-usuario");

    // Editar usuario
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
      // Privilegios: refresca descripción
      if (rolInput && privilegiosInput) {
        let mensaje = "";
        switch (rol) {
          case "Administrador":
            mensaje =
              "Puede visualizar, crear y editar clientes. No puede eliminar registros. No puede ingresar al modulo Usuario.";
            break;
          case "Salonero":
            mensaje =
              "Solo puede visualizar la sección. No puede editar, guardar ni eliminar registros. No puede ingresar al Modulo Usuario.";
            break;
          case "Propietario":
            mensaje =
              "Acceso completo a todas las secciones y funcionalidades del sistema.";
            break;
          default:
            mensaje = "";
        }
        privilegiosInput.value = mensaje;
      }
      document.getElementById("usuario").focus();
      return;
    }

    // Eliminar usuario
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
              alert(
                "No se pudo eliminar: " + (data.message || "Error desconocido.")
              );
            }
          })
          .catch((err) => {
            console.error("Error:", err);
            alert("Error al eliminar usuario.");
          });
      }
    }
  });

  // Botón Cancelar para volver a modo "crear"
  const cancelBtn = document.getElementById("cancelBtn");
  if (cancelBtn) {
    cancelBtn.addEventListener("click", function () {
      editandoId = null;
      if (formTitulo) formTitulo.textContent = "Registrar Usuario";
      if (form) form.reset();
    });
  }

  // Cargar la lista al abrir la vista
  cargarUsuarios();

  // Mensaje personalizado para los campos
  const usuarioInput = document.getElementById("usuario");
  if (usuarioInput) {
    usuarioInput.oninvalid = function (e) {
      e.target.setCustomValidity("Por favor introduzca un nombre");
    };
    usuarioInput.oninput = function (e) {
      e.target.setCustomValidity("");
    };
  }

  if (passwordInput) {
    passwordInput.setAttribute("maxlength", "16"); // asegúrate que el input tenga el límite también
    passwordInput.oninvalid = function (e) {
      e.target.setCustomValidity("Por favor introduzca una contraseña");
    };
    passwordInput.oninput = function (e) {
      e.target.setCustomValidity("");
    };
  }

  const rolInput = document.getElementById("rol");
  if (rolInput) {
    rolInput.oninvalid = function (e) {
      e.target.setCustomValidity("Por favor seleccione un rol");
    };
    rolInput.oninput = function (e) {
      e.target.setCustomValidity("");
    };
  }

  // Mostrar mensaje en privilegios según el rol seleccionado
  const privilegiosInput = document.getElementById("privilegios");
  if (rolInput && privilegiosInput) {
    rolInput.addEventListener("change", function () {
      let mensaje = "";
      switch (rolInput.value) {
        case "Administrador":
          mensaje =
            "Puede visualizar, crear y editar clientes. No puede eliminar registros. No puede ingresar al modulo Usuario.";
          break;
        case "Salonero":
          mensaje =
            "Solo puede visualizar la sección. No puede editar, guardar ni eliminar registros. No puede ingresar al Modulo Usuario.";
          break;
        case "Propietario":
          mensaje =
            "Acceso completo a todas las secciones y funcionalidades del sistema.";
          break;
        default:
          mensaje = "";
      }
      privilegiosInput.value = mensaje;
    });
  }

  // Limpiar el campo de privilegios al cargar la página
  if (privilegiosInput) {
    privilegiosInput.value = "";
  }
});
