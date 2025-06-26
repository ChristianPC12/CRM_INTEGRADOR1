document.addEventListener("DOMContentLoaded", function () {

  //  Funcionalidad para mostrar/ocultar contraseña
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("contrasena");
  const passwordIcon = document.getElementById("passwordIcon");
  let passwordTimeout;

  if (togglePassword && passwordInput && passwordIcon) {
    
    // Mostrar contraseña al presionar
    togglePassword.addEventListener("mousedown", function () {
      passwordInput.setAttribute("type", "text");
      passwordIcon.classList.remove("bi-eye");
      passwordIcon.classList.add("bi-eye-slash");
      togglePassword.setAttribute("title", "Ocultar contraseña");
    });

    // Ocultar contraseña al soltar el botón
    togglePassword.addEventListener("mouseup", function () {
      passwordInput.setAttribute("type", "password");
      passwordIcon.classList.remove("bi-eye-slash");
      passwordIcon.classList.add("bi-eye");
      togglePassword.setAttribute("title", "Mostrar contraseña");
    });

    // Ocultar contraseña al quitar el mouse
    togglePassword.addEventListener("mouseleave", function () {
      passwordInput.setAttribute("type", "password");
      passwordIcon.classList.remove("bi-eye-slash");
      passwordIcon.classList.add("bi-eye");
      togglePassword.setAttribute("title", "Mostrar contraseña");
    });

    // Para dispositivos táctiles (móviles)
    togglePassword.addEventListener("touchstart", function () {
      passwordInput.setAttribute("type", "text");
      passwordIcon.classList.remove("bi-eye");
      passwordIcon.classList.add("bi-eye-slash");
      togglePassword.setAttribute("title", "Ocultar contraseña");
    });

    togglePassword.addEventListener("touchend", function () {
      passwordInput.setAttribute("type", "password");
      passwordIcon.classList.remove("bi-eye-slash");
      passwordIcon.classList.add("bi-eye");
      togglePassword.setAttribute("title", "Mostrar contraseña");
    });
  }

  // Validación sencilla de contraseña (mínimo 6 caracteres, al menos una letra y un número)
  function esContrasenaValida(contrasena) {
    const regex = /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/;
    return regex.test(contrasena);
  }

  const form = document.getElementById("usuarioForm");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value.trim();
    const contrasena = document.getElementById("contrasena").value.trim();
    const rol = document.getElementById("rol").value.trim();
    const privilegios = document.getElementById("privilegios").value.trim();

    if (!usuario || !contrasena || !rol) {
      alert("Todos los campos obligatorios deben completarse.");
      return;
    }

    if (!esContrasenaValida(contrasena)) {
      alert("La contraseña debe tener al menos 6 caracteres, una letra y un número.");
      return;
    }

    const datos = new FormData();
    datos.append("action", "create");
    datos.append("usuario", usuario);
    datos.append("contrasena", contrasena);
    datos.append("rol", rol);
    datos.append("privilegios", privilegios);

    fetch("/CRM_INT/CRM/controller/UsuarioController.php", {
      method: "POST",
      body: datos,
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Usuario guardado correctamente.");
          form.reset();
          cargarUsuarios();
        } else {
          alert("No se pudo guardar: " + data.message);
        }
      })
      .catch(err => {
        console.error("Error al guardar usuario:", err);
        alert("Error al guardar usuario.");
      });
  });

  // ✅ Cargar usuarios en la tabla
  window.cargarUsuarios = function () {
    const contenedor = document.getElementById("usuarioLista");
    contenedor.innerHTML = `<div class="text-center p-3">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2">Cargando usuarios...</p>
    </div>`;

    fetch("/CRM_INT/CRM/controller/UsuarioController.php?action=readAll")
      .then(res => res.json())
      .then(data => {
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
          data.data.forEach(usuario => {
            html += `<tr>
              <td>${usuario.id}</td>
              <td>${usuario.usuario}</td>
              <td>${usuario.rol}</td>
              <td>
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
      .catch(err => {
        console.error("Error al cargar usuarios:", err);
        contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
      });
  };

  // Eliminar usuario al hacer clic
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("eliminar-usuario") || e.target.closest(".eliminar-usuario")) {
      const btn = e.target.closest(".eliminar-usuario");
      const id = btn.getAttribute("data-id");

      if (confirm("¿Estás seguro de eliminar este usuario?")) {
        const datos = new FormData();
        datos.append("action", "delete");
        datos.append("id", id);

        fetch("/CRM_INT/CRM/controller/UsuarioController.php", {
          method: "POST",
          body: datos,
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert(" Usuario eliminado.");
              cargarUsuarios();
            } else {
              alert("No se pudo eliminar: " + data.message);
            }
          })
          .catch(err => {
            console.error("Error:", err);
            alert("Error al eliminar usuario.");
          });
      }
    }
  });

  //  Cargar la lista al abrir la vista
  cargarUsuarios();

  // Mensaje personalizado para el campo usuario
  const usuarioInput = document.getElementById("usuario");
  usuarioInput.oninvalid = function(e) {
    e.target.setCustomValidity("Por favor introduzca un nombre");
  };
  usuarioInput.oninput = function(e) {
    e.target.setCustomValidity("");
  };

  // Mensaje personalizado para el campo contraseña
  const contrasenaInput = document.getElementById("contrasena");
  contrasenaInput.oninvalid = function(e) {
    e.target.setCustomValidity("Por favor introduzca una contraseña");
  };
  contrasenaInput.oninput = function(e) {
    e.target.setCustomValidity("");
  };

  // Mensaje personalizado para el campo rol
  const rolInput = document.getElementById("rol");
  rolInput.oninvalid = function(e) {
    e.target.setCustomValidity("Por favor seleccione un rol");
  };
  rolInput.oninput = function(e) {
    e.target.setCustomValidity("");
  };

  // Mostrar mensaje en privilegios según el rol seleccionado
  const privilegiosInput = document.getElementById("privilegios");
  if (rolInput && privilegiosInput) {
    rolInput.addEventListener("change", function() {
      let mensaje = "";
      switch (rolInput.value) {
        case "Administrador":
          mensaje = "Puede visualizar, crear y editar clientes. No puede eliminar registros. No puede ingresar al modulo Usuario.";
          break;
        case "Salonero":
          mensaje = "Solo puede visualizar la sección. No puede editar, guardar ni eliminar registros. No puede ingresar al Modulo Usuario.";
          break;
        case "Propietario":
          mensaje = "Acceso completo a todas las secciones y funcionalidades del sistema.";
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
